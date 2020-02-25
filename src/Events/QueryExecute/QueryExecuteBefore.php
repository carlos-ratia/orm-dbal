<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Events\QueryExecute;


use Cratia\ORM\DQL\Interfaces\ISql;
use Doctrine\Common\EventArgs;

/**
 * Class QueryExecuteBefore
 * @package Cratia\ORM\DBAL\Events\QueryExecute
 */
class QueryExecuteBefore extends EventArgs
{
    /**
     * @var ISql
     */
    private $sql;

    /**
     * QueryExecuteBefore constructor.
     * @param ISql $sql
     */
    public function __construct(ISql $sql)
    {
        $this->sql = $sql;
    }

    /**
     * @return ISql
     */
    public function getSql(): ISql
    {
        return $this->sql;
    }
}