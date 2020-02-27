<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Events;

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

    public const ON_ERROR = 'queryExecuteOnError'; // COMMON FOR QUERY AND NON QUERY

    public const ON_AFTER_EXECUTE_QUERY = 'queryExecuteOnAfterExecuteQuery';
    public const ON_BEFORE_EXECUTE_QUERY = 'queryExecuteOnBeforeExecuteQuery';

    public const ON_AFTER_EXECUTE_NON_QUERY = 'queryExecuteOnAfterExecuteNonQuery';
    public const ON_BEFORE_EXECUTE_NON_QUERY = 'queryExecuteOnBeforeExecuteNonQuery';
}