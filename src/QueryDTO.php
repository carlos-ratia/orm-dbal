<?php
declare(strict_types=1);


namespace  Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Interfaces\IQueryDTO;
use Cratia\ORM\DQL\Interfaces\ISql;
use Cratia\ORM\DQL\Sql;

/**
 * Class QueryDTO
 * @package Cratia\ORM\DBAL
 */
class QueryDTO implements IQueryDTO
{
    /**
     * @var int
     */
    private $found;

    /**
     * @var array
     */
    private $rows;

    /**
     * @var ISql
     */
    private $sql;

    /**
     * QueryDTO constructor.
     */
    public function __construct()
    {
        $this->found = -1;
        $this->rows = [];
        $this->sql = new Sql();
    }

    /**
     * @return int
     */
    public function getFound(): int
    {
        return $this->found;
    }

    /**
     * @param int $found
     * @return QueryDTO
     */
    public function setFound(int $found): IQueryDTO
    {
        $this->found = $found;
        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return count($this->getRows());
    }

    /**
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @param array $rows
     * @return QueryDTO
     */
    public function setRows(array $rows): IQueryDTO
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * @return ISql
     */
    public function getSql(): ISql
    {
        return $this->sql;
    }

    /**
     * @param ISql $sql
     * @return QueryDTO
     */
    public function setSql(ISql $sql): IQueryDTO
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->getCount() === 0;
    }
}