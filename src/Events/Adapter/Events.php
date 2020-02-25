<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Events\Adapter;

/**
 * Class Events
 * @package Cratia\ORM\DBAL\Events
 */
final class Events
{
    /**
     * Private constructor. This class cannot be instantiated.
     */
    private function __construct()
    {
    }

    public const ON_ERROR = 'onError'; // COMMON FOR QUERY AND NON QUERY

    public const ON_AFTER_QUERY = 'onAfterQuery';
    public const ON_BEFORE_QUERY = 'onBeforeQuery';

    public const ON_AFTER_NON_QUERY = 'onAfterNonQuery';
    public const ON_BEFORE_NON_QUERY = 'onBeforeNonQuery';
}