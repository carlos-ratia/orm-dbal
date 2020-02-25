<?php
declare(strict_types=1);


namespace Tests\Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Events\QueryExecute\Events;
use Cratia\ORM\DBAL\Events\QueryExecute\QueryExecuteAfter;
use Cratia\ORM\DBAL\Events\QueryExecute\QueryExecuteBefore;
use Cratia\ORM\DBAL\Events\QueryExecute\QueryExecuteError;
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

    public function onError(QueryExecuteError $event)
    {
        $this->onError = true;
    }

    public function onAfterExecuteQuery(QueryExecuteAfter $event)
    {
        $this->onAfterExecuteQuery = true;
    }

    public function onBeforeExecuteQuery(QueryExecuteBefore $event)
    {
        $this->onBeforeExecuteQuery = true;
    }

    public function onAfterExecuteNonQuery(QueryExecuteAfter $event)
    {
        $this->onAfterExecuteNonQuery = true;
    }

    public function onBeforeExecuteNonQuery(QueryExecuteBefore $event)
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