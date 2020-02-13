<?php
declare(strict_types=1);


namespace Test\Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Interfaces\IAdapter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\FetchMode;

/**
 * Class Adapter
 * @package Test\Cratia\ORM\DBAL
 */
class Adapter implements IAdapter
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * Adapter constructor.
     * @throws DBALException
     */
    public function __construct()
    {
        $connectionParams = array(
            'dbname' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
            'host' => $_ENV['DB_HOST'],
            'driver' => 'pdo_mysql',
            'charset' => $_ENV['DB_CHARSET']
        );
        $this->connection = DriverManager::getConnection($connectionParams);
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
}