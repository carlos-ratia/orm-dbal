<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Interfaces\IAdapter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\FetchMode;

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
     * Adapter constructor.
     * @param array $params
     * @throws DBALException
     */
    public function __construct(array $params)
    {
        $this->connection = DriverManager::getConnection($params);
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param array $types
     * @return mixed[]
     * @throws DBALException
     */
    public function query(string $sql, array $params = [], array $types = []): array
    {
        return $this
            ->getConnection()
            ->executeQuery($sql, $params, $types)
            ->fetchAll(FetchMode::ASSOCIATIVE);
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
        $affectedRows = $this->getConnection()->executeUpdate($sentence, $params, $types);
        return $affectedRows;
    }

    /**
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }
}