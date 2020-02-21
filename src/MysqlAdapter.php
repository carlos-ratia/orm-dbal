<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Common\Functions;
use Cratia\ORM\DBAL\Interfaces\IAdapter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\FetchMode;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use stdClass;

/**
 * Class DataBaseAdapter
 * @package App\Persistence
 */
class MysqlAdapter implements IAdapter
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * Adapter constructor.
     * @param array $params
     * @param LoggerInterface|null $logger
     * @throws DBALException
     */
    public function __construct(array $params, LoggerInterface $logger = null)
    {
        $this->connection = DriverManager::getConnection($params);
        $this->logger = $logger;
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param LoggerInterface $logger
     * @return MysqlAdapter
     */
    public function setLogger(LoggerInterface $logger): MysqlAdapter
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param string $sentence
     * @param array $params
     * @param array $types
     * @return mixed[]
     * @throws DBALException
     */
    public function query(string $sentence, array $params = [], array $types = []): array
    {
        $sentence = trim($sentence);

        try {
            $time = -microtime(true);

            $result = $this
                ->getConnection()
                ->executeQuery($sentence, $params, $types)
                ->fetchAll(FetchMode::ASSOCIATIVE);

            $time += microtime(true);
            $this->logPerformance($sentence, $params, $time);
        } catch (Exception $_e) {
            $this->logError(__METHOD__, $_e);
            $e = new DBALException($_e->getMessage(), $_e->getCode(), $_e->getPrevious());
            throw $e;
        }
        return $result;
    }

    /**
     * @param string $sentence
     * @param array $params
     * @param array $types
     * @return int
     * @throws DBALException
     */
    public function nonQuery(string $sentence, array $params = [], array $types = []): int
    {
        $sentence = trim($sentence);
        try {
            $time = -microtime(true);

            $affectedRows = $this->getConnection()->executeUpdate($sentence, $params, $types);

            $time += microtime(true);
            $this->logPerformance($sentence, $params, $time);
        } catch (Exception $_e) {
            $this->logError(__METHOD__, $_e);
            $e = new DBALException($_e->getMessage(), $_e->getCode(), $_e->getPrevious());
            throw $e;
        }
        return $affectedRows;
    }

    /**
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * @param string $sentence
     * @param array $params
     * @param float $time
     */
    private function logPerformance(string $sentence, array $params, float $time): void
    {
        $performance = new stdClass;
        $performance->sql = Functions::formatSql($sentence, $params);
        $performance->run_time = Functions::pettyRunTime($time);
        $performance->memmory = intval(memory_get_usage() / 1024 / 1024) . ' MB';
        $this->logDebug(json_encode($performance));
    }

    /**
     * @param $level
     * @param string $message
     */
    protected function log($level, string $message): void
    {
        if (
            !is_null($logger = $this->getLogger()) &&
            ($logger instanceof LoggerInterface)
        ) {
            $logger->log($level, $message);
        }
    }

    /**
     * @param string $location
     * @param Exception $e
     */
    protected function logError(string $location, Exception $e)
    {
        $this->log(LogLevel::ERROR, "Error in the {$location}(...) -> {$e->getMessage()}");
    }

    /**
     * @param string $message
     */
    protected function logDebug(string $message): void
    {
        $this->log(LogLevel::DEBUG, $message);
    }
}