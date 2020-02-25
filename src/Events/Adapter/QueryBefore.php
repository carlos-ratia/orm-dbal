<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Events\Adapter;


/**
 * Class EventQueryBefore
 * @package Cratia\ORM\DBAL\Events
 */
class QueryBefore extends Query
{
    /**
     * EventQueryAfter constructor.
     * @param string $sentence
     * @param array $params
     * @param array $types
     */
    public function __construct(string $sentence, array $params, array $types)
    {
        parent::__construct($sentence, $params, $types);
    }
}