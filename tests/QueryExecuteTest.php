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
use Doctrine\DBAL\DBALException;
use Exception;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

/**
 * Class QueryExecuteTest
 * @package Tests\Cratia\ORM\DBAL
 */
class QueryExecuteTest extends PHPUnit_TestCase
{
    /**
     * @throws \Doctrine\DBAL\DBALException
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
     * @throws \Doctrine\DBAL\DBALException
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
     * @throws \Doctrine\DBAL\DBALException
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

    public function testExecute6()
    {
        $this->expectException(DBALException::class);
        $sql = new Sql();
        $sql->sentence = "INSERT INTO (status, id_connection, network_service, network_params, created, updated, disabled, validity_period_to, validity_period_from, error_exception) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
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
}