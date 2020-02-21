<?php
declare(strict_types=1);


namespace Tests\Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Interfaces\IAdapter;
use Cratia\ORM\DBAL\QueryDTO;
use Cratia\ORM\DQL\Interfaces\ISql;
use Cratia\ORM\DQL\Sql;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

class QueryDTOTest extends PHPUnit_TestCase
{

    public function testConstructor1()
    {
        $sql = new Sql();
        $sql->sentence = "SELECT SQL_CALC_FOUND_ROWS t.* FROM {$_ENV['TABLE_TEST']} AS t LIMIT 20 OFFSET 0";
        $sql->params = [];

        $rows = [1, 2, 3, 4, 5, 6];

        $dto = new QueryDTO();
        $dto->setFound(count($rows));
        $dto->setRows($rows);
        $dto->setSql($sql);
        $dto->setKing(IAdapter::FETCH);

        $this->assertEqualsCanonicalizing($rows, $dto->getRows());
        $this->assertEquals(count($rows), $dto->getFound());
        $this->assertEquals($sql, $dto->getSql());
        $this->assertInstanceOf(ISql::class, $dto->getSql());
        $this->assertNull($dto->getPerformance());
        $this->assertEmpty($dto->getAffectedRows());

        $this->assertEqualsCanonicalizing(
            '{"king":"IAdapter::FETCH","found":6,"affectedRows":0,"sql":"SELECT SQL_CALC_FOUND_ROWS t.* FROM analytics_dimension_entity AS t LIMIT 20 OFFSET 0","performance":null}',
            json_encode($dto)
        );

        $dto->calculatePerformance(-microtime(true));

        $this->assertNotNull($dto->getPerformance());

        $this->assertStringContainsString(
            '{"king":"IAdapter::FETCH","found":6,"affectedRows":0,"sql":"SELECT SQL_CALC_FOUND_ROWS t.* FROM analytics_dimension_entity AS t LIMIT 20 OFFSET 0","performance":',
            json_encode($dto)
        );

    }
}