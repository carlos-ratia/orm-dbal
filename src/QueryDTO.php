<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL;

use Cratia\ORM\DBAL\Common\Functions;
use Cratia\ORM\DBAL\Interfaces\IQueryDTO;
use Cratia\ORM\DBAL\Interfaces\IQueryPerformance;
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
     * @var IQueryPerformance|null
     */
    private $performance;

    /**
     * @var int|string
     */
    private $affectedRows;

    /**
     * QueryDTO constructor.
     */
    public function __construct()
    {
        $this->found = 0;
        $this->rows = [];
        $this->sql = new Sql();
        $this->performance = null;
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

    /**
     * @return IQueryPerformance|null
     */
    public function getPerformance(): ?IQueryPerformance
    {
        return $this->performance;
    }

    /**
     * @param IQueryPerformance $performance
     * @return IQueryDTO
     */
    public function setPerformance(IQueryPerformance $performance): IQueryDTO
    {
        $this->performance = $performance;
        return $this;
    }

    /**
     * @param float $time
     * @return IQueryDTO
     */
    public function calculatePerformance(float $time): IQueryDTO
    {
        $time += microtime(true);
        $time = Functions::pettyRunTime($time);
        $memory = intval(memory_get_usage() / 1024 / 1024) . ' MB';
        $this->setPerformance(new QueryPerformance($time, $memory));
        return $this;
    }

    /**
     * @return int|string
     */
    public function getAffectedRows()
    {
        return $this->affectedRows;
    }

    /**
     * @param int|string $affectedRows
     * @return QueryDTO
     */
    public function setAffectedRows($affectedRows): IQueryDTO
    {
        $this->affectedRows = $affectedRows;
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
                'found' => $this->getFound(),
                'sql' => $this->getSql(),
                'performance' => $this->getPerformance()
            ];
    }
}