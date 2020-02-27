<?php
declare(strict_types=1);


namespace Tests\Cratia\ORM\DBAL;


use Cratia\ORM\DBAL\Adapter\Events\Events;
use Cratia\ORM\DBAL\Adapter\Events\Payloads\EventAfterPayload;
use Cratia\ORM\DBAL\Adapter\Events\Payloads\EventBeforePayload;
use Cratia\ORM\DBAL\Adapter\Events\Payloads\EventErrorPayload;
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

    public function adapterOnError(EventErrorPayload $event)
    {
        $this->onError = true;
    }

    public function adapterOnAfterQuery(EventAfterPayload $event)
    {
        $this->onAfterQuery = true;
    }

    public function adapterOnBeforeQuery(EventBeforePayload $event)
    {
        $this->onBeforeQuery = true;
    }

    public function adapterOnAfterNonQuery(EventAfterPayload $event)
    {
        $this->onAfterNonQuery = true;
    }

    public function adapterOnBeforeNonQuery(EventBeforePayload $event)
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