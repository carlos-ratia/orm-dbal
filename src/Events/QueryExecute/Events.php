<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Events\QueryExecute;

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

    public const ON_AFTER_EXECUTE_QUERY = 'onAfterExecuteQuery';
    public const ON_BEFORE_EXECUTE_QUERY = 'onBeforeExecuteQuery';

    public const ON_AFTER_EXECUTE_NON_QUERY = 'onAfterExecuteNonQuery';
    public const ON_BEFORE_EXECUTE_NON_QUERY = 'onBeforeExecuteNonQuery';
}