<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Interfaces;


use Cratia\ORM\DBAL\Adapter\Interfaces\ISqlPerformance;
use Cratia\ORM\DQL\Interfaces\ISql;

/**
 * Interface IQueryDTO
 * @package Cratia\ORM\DBAL\Interfaces
 */
interface IQueryDTO
{
    /**
     * @return mixed
     */
    public function getResult();

    /**
     * @param mixed $result
     * @return IQueryDTO
     */
    public function setResult($result): IQueryDTO;

    /**
     * @return int
     */
    public function getCount(): int;

    /**
     * @return array
     */
    public function getRows(): array;

    /**
     * @param array $rows
     * @return IQueryDTO
     */
    public function setRows(array $rows): IQueryDTO;

    /**
     * @return ISql
     */
    public function getSql(): ISql;

    /**
     * @param ISql $sql
     * @return IQueryDTO
     */
    public function setSql(ISql $sql): IQueryDTO;

    /**
     * @return ISqlPerformance|null
     */
    public function getPerformance(): ?ISqlPerformance;

    /**
     * @param ISqlPerformance $performance
     * @return IQueryDTO
     */
    public function setPerformance(ISqlPerformance $performance): IQueryDTO;

    /**
     * @return bool
     */
    public function isEmpty(): bool;
}