<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Events\Adapter;


use Cratia\ORM\DBAL\Interfaces\IQueryPerformance;

/**
 * Class EventQueryAfter
 * @package Cratia\ORM\DBAL\Events
 */
class QueryAfter extends Query
{
    /**
     * @var IQueryPerformance
     */
    private $performance;
    /**
     * @var array
     */
    private $result;

    /**
     * EventQueryAfter constructor.
     * @param string $sentence
     * @param array $params
     * @param array $types
     * @param array $result
     * @param IQueryPerformance $performance
     */
    public function __construct(
        string $sentence,
        array $params,
        array $types,
        array $result,
        IQueryPerformance $performance
    )
    {
        $this->performance = $performance;
        $this->result = $result;
        parent::__construct($sentence, $params, $types);
    }

    /**
     * @return IQueryPerformance
     */
    public function getPerformance(): IQueryPerformance
    {
        return $this->performance;
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

}