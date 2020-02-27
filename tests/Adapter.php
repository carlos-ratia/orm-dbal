<?php
declare(strict_types=1);


namespace Tests\Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Adapter\MysqlAdapter;
use Doctrine\DBAL\DBALException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;

/**
 * Class Adapter
 * @package Tests\Cratia\ORM\DBAL
 */
class Adapter extends MysqlAdapter
{
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
        $logger = new Logger('orm-dbal');
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new MemoryUsageProcessor());
        $logger->pushProcessor(new IntrospectionProcessor());
        $handler = new StreamHandler('php://stdout', Logger::DEBUG);
        $logger->pushHandler($handler);
        parent::__construct($connectionParams, $logger);
    }
}