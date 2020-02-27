<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Events\Payloads;


use Doctrine\Common\EventArgs;
use Doctrine\DBAL\DBALException;
use Exception;

/**
 * Class EventQueryExecuteErrorPayload
 * @package Cratia\ORM\DBAL\Events\Payloads
 */
class EventQueryExecuteErrorPayload extends EventArgs
{
    /**
     * @var Exception|DBALException
     */
    private $exception;

    /**
     * QueryExecuteError constructor.
     * @param Exception $e
     */
    public function __construct(Exception $e)
    {
        $this->exception = $e;
    }

    /**
     * @return DBALException|Exception
     */
    public function getException()
    {
        return $this->exception;
    }

}