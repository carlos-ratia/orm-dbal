<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL;

use Cratia\Common\Functions;
use Cratia\ORM\DBAL\Adapter\Interfaces\ISqlPerformance;
use Cratia\ORM\DBAL\Adapter\SqlPerformance;
use Cratia\ORM\DBAL\Interfaces\IQueryDTO;
use Cratia\ORM\DQL\Interfaces\ISql;
use Cratia\ORM\DQL\Sql;
use JsonSerializable;

/**
 * Class QueryDTO
 * @package Cratia\ORM\DBAL
 */
class QueryDTO implements IQueryDTO, JsonSerializable
{
    /**
     * @var int|string|null
     */
    private $result;

    /**
     * @var array
     */
    private $rows;

    /**
     * @var ISql
     */
    private $sql;

    /**
     * @var |null
     */
    private $performance;

    /**
     * @var null|null
     */
    private $kind;

    /**
     * QueryDTO constructor.
     */
    public function __construct()
    {
        $this->kind = null;
        $this->result = null;
        $this->sql = new Sql();
        $this->performance = null;
        $this->rows = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * {@inheritDoc}
     */
    public function setResult($result): IQueryDTO
    {
        $this->result = $result;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount(): int
    {
        return count($this->getRows());
    }

    /**
     * {@inheritDoc}
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * {@inheritDoc}
     */
    public function setRows(array $rows): IQueryDTO
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSql(): ISql
    {
        return $this->sql;
    }

    /**
     * {@inheritDoc}
     */
    public function setSql(ISql $sql): IQueryDTO
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return $this->getCount() === 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getPerformance(): ?ISqlPerformance
    {
        return $this->performance;
    }

    /**
     * {@inheritDoc}
     */
    public function setPerformance(ISqlPerformance $performance): IQueryDTO
    {
        $this->performance = $performance;
        return $this;
    }

    /**
     * @param float $time
     * @return $this
     */
    public function calculatePerformance(float $time): IQueryDTO
    {
        $this->performance = new SqlPerformance($time);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getKind(): ?string
    {
        return $this->kind;
    }

    /**
     * {@inheritDoc}
     */
    public function setKind(string $kind): IQueryDTO
    {
        $this->kind = $kind;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return
            [
                'kind' => $this->getKind(),
                'result' => $this->getResult(),
                'sql' => Functions::formatSql($this->getSql()->getSentence(), $this->getSql()->getParams()),
                'performance' => $this->getPerformance()
            ];
    }
}