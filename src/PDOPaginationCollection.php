<?php


namespace PDOPaginator;


use Exception;

class PDOPaginationCollection extends \ArrayIterator implements PDOPaginationCollectionInterface
{
    /**
     * @var int
     */
    protected $total;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @var int
     */
    protected $currentPage;


    /**
     * PdoPaginationCollection constructor.
     * @param array $array Array of data
     * @param int $currentPage Current page number
     * @param int $perPage Size data per page
     * @param int $total Total of data in database
     * @param int $flags
     */
    public function __construct(array $array, int $currentPage, int $perPage, int $total, $flags = 0)
    {
        parent::__construct($array, $flags);
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->total = $total;
    }

    /**
     * Return a copy of the data
     * @return array
     */
    public function getData(): array
    {
        return $this->getArrayCopy();
    }


    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return ceil($this->total / $this->perPage);
    }

    /**
     * @return array
     */
    public function getPaginationArray(): array
    {
        return [
            'total' => $this->total,
            'perPage' => $this->perPage,
            'currentPage' => $this->currentPage,
            'totalPages'=> $this->getTotalPages()
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function toArray(): array
    {
        throw new Exception("It's not possible to know your data type. If you need use this, implements your own extending this class");
    }
}