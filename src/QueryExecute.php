<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Adapter\Interfaces\IAdapter;
use Cratia\ORM\DBAL\Events\Events;
use Cratia\ORM\DBAL\Events\Payloads\EventQueryExecuteAfterPayload;
use Cratia\ORM\DBAL\Events\Payloads\EventQueryExecuteBeforePayload;
use Cratia\ORM\DBAL\Events\Payloads\EventQueryExecuteErrorPayload;
use Cratia\ORM\DBAL\Interfaces\IQueryDTO;
use Cratia\ORM\DQL\Interfaces\IField;
use Cratia\ORM\DQL\Interfaces\IQuery;
use Cratia\ORM\DQL\Interfaces\ISql;
use Cratia\ORM\DQL\Sql;
use Cratia\Pipeline;
use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DBALException;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class QueryExecute
 */
class QueryExecute
{
    /**
     * @var IAdapter
     */
    private $adapter;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var EventManager|null
     */
    private $eventManager;

    /**
     * QueryExecute constructor.
     * @param IAdapter $adapter
     * @param LoggerInterface|null $logger
     * @param EventManager|null $eventManager
     */
    public function __construct(IAdapter $adapter, ?LoggerInterface $logger = null, ?EventManager $eventManager = null)
    {
        $this->adapter = $adapter;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
    }

    /**
     * @return IAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     * @return QueryExecute
     */
    public function setLogger(LoggerInterface $logger): QueryExecute
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return EventManager|null
     */
    public function getEventManager(): ?EventManager
    {
        return $this->eventManager;
    }

    /**
     * @param EventManager|null $eventManager
     * @return QueryExecute
     */
    public function setEventManager(?EventManager $eventManager): QueryExecute
    {
        $this->eventManager = $eventManager;
        return $this;
    }

    /**
     * @param IQuery $query
     * @return IQueryDTO
     */
    public function executeQuery(IQuery $query): IQueryDTO
    {
        $time = -microtime(true);
        /** @var ISql $sql */
        $sql = $query->toSQL();
        return Pipeline::try(
            function () {
            })
            ->tap(function () use ($sql) {
                $this->notify(Events::ON_BEFORE_EXECUTE_QUERY, new EventQueryExecuteBeforePayload($sql));
            })
            ->then(function () use ($sql) {
                return $this->_executeQuery($sql);
            })
            ->then(function (array $rawRows) use ($query) {
                return $this->completeRawRows($rawRows, $query);
            })
            ->then(function (array $rows) use ($time, $sql) {
                return $this->createDTO(IAdapter::FETCH, $rows, $sql, $time);
            })
            ->then(function (IQueryDTO $dto) use ($query) {
                return $this->resolveFoundRows($dto, $query);
            })
            ->then(function (IQueryDTO $dto) use ($query) {
                return $dto;
            })
            ->tap(function (IQueryDTO $dto) {
                $this->log($dto);
            })
            ->tap(function (IQueryDTO $dto) {
                $this->notify(Events::ON_AFTER_EXECUTE_QUERY, new EventQueryExecuteAfterPayload($dto));
            })
            ->tapCatch(function (DBALException $e) {
                $this->notify(Events::ON_ERROR, new EventQueryExecuteErrorPayload($e));
            })
            ->tapCatch(function (Exception $e) {
                $this->notify(Events::ON_ERROR, new EventQueryExecuteErrorPayload($e));
            })
            ->catch(function (DBALException $e) {
                throw $e;
            })
            ->catch(function (Exception $e) {
                throw $e;
            })
        ();
    }

    /**
     * @param ISql $sql
     * @return array
     * @throws DBALException
     */
    protected function _executeQuery(ISql $sql): array
    {
        try {
            return $this->getAdapter()->query($sql->getSentence(), $sql->getParams());
        } catch (Exception $_e) {
            throw new DBALException($_e->getMessage(), $_e->getCode(), $_e->getPrevious());
        }
    }

    /**
     * @param array $rawRows
     * @param IQuery $query
     * @return array
     * @throws Exception
     */
    protected function completeRawRows(array $rawRows, IQuery $query): array
    {
        /** @var array $rows */
        $rows = $rawRows;

        /** @var IField[] $fieldCallback */
        $fieldCallback = [];

        /** @var IField $field */
        foreach ($query->getFields() as $field) {
            if ($field->isCallback()) {
                $fieldCallback[] = $field;
            }
        }

        if (count($fieldCallback) === 0) {
            return $rows;
        }

        try {
            /** @var array $row */
            foreach ($rawRows as $index => $rawRow) {
                /** @var IField $field */
                foreach ($fieldCallback as $field) {
                    /** @var callable $callback */
                    $callback = $field->getCallback();
                    $rows[$index] = $callback($rawRow, $index, $rawRows);
                }
            }
        } catch (Exception $_e) {
            throw new Exception($_e->getMessage(), $_e->getCode(), $_e->getPrevious());
        }

        return $rows;
    }

    /**
     * @param string $king
     * @param array $rows
     * @param ISql $sql
     * @param float $time
     * @param string|int $affectedRows
     * @return IQueryDTO
     */
    protected function createDTO(string $king, array $rows, ISql $sql, float $time, $affectedRows = ''): IQueryDTO
    {
        $dto = new QueryDTO();
        $dto
            ->setKind($king)
            ->setRows($rows)
            ->setSql($sql)
            ->setResult($affectedRows)
            ->calculatePerformance($time + microtime(true));
        return $dto;
    }

    /**
     * @param IQueryDTO $dto
     * @param IQuery $query
     * @return IQueryDTO
     * @throws DBALException
     */
    protected function resolveFoundRows(IQueryDTO $dto, IQuery $query): IQueryDTO
    {
        if ($query->getFoundRows()) {
            $sql = new Sql();
            $sql->sentence = "SELECT FOUND_ROWS() AS found";
            $sql->params = [];
            $result = $this->_executeQuery($sql);
            $dto->setResult(intval(array_pop($result)['found']));
        }
        return $dto;
    }

    /**
     * @param string $king
     * @param ISql $sql
     * @return IQueryDTO
     * @throws DBALException
     * @throws Exception
     */
    public function executeNonQuery(string $king, ISql $sql): IQueryDTO
    {
        $time = -microtime(true);
        return Pipeline::try(
            function () {
            })
            ->tap(function () use ($sql) {
                $this->notify(Events::ON_BEFORE_EXECUTE_NON_QUERY, new EventQueryExecuteBeforePayload($sql));
            })
            ->then(function () use ($king, $sql) {
                return $this->_executeNonQuery($king, $sql);
            })
            ->then(function ($affectedRows) use ($king, $time, $sql) {
                return $this->createDTO($king, [], $sql, $time, $affectedRows);
            })
            ->then(function (IQueryDTO $dto) {
                return $dto;
            })
            ->tap(function (IQueryDTO $dto) {
                $this->log($dto);
            })
            ->tap(function (IQueryDTO $dto) {
                $this->notify(Events::ON_AFTER_EXECUTE_NON_QUERY, new EventQueryExecuteAfterPayload($dto));
            })
            ->tapCatch(function (DBALException $e) {
                $this->notify(Events::ON_ERROR, new EventQueryExecuteErrorPayload($e));
            })
            ->tapCatch(function (Exception $e) {
                $this->notify(Events::ON_ERROR, new EventQueryExecuteErrorPayload($e));
            })
            ->catch(function (DBALException $e) {
                throw $e;
            })
            ->catch(function (Exception $e) {
                throw $e;
            })
        ();
    }

    /**
     * @param string $king
     * @param ISql $sql
     * @return int|string
     * @throws DBALException
     */
    protected function _executeNonQuery(string $king, ISql $sql)
    {
        try {
            $affectedRows = $this->getAdapter()->nonQuery($sql->getSentence(), $sql->getParams());
            if ($king === IAdapter::CREATE) {
                $affectedRows = $this->getAdapter()->lastInsertId();
            }
        } catch (Exception $_e) {
            throw new DBALException($_e->getMessage(), $_e->getCode(), $_e->getPrevious());
        }
        return $affectedRows;
    }

    /**
     * @param IQueryDTO $dto
     */
    protected function log(IQueryDTO $dto)
    {
        if (
            !is_null($logger = $this->getLogger()) &&
            ($logger instanceof LoggerInterface)
        ) {
            $logger->info(json_encode($dto));
        }
    }

    /**
     * @param string $eventName
     * @param EventArgs $event
     * @return $this
     */
    protected function notify(string $eventName, EventArgs $event)
    {
        if (
            !is_null($eventManager = $this->getEventManager()) &&
            ($eventManager instanceof EventManager)
        ) {
            $eventManager->dispatchEvent($eventName, $event);
        }
        return $this;
    }
}