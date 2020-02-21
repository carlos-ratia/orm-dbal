<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL;

use Cratia\ORM\DBAL\Interfaces\IQueryPerformance;
use JsonSerializable;

/**
 * Class QueryPerformance
 * @package Cratia\ORM\DBAL
 */
class QueryPerformance implements IQueryPerformance, JsonSerializable
{
    /** @var string */
    private $runtime;

    /** @var string */
    private $memory;

    /**
     * QueryPerformance constructor.
     * @param string $runtime
     * @param string $memory
     */
    public function __construct(string $runtime, string $memory)
    {
        $this->runtime = $runtime;
        $this->memory = $memory;
    }

    /**
     * @return string
     */
    public function getRuntime(): string
    {
        return $this->runtime;
    }

    /**
     * @return string
     */
    public function getMemory(): string
    {
        return $this->memory;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return
            [
                'runtime' => $this->getRuntime(),
                'memory' => $this->getMemory()
            ];
    }
}