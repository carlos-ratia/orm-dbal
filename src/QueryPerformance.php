<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL;

use Cratia\ORM\DBAL\Common\Functions;
use Cratia\ORM\DBAL\Interfaces\IQueryPerformance;
use JsonSerializable;

/**
 * Class QueryPerformance
 * @package Cratia\ORM\DBAL
 */
class QueryPerformance implements IQueryPerformance, JsonSerializable
{
    /**
     * @var string
     */
    private $runtime;

    /**
     * @var string
     */
    private $memory;

    /**
     * @var float
     */
    private $time;

    /**
     * QueryPerformance constructor.
     * @param float $time
     */
    public function __construct(float $time)
    {
        $this->time = $time;
        $this->runtime = Functions::pettyRunTime($time);
        $this->memory = intval(memory_get_usage() / 1024 / 1024) . ' MB';
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
     * @return float
     */
    public function getTime(): float
    {
        return $this->time;
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