<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Interfaces;

use Doctrine\DBAL\DBALException;

/**
 * Interface IAdapter
 * @package Cratia\ORM\DBAL
 */
interface IAdapter
{
    /**
     * @param string $sentence
     * @param array $params
     * @param array $types
     * @return array
     * @throws DBALException
     */
    public function query(string $sentence, array $params = [], array $types = []): array;

    /**
     * @param string $sentence
     * @param array $params
     * @param array $types
     * @return int
     * @throws DBALException
     */
    public function nonQuery(string $sentence, array $params = [], array $types = []): int;

    /**
     * @return string
     */
    public function lastInsertId(): string;
}