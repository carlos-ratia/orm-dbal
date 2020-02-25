<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Events\Adapter;


use Doctrine\Common\EventArgs;
use Doctrine\DBAL\DBALException;

/**
 * Class QueryError
 * @package Cratia\ORM\DBAL\Events
 */
class QueryError extends EventArgs
{
    /**
     * @var DBALException
     */
    private $exception;

    /**
     * Query constructor.
     * @param DBALException $e
     */
    public function __construct(DBALException $e)
    {
        $this->exception = $e;
    }

    /**
     * @return DBALException
     */
    public function getException(): DBALException
    {
        return $this->exception;
    }
}