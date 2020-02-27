<?php
declare(strict_types=1);


namespace Tests\Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Events\Events;
use Cratia\ORM\DBAL\Events\Payloads\EventQueryExecuteAfterPayload;
use Cratia\ORM\DBAL\Events\Payloads\EventQueryExecuteBeforePayload;
use Cratia\ORM\DBAL\Events\Payloads\EventQueryExecuteErrorPayload;
use Doctrine\Common\EventSubscriber;

/**
 * Class EventSubscriberAdapter
 * @package Tests\Cratia\ORM\DBAL
 */
class EventSubscriberQueryExecute implements EventSubscriber
{
    public $onError;
    public $onAfterExecuteQuery;
    public $onBeforeExecuteQuery;
    public $onAfterExecuteNonQuery;
    public $onBeforeExecuteNonQuery;

    public function __construct()
    {
        $this->onError = false;
        $this->onAfterExecuteQuery = false;
        $this->onBeforeExecuteQuery = false;
        $this->onAfterExecuteNonQuery = false;
        $this->onBeforeExecuteNonQuery = false;
    }

    public function queryExecuteOnError(EventQueryExecuteErrorPayload $event)
    {
        $this->onError = true;
    }

    public function queryExecuteOnAfterExecuteQuery(EventQueryExecuteAfterPayload $event)
    {
        $this->onAfterExecuteQuery = true;
    }

    public function queryExecuteOnBeforeExecuteQuery(EventQueryExecuteBeforePayload $event)
    {
        $this->onBeforeExecuteQuery = true;
    }

    public function queryExecuteOnAfterExecuteNonQuery(EventQueryExecuteAfterPayload $event)
    {
        $this->onAfterExecuteNonQuery = true;
    }

    public function queryExecuteOnBeforeExecuteNonQuery(EventQueryExecuteBeforePayload $event)
    {
        $this->onBeforeExecuteNonQuery = true;
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return
            [
                Events::ON_ERROR,
                Events::ON_BEFORE_EXECUTE_QUERY,
                Events::ON_AFTER_EXECUTE_QUERY,
                Events::ON_BEFORE_EXECUTE_NON_QUERY,
                Events::ON_AFTER_EXECUTE_NON_QUERY,
            ];
    }
}