<?php
declare(strict_types=1);


namespace Tests\Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Events\Adapter\Events;
use Cratia\ORM\DBAL\Events\Adapter\QueryAfter;
use Cratia\ORM\DBAL\Events\Adapter\QueryBefore;
use Cratia\ORM\DBAL\Events\Adapter\QueryError;
use Doctrine\Common\EventSubscriber;

/**
 * Class EventSubscriberAdapter
 * @package Tests\Cratia\ORM\DBAL
 */
class EventSubscriberAdapter implements EventSubscriber
{
    public $onError;
    public $onAfterQuery;
    public $onBeforeQuery;
    public $onAfterNonQuery;
    public $onBeforeNonQuery;

    public function __construct()
    {
        $this->onError = false;
        $this->onAfterQuery = false;
        $this->onBeforeQuery = false;
        $this->onAfterNonQuery = false;
        $this->onBeforeNonQuery = false;

    }

    public function onError(QueryError $event)
    {
        $this->onError = true;
    }

    public function onAfterQuery(QueryAfter $event)
    {
        $this->onAfterQuery = true;
    }

    public function onBeforeQuery(QueryBefore $event)
    {
        $this->onBeforeQuery = true;
    }

    public function onAfterNonQuery(QueryAfter $event)
    {
        $this->onAfterNonQuery = true;
    }

    public function onBeforeNonQuery(QueryBefore $event)
    {
        $this->onBeforeNonQuery = true;
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return
            [
                Events::ON_ERROR,
                Events::ON_AFTER_QUERY,
                Events::ON_BEFORE_QUERY,
                Events::ON_AFTER_NON_QUERY,
                Events::ON_BEFORE_NON_QUERY,
            ];
    }
}