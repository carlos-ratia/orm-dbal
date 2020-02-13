<?php
declare(strict_types=1);


namespace Test\Cratia\ORM\DBAL;


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
 * @package Test\Cratia\ORM\DBAL
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

        $dto = (new QueryExecute(new Adapter()))->execute($query);;
        $this->assertEquals(20, $dto->getCount());
        $this->assertEquals(20, count($dto->getRows()));
        $this->assertIsArray($dto->getRows());
        $this->assertEquals($sql, $dto->getSql());
        $this->assertFalse($dto->isEmpty());
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

        $dto = (new QueryExecute(new Adapter()))->execute($query);;
        $this->assertEquals(1, $dto->getCount());
        $this->assertEquals(1, count($dto->getRows()));
        $this->assertIsArray($dto->getRows());
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

        (new QueryExecute(new Adapter()))->execute($query);
    }


    public function testExecute4()
    {
        $error_msg = "Error in the " . __METHOD__ . "() -> Error expected.";
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error_msg);

        $table1 = new Table($_ENV['TABLE_TEST'], "t");
        $field10 = Field::column($table1, "id"); //FIELD NO EXIST IN THE TABLE
        $field11 = Field::callback(
            function (array $_) use ($error_msg) {
                throw new Exception($error_msg);
            },
            'connection_id');

        $query = new Query($table1);
        $query
            ->addField($field10)
            ->addField($field11)
            ->setLimit(1);

        (new QueryExecute(new Adapter()))->execute($query);
    }
}