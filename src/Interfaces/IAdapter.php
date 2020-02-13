<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Interfaces;

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
     */
    public function query(string $sentence, array $params = [], array $types = []): array;
}