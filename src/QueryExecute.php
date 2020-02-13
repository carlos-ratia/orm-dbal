<?php
declare(strict_types=1);


use Cratia\ORM\DBAL\Interfaces\IAdapter;
use Cratia\ORM\DBAL\Interfaces\IQueryDTO;
use Cratia\ORM\DBAL\QueryDTO;
use Cratia\ORM\DQL\Interfaces\IField;
use Cratia\ORM\DQL\Interfaces\IQuery;
use Cratia\ORM\DQL\Interfaces\ISql;
use Cratia\ORM\DQL\Sql;
use Cratia\Pipeline;
use Doctrine\DBAL\DBALException;

/**
 * Class QueryExecute
 */
class QueryExecute
{
    /**
     * @var IAdapter
     */
    private $adapter;

    public function __construct(IAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return IAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param IQuery $query
     * @return IQueryDTO
     */
    public function execute(IQuery $query): IQueryDTO
    {
        /** @var ISql $sql */
        $sql = $query->toSQL();
        return Pipeline::try(
            function () use ($sql) {
                return $this->executeQuery($sql);
            })
            ->then(function (array $rawRows) use ($query) {
                return $this->completeRawRows($rawRows, $query);
            })
            ->then(function (array $rows) use ($sql) {
                return $this->createDTO($rows, $sql);
            })
            ->then(function (IQueryDTO $dto) use ($query) {
                return $this->getFoundRows($dto, $query);
            })
            ->then(function (IQueryDTO $dto) use ($query) {
                return $dto;
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
    protected function executeQuery(ISql $sql): array
    {
        try {
            return $this->getAdapter()->query($sql->getSentence(), $sql->getParams());
        } catch (Exception $_e) {
            $e = new DBALException($_e->getMessage(), $_e->getCode(), $_e->getPrevious());
            throw $e;
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
            $e = new Exception($_e->getMessage(), $_e->getCode(), $_e->getPrevious());
            throw $e;
        }

        return $rows;
    }

    /**
     * @param array $rows
     * @param ISql $sql
     * @return IQueryDTO
     */
    protected function createDTO(array $rows, ISql $sql): IQueryDTO
    {
        $dto = new QueryDTO();
        $dto->setRows($rows)
            ->setSql($sql);
        return $dto;
    }

    /**
     * @param IQueryDTO $dto
     * @param IQuery $query
     * @return IQueryDTO
     * @throws DBALException
     */
    protected function getFoundRows(IQueryDTO $dto, IQuery $query): IQueryDTO
    {
        if ($query->getFoundRows()) {
            $sql = new Sql();
            $sql->sentence = "SELECT FOUND_ROWS() AS found";
            $sql->params = [];
            $result = $this->executeQuery($sql);
            $dto->setFound(intval(array_pop($result)['found']));
        }
        return $dto;
    }
}