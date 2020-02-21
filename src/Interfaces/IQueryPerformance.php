<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Interfaces;

/**
 * Interface IQueryPerformance
 * @package Cratia\ORM\DBAL\Interfaces
 */
interface IQueryPerformance
{
    /**
     * @return string
     */
    public function getRuntime(): string;

    /**
     * @return string
     */
    public function getMemory(): string;
}