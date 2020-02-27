<?php
declare(strict_types=1);


namespace Tests\Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Adapter\Interfaces\IAdapter;
use Cratia\ORM\DBAL\QueryDTO;
use Cratia\ORM\DQL\Interfaces\ISql;
use Cratia\ORM\DQL\Sql;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

class QueryDTOTest extends PHPUnit_TestCase
{

    public function testConstructor1()
    {
        $time = -microtime(true);
        $sql = new Sql();
        $sql->sentence = "SELECT SQL_CALC_FOUND_ROWS t.* FROM {$_ENV['TABLE_TEST']} AS t LIMIT 20 OFFSET 0";
        $sql->params = [];

        $rows = [1, 2, 3, 4, 5, 6];

        $dto = new QueryDTO();
        $dto->setResult(count($rows));
        $dto->setRows($rows);
        $dto->setSql($sql);
        $dto->setKind(IAdapter::FETCH);

        $this->assertEqualsCanonicalizing($rows, $dto->getRows());
        $this->assertEquals(count($rows), $dto->getResult());
        $this->assertEquals($sql, $dto->getSql());
        $this->assertInstanceOf(ISql::class, $dto->getSql());
        $this->assertNull($dto->getPerformance());

        $this->assertEqualsCanonicalizing(
            '{"kind":"IAdapter::FETCH","result":6,"sql":"SELECT SQL_CALC_FOUND_ROWS t.* FROM analytics_dimension_entity AS t LIMIT 20 OFFSET 0","performance":null}',
            json_encode($dto)
        );

        $dto->calculatePerformance($time + microtime(true));

        $this->assertNotNull($dto->getPerformance());

        $this->assertStringContainsString(
            '{"kind":"IAdapter::FETCH","result":6,"sql":"SELECT SQL_CALC_FOUND_ROWS t.* FROM analytics_dimension_entity AS t LIMIT 20 OFFSET 0","performance":{"runtime":',
            json_encode($dto)
        );

    }
}