<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Interfaces;

use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;

/**
 * Interface IAdapter
 * @package Cratia\ORM\DBAL
 */
interface IAdapter
{
    const FETCH = 'IAdapter::FETCH';
    const CREATE = 'IAdapter::CREATE';
    const UPDATE = 'IAdapter::UPDATE';
    const DELETE = 'IAdapter::DELETE';

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

    /**
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface;
}