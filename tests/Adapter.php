<?php
declare(strict_types=1);


namespace Tests\Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Interfaces\IAdapter;
use Cratia\ORM\DBAL\MysqlAdapter;
use Doctrine\DBAL\DBALException;

/**
 * Class Adapter
 * @package Tests\Cratia\ORM\DBAL
 */
class Adapter implements IAdapter
{
    /**
     * @var MysqlAdapter
     */
    private $adapter;

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
        $this->adapter = new MysqlAdapter($connectionParams);
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
        return $this->adapter->query($sentence, $params, $types);
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
        $affectedRows = $this->adapter->nonQuery($sentence, $params, $types);
        return $affectedRows;
    }

    /**
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->adapter->lastInsertId();
    }
}