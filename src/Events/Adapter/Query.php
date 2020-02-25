<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Events\Adapter;


use Doctrine\Common\EventArgs;

/**
 * Class EventQuery
 * @package Cratia\ORM\DBAL\Events
 */
class Query extends EventArgs
{
    /**
     * @var string
     */
    private $sentence;
    /**
     * @var array
     */
    private $params;
    /**
     * @var array
     */
    private $types;

    /**
     * EventQueryAfter constructor.
     * @param string $sentence
     * @param array $params
     * @param array $types
     */
    public function __construct(string $sentence, array $params, array $types)
    {
        $this->sentence = $sentence;
        $this->params = $params;
        $this->types = $types;
    }

    /**
     * @return string
     */
    public function getSentence(): string
    {
        return $this->sentence;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}