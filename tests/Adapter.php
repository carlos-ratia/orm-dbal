<?php
declare(strict_types=1);


namespace Tests\Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Interfaces\IAdapter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\FetchMode;

/**
 * Class Adapter
 * @package Tests\Cratia\ORM\DBAL
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
     * @param string $sentence
     * @param array $params
     * @param array $types
     * @return mixed[]
     * @throws DBALException
     */
    public function query(string $sentence, array $params = [], array $types = []): array
    {
        $sentence = trim($sentence);
        return $this
            ->getConnection()
            ->executeQuery($sentence, $params, $types)
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