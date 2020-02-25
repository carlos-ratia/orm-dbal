<?php
declare(strict_types=1);


namespace Cratia\ORM\DBAL\Events\QueryExecute;


use Cratia\ORM\DBAL\Interfaces\IQueryDTO;
use Doctrine\Common\EventArgs;

/**
 * Class QueryExecuteAfter
 * @package Cratia\ORM\DBAL\Events\QueryExecute
 */
class QueryExecuteAfter extends EventArgs
{
    /**
     * @var IQueryDTO
     */
    private $dto;

    /**
     * QueryExecuteAfter constructor.
     * @param IQueryDTO $dto
     */
    public function __construct(IQueryDTO $dto)
    {
        $this->dto = $dto;
    }

    /**
     * @return IQueryDTO
     */
    public function getDto(): IQueryDTO
    {
        return $this->dto;
    }
}