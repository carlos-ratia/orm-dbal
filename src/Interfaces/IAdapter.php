<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Interfaces;

/**
 * Interface IAdapter
 * @package Cratia\ORM\DBAL
 */
interface IAdapter
{
    public function query(string $sentence, array $params = [], array $types = []): array;
}