<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Interfaces;

use Cratia\ORM\DQL\Interfaces\ISql;

/**
 * Interface IQueryDTO
 * @package Cratia\ORM\DBAL
 */
interface IQueryDTO
{
    /**
     * @return int
     */
    public function getFound(): int;

    /**
     * @param int $found
     * @return IQueryDTO
     */
    public function setFound(int $found): IQueryDTO;

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
     * @return bool
     */
    public function isEmpty(): bool;
}