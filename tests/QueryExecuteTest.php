<?php
declare(strict_types=1);


namespace Tests\Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Interfaces\IAdapter;
use Cratia\ORM\DBAL\Interfaces\IQueryDTO;
use Cratia\ORM\DBAL\Interfaces\IQueryPerformance;
use Cratia\ORM\DBAL\QueryExecute;
use Cratia\ORM\DQL\Field;
use Cratia\ORM\DQL\Query;
use Cratia\ORM\DQL\Sql;
use Cratia\ORM\DQL\Table;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DBALException;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\UidProcessor;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class QueryExecuteTest
 * @package Tests\Cratia\ORM\DBAL
 */
class QueryExecuteTest extends PHPUnit_TestCase
{
    /**
     * @throws DBALException
     */
    public function testExecute1()
    {
        $table = new Table($_ENV['TABLE_TEST'], "t");
        $query = new Query($table);
        $sql = new Sql();
        $sql->sentence = "SELECT SQL_CALC_FOUND_ROWS t.* FROM {$_ENV['TABLE_TEST']} AS t LIMIT 20 OFFSET 0";
        $sql->params = [];
        $this->assertEquals($sql, $query->toSQL());

        $dto = (new QueryExecute(new Adapter()))->executeQuery($query);;
        $this->assertEquals(20, $dto->getCount());
        $this->assertEquals(20, count($dto->getRows()));
        $this->assertIsArray($dto->getRows());
        $this->assertEquals($sql, $dto->getSql());
        $this->assertFalse($dto->isEmpty());
        $this->assertNotNull($dto->getPerformance());
    }

    /**
     * @throws DBALException
     */
    public function testExecute2()
    {
        $table1 = new Table($_ENV['TABLE_TEST'], "t");
        $field10 = Field::column($table1, "id");
        $field11 = Field::callback(
            function (array $rawRow) {
                $newRow = $rawRow;
                $newRow['connection_id'] = $rawRow['id'] . '- CONNECTION';
                return $newRow;
            },
            'connection_id');

        $query = new Query($table1);
        $query
            ->addField($field10)
            ->addField($field11)
            ->setLimit(1);

        $dto = (new QueryExecute(new Adapter()))->executeQuery($query);;
        $this->assertEquals(1, $dto->getCount());
        $this->assertEquals(1, count($dto->getRows()));
        $this->assertIsArray($dto->getRows());
        $this->assertNotNull($dto->getPerformance());
    }

    /**
     * @throws DBALException
     */
    public function testExecute3()
    {
        $this->expectException(DBALException::class);

        $table1 = new Table($_ENV['TABLE_TEST'], "t");
        $field10 = Field::column($table1, "_id"); //FIELD NO EXIST IN THE TABLE
        $field11 = Field::callback(
            function (array $rawRow) {
                $newRow = $rawRow;
                $newRow['connection_id'] = $rawRow['id'] . '- CONNECTION';
                return $newRow;
            },
            'connection_id');

        $query = new Query($table1);
        $query
            ->addField($field10)
            ->addField($field11)
            ->setLimit(1);

        (new QueryExecute(new Adapter()))->executeQuery($query);
    }


    public function testExecute4()
    {
        $error_msg = "Error in the " . __METHOD__ . "() -> Error expected.";
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error_msg);

        $table1 = new Table($_ENV['TABLE_TEST'], "t");
        $field10 = Field::column($table1, "id"); //FIELD NO EXIST IN THE TABLE
        $field11 = Field::callback(
            function () use ($error_msg) {
                throw new Exception($error_msg);
            },
            'connection_id');

        $query = new Query($table1);
        $query
            ->addField($field10)
            ->addField($field11)
            ->setLimit(1);

        (new QueryExecute(new Adapter()))->executeQuery($query);
    }

    /**
     * @throws DBALException
     */
    public function testExecute5()
    {
        $sql = new Sql();
        $sql->sentence = "INSERT INTO {$_ENV['TABLE_TEST']} (status, id_connection, network_service, network_params, created, updated, disabled, validity_period_to, validity_period_from, error_exception) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $sql->params = ['inactive', 1, 'TEST', 'TEST', '2020-02-20 18:53:16', null, 0, null, null, 'TEST'];

        $dto = (new QueryExecute(new Adapter()))->executeNonQuery(IAdapter::CREATE, $sql);

        $this->assertInstanceOf(IQueryDTO::class, $dto);
        $this->assertIsString($dto->getAffectedRows());
        $this->assertEqualsCanonicalizing(0, $dto->getCount());
        $this->assertEqualsCanonicalizing(0, $dto->getFound());
        $this->assertInstanceOf(IQueryPerformance::class, $dto->getPerformance());
        $this->assertNotNull($dto->getPerformance());
        $this->assertEqualsCanonicalizing([], $dto->getRows());
        $this->assertEqualsCanonicalizing($sql, $dto->getSql());
    }

    /**
     * @throws DBALException
     */
    public function testExecute6()
    {
        $this->expectException(DBALException::class);
        $sql = new Sql();
        $sql->sentence = "INSERT INTO (status, id_connection, network_service, network_params, created, updated, disabled, validity_period_to, validity_period_from, error_exception) VALUES (?, ?, ?, :network_params, ?, ?, ?, ?, ?, :error_exception);";
        $sql->params = ['inactive', 1, 'TEST', 'TEST', '2020-02-20 18:53:16', null, 0, null, null, 'TEST'];

        (new QueryExecute(new Adapter()))->executeNonQuery(IAdapter::CREATE, $sql);
    }

    public function testExecute7()
    {
        $sql = new Sql();
        $sql->sentence = "UPDATE {$_ENV['TABLE_TEST']} SET status = ?, id_connection = ? WHERE id = 1";
        $sql->params = ['inactive', 1];

        $dto = (new QueryExecute(new Adapter()))->executeNonQuery(IAdapter::UPDATE, $sql);

        $this->assertInstanceOf(IQueryDTO::class, $dto);
        $this->assertIsInt($dto->getAffectedRows());
        $this->assertEqualsCanonicalizing(0, $dto->getCount());
        $this->assertEqualsCanonicalizing(0, $dto->getFound());
        $this->assertInstanceOf(IQueryPerformance::class, $dto->getPerformance());
        $this->assertNotNull($dto->getPerformance());
        $this->assertEqualsCanonicalizing([], $dto->getRows());
        $this->assertEqualsCanonicalizing($sql, $dto->getSql());
    }

    /**
     * @throws DBALException
     */
    public function testExecute8()
    {
        $sql = new Sql();
        $sql->sentence = "INSERT INTO {$_ENV['TABLE_TEST']} (status, id_connection, network_service, network_params, created, updated, disabled, validity_period_to, validity_period_from, error_exception) VALUES (:x1, :x2,:x3,:x4,:x5,:x6,:x7,:x8,:x9,:x10);";
        $sql->params = [
            'x1' => 'inactive',
            'x2' => true,
            'x3' => 'TEST',
            'x4' => 'TEST',
            'x5' => '2020-02-20 18:53:16',
            'x6' => null,
            'x7' => 0,
            'x8' => null,
            'x9' => null,
            'x10' => 'TEST'
        ];

        $dto = (new QueryExecute(new Adapter()))->executeNonQuery(IAdapter::CREATE, $sql);

        $this->assertInstanceOf(IQueryDTO::class, $dto);
        $this->assertIsString($dto->getAffectedRows());
        $this->assertEqualsCanonicalizing(0, $dto->getCount());
        $this->assertEqualsCanonicalizing(0, $dto->getFound());
        $this->assertInstanceOf(IQueryPerformance::class, $dto->getPerformance());
        $this->assertNotNull($dto->getPerformance());
        $this->assertEqualsCanonicalizing([], $dto->getRows());
        $this->assertEqualsCanonicalizing($sql, $dto->getSql());

        $this->assertInstanceOf(LoggerInterface::class, (new Adapter())->getLogger());
    }

    /**
     * @throws DBALException
     */
    public function testExecute9()
    {
        $sql = new Sql();
        $sql->sentence = "INSERT INTO {$_ENV['TABLE_TEST']} (status, id_connection, network_service, network_params, created, updated, disabled, validity_period_to, validity_period_from, error_exception) VALUES (:x1, :x2,:x3,:x4,:x5,:x6,:x7,:x8,:x9,:x10);";
        $sql->params = [
            'x1' => 'inactive',
            'x2' => true,
            'x3' => 'TEST',
            'x4' => 'TEST',
            'x5' => '2020-02-20 18:53:16',
            'x6' => null,
            'x7' => 0,
            'x8' => null,
            'x9' => null,
            'x10' => 'TEST'
        ];

        $adapter = new Adapter();

        $logger = new Logger('orm-dbal');
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new MemoryUsageProcessor());
        $logger->pushProcessor(new IntrospectionProcessor());
        $handler = new StreamHandler('php://stdout', Logger::DEBUG);
        $logger->pushHandler($handler);

        $adapter->setLogger($logger);
        $this->assertInstanceOf(LoggerInterface::class, $adapter->getLogger());

        $executer = new QueryExecute($adapter);

        $this->assertNull($executer->getLogger());

        $executer->setLogger($logger);

        $this->assertNotNull($executer->getLogger());
        $this->assertInstanceOf(LoggerInterface::class, $executer->getLogger());

        $dto = $executer->executeNonQuery(IAdapter::CREATE, $sql);

        $this->assertInstanceOf(IQueryDTO::class, $dto);
        $this->assertIsString($dto->getAffectedRows());
        $this->assertEqualsCanonicalizing(0, $dto->getCount());
        $this->assertEqualsCanonicalizing(0, $dto->getFound());
        $this->assertInstanceOf(IQueryPerformance::class, $dto->getPerformance());
        $this->assertNotNull($dto->getPerformance());
        $this->assertEqualsCanonicalizing([], $dto->getRows());
        $this->assertEqualsCanonicalizing($sql, $dto->getSql());
        $this->assertInstanceOf(LoggerInterface::class, $executer->getLogger());
        $this->assertInstanceOf(IAdapter::class, $executer->getAdapter());
    }

    /**
     * @throws DBALException
     */
    public function testExecute10()
    {
        $sql = new Sql();
        $sql->sentence = "INSERT INTO {$_ENV['TABLE_TEST']} (status, id_connection, network_service, network_params, created, updated, disabled, validity_period_to, validity_period_from, error_exception) VALUES (:x1, :x2,:x3,:x4,:x5,:x6,:x7,:x8,:x9,:x10);";
        $sql->params = [
            'x1' => 'inactive',
            'x2' => true,
            'x3' => 'TEST',
            'x4' => 'TEST',
            'x5' => '2020-02-20 18:53:16',
            'x6' => null,
            'x7' => 0,
            'x8' => null,
            'x9' => null,
            'x10' => 'TEST'
        ];

        $adapter = new Adapter();

        $logger = new Logger('orm-dbal');
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new MemoryUsageProcessor());
        $logger->pushProcessor(new IntrospectionProcessor());
        $handler = new StreamHandler('php://stdout', Logger::DEBUG);
        $logger->pushHandler($handler);

        $eventManager = new EventManager();
        $subscriber = new EventSubscriberAdapter();
        $eventManager->addEventSubscriber($subscriber);

        $adapter->setLogger($logger);
        $adapter->setEventManager($eventManager);

        $this->assertInstanceOf(LoggerInterface::class, $adapter->getLogger());
        $this->assertInstanceOf(EventManager::class, $adapter->getEventManager());

        $executer = new QueryExecute($adapter);

        $this->assertNull($executer->getLogger());

        $executer->setLogger($logger);

        $this->assertNotNull($executer->getLogger());
        $this->assertInstanceOf(LoggerInterface::class, $executer->getLogger());

        $dto = $executer->executeNonQuery(IAdapter::CREATE, $sql);

        $this->assertInstanceOf(IQueryDTO::class, $dto);
        $this->assertIsString($dto->getAffectedRows());
        $this->assertEqualsCanonicalizing(0, $dto->getCount());
        $this->assertEqualsCanonicalizing(0, $dto->getFound());
        $this->assertInstanceOf(IQueryPerformance::class, $dto->getPerformance());
        $this->assertNotNull($dto->getPerformance());
        $this->assertEqualsCanonicalizing([], $dto->getRows());
        $this->assertEqualsCanonicalizing($sql, $dto->getSql());
        $this->assertInstanceOf(LoggerInterface::class, $executer->getLogger());
        $this->assertInstanceOf(IAdapter::class, $executer->getAdapter());

        $this->assertFalse($subscriber->onError);
        $this->assertFalse($subscriber->onAfterQuery);
        $this->assertFalse($subscriber->onBeforeQuery);
        $this->assertTrue($subscriber->onAfterNonQuery);
        $this->assertTrue($subscriber->onBeforeNonQuery);
    }

    /**
     * @throws DBALException
     */
    public function testExecute11()
    {
        $table = new Table($_ENV['TABLE_TEST'], "t");
        $query = new Query($table);
        $sql = new Sql();
        $sql->sentence = "SELECT SQL_CALC_FOUND_ROWS t.* FROM {$_ENV['TABLE_TEST']} AS t LIMIT 20 OFFSET 0";
        $sql->params = [];
        $this->assertEquals($sql, $query->toSQL());

        $eventManager = new EventManager();
        $subscriber = new EventSubscriberAdapter();

        $this->assertFalse($subscriber->onError);
        $this->assertFalse($subscriber->onAfterQuery);
        $this->assertFalse($subscriber->onBeforeQuery);
        $this->assertFalse($subscriber->onAfterNonQuery);
        $this->assertFalse($subscriber->onBeforeNonQuery);

        $eventManager->addEventSubscriber($subscriber);

        $adapter = new Adapter();
        $adapter->setEventManager($eventManager);

        $dto = (new QueryExecute($adapter))->executeQuery($query);;
        $this->assertEquals(20, $dto->getCount());
        $this->assertEquals(20, count($dto->getRows()));
        $this->assertIsArray($dto->getRows());
        $this->assertEquals($sql, $dto->getSql());
        $this->assertFalse($dto->isEmpty());
        $this->assertNotNull($dto->getPerformance());

        $this->assertFalse($subscriber->onError);
        $this->assertTrue($subscriber->onAfterQuery);
        $this->assertTrue($subscriber->onBeforeQuery);
        $this->assertFalse($subscriber->onAfterNonQuery);
        $this->assertFalse($subscriber->onBeforeNonQuery);
    }

    /**
     * @throws DBALException
     */
    public function testExecute12()
    {

        $table1 = new Table($_ENV['TABLE_TEST'], "t");
        $field10 = Field::column($table1, "_id"); //FIELD NO EXIST IN THE TABLE
        $field11 = Field::callback(
            function (array $rawRow) {
                $newRow = $rawRow;
                $newRow['connection_id'] = $rawRow['id'] . '- CONNECTION';
                return $newRow;
            },
            'connection_id');

        $query = new Query($table1);
        $query
            ->addField($field10)
            ->addField($field11)
            ->setLimit(1);

        $eventManager = new EventManager();
        $subscriber = new EventSubscriberAdapter();

        $this->assertFalse($subscriber->onError);
        $this->assertFalse($subscriber->onAfterQuery);
        $this->assertFalse($subscriber->onBeforeQuery);
        $this->assertFalse($subscriber->onAfterNonQuery);
        $this->assertFalse($subscriber->onBeforeNonQuery);

        $eventManager->addEventSubscriber($subscriber);

        $adapter = new Adapter();
        $adapter->setEventManager($eventManager);

        try {
            (new QueryExecute($adapter))->executeQuery($query);
        } catch (Exception $e) {

        }

        $this->assertTrue($subscriber->onError);
        $this->assertFalse($subscriber->onAfterQuery);
        $this->assertTrue($subscriber->onBeforeQuery);
        $this->assertFalse($subscriber->onAfterNonQuery);
        $this->assertFalse($subscriber->onBeforeNonQuery);

    }

    /**
     * @throws DBALException
     */
    public function testExecute13()
    {
        $table = new Table($_ENV['TABLE_TEST'], "t");
        $query = new Query($table);
        $sql = new Sql();
        $sql->sentence = "SELECT SQL_CALC_FOUND_ROWS t.* FROM {$_ENV['TABLE_TEST']} AS t LIMIT 20 OFFSET 0";
        $sql->params = [];
        $this->assertEquals($sql, $query->toSQL());

        $eventManager = new EventManager();
        $subscriber = new EventSubscriberQueryExecute();

        $this->assertFalse($subscriber->onError);
        $this->assertFalse($subscriber->onBeforeExecuteQuery);
        $this->assertFalse($subscriber->onAfterExecuteQuery);
        $this->assertFalse($subscriber->onBeforeExecuteNonQuery);
        $this->assertFalse($subscriber->onAfterExecuteNonQuery);

        $eventManager->addEventSubscriber($subscriber);

        $dto = (new QueryExecute(new Adapter(), null, $eventManager))->executeQuery($query);

        $this->assertEquals(20, $dto->getCount());
        $this->assertEquals(20, count($dto->getRows()));
        $this->assertIsArray($dto->getRows());
        $this->assertEquals($sql, $dto->getSql());
        $this->assertFalse($dto->isEmpty());
        $this->assertNotNull($dto->getPerformance());

        $this->assertFalse($subscriber->onError);
        $this->assertTrue($subscriber->onBeforeExecuteQuery);
        $this->assertTrue($subscriber->onAfterExecuteQuery);
        $this->assertFalse($subscriber->onBeforeExecuteNonQuery);
        $this->assertFalse($subscriber->onAfterExecuteNonQuery);

    }

    /**
     * @throws DBALException
     */
    public function testExecute14()
    {

        $table1 = new Table($_ENV['TABLE_TEST'], "t");
        $field10 = Field::column($table1, "_id"); //FIELD NO EXIST IN THE TABLE
        $field11 = Field::callback(
            function (array $rawRow) {
                $newRow = $rawRow;
                $newRow['connection_id'] = $rawRow['id'] . '- CONNECTION';
                return $newRow;
            },
            'connection_id');

        $query = new Query($table1);
        $query
            ->addField($field10)
            ->addField($field11)
            ->setLimit(1);

        $eventManager = new EventManager();
        $subscriber = new EventSubscriberQueryExecute();

        $this->assertFalse($subscriber->onError);
        $this->assertFalse($subscriber->onBeforeExecuteQuery);
        $this->assertFalse($subscriber->onAfterExecuteQuery);
        $this->assertFalse($subscriber->onBeforeExecuteNonQuery);
        $this->assertFalse($subscriber->onAfterExecuteNonQuery);

        $eventManager->addEventSubscriber($subscriber);

        $adapter = new Adapter();

        try {
            (new QueryExecute($adapter, null, null))
                ->setEventManager($eventManager)
                ->executeQuery($query);
        } catch (Exception $e) {

        }

        $this->assertTrue($subscriber->onError);
        $this->assertTrue($subscriber->onBeforeExecuteQuery);
        $this->assertFalse($subscriber->onAfterExecuteQuery);
        $this->assertFalse($subscriber->onBeforeExecuteNonQuery);
        $this->assertFalse($subscriber->onAfterExecuteNonQuery);

    }

    /**
     * @throws DBALException
     */
    public function testExecute15()
    {
        $sql = new Sql();
        $sql->sentence = "INSERT INTO {$_ENV['TABLE_TEST']} (status, id_connection, network_service, network_params, created, updated, disabled, validity_period_to, validity_period_from, error_exception) VALUES (:x1, :x2,:x3,:x4,:x5,:x6,:x7,:x8,:x9,:x10);";
        $sql->params = [
            'x1' => 'inactive',
            'x2' => true,
            'x3' => 'TEST',
            'x4' => 'TEST',
            'x5' => '2020-02-20 18:53:16',
            'x6' => null,
            'x7' => 0,
            'x8' => null,
            'x9' => null,
            'x10' => 'TEST'
        ];

        $adapter = new Adapter();

        $eventManager = new EventManager();
        $subscriber = new EventSubscriberQueryExecute();

        $this->assertFalse($subscriber->onError);
        $this->assertFalse($subscriber->onBeforeExecuteQuery);
        $this->assertFalse($subscriber->onAfterExecuteQuery);
        $this->assertFalse($subscriber->onBeforeExecuteNonQuery);
        $this->assertFalse($subscriber->onAfterExecuteNonQuery);

        $eventManager->addEventSubscriber($subscriber);

        $executer = new QueryExecute($adapter);

        $this->assertNull($executer->getLogger());
        $this->assertNull($executer->getEventManager());

        $executer->setEventManager($eventManager);

        $this->assertNotNull($executer->getEventManager());
        $this->assertInstanceOf(EventManager::class, $executer->getEventManager());

        $dto = $executer->executeNonQuery(IAdapter::CREATE, $sql);

        $this->assertInstanceOf(IQueryDTO::class, $dto);
        $this->assertIsString($dto->getAffectedRows());
        $this->assertEqualsCanonicalizing(0, $dto->getCount());
        $this->assertEqualsCanonicalizing(0, $dto->getFound());
        $this->assertInstanceOf(IQueryPerformance::class, $dto->getPerformance());
        $this->assertNotNull($dto->getPerformance());
        $this->assertEqualsCanonicalizing([], $dto->getRows());
        $this->assertEqualsCanonicalizing($sql, $dto->getSql());
        $this->assertInstanceOf(IAdapter::class, $executer->getAdapter());
        $this->assertInstanceOf(EventManager::class, $executer->getEventManager());

        $this->assertFalse($subscriber->onError);
        $this->assertFalse($subscriber->onBeforeExecuteQuery);
        $this->assertFalse($subscriber->onAfterExecuteQuery);
        $this->assertTrue($subscriber->onBeforeExecuteNonQuery);
        $this->assertTrue($subscriber->onAfterExecuteNonQuery);
    }
}