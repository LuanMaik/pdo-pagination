<?php


namespace PDOPaginator;


interface PDOPaginationCollectionInterface
{
    public function __construct(array $array, int $page, int $perPage, int $total, $flags = 0);

    /**
     * Return the data
     * @return array
     */
    public function getData(): array;

    /**
     * Return the total of data in datasource
     * @return int
     */
    public function getTotal(): int;

    /**
     * Returns the length of data per page
     * @return int
     */
    public function getPerPage(): int;

    /**
     * Returns the current page number
     * @return int
     */
    public function getCurrentPage(): int;

    /**
     * Return the amount of pages
     * Ex: return ceil($this->total / $this->perPage);
     * @return int
     */
    public function getTotalPages(): int;

    /**
     * Returns a array of pagination info
     * Ex:
     * return [
     *   'total' => $this->total,
     *   'perPage' => $this->perPage,
     *   'currentPage' => $this->currentPage,
     *   'totalPages'=> $this->getTotalPages()
     * ];
     * @return array
     */
    public function getPaginationArray(): array;

    /**
     * Returns the data parsed to array
     *
     * Ex:
     *  $array = [];
     *  foreach ($this->getIterator() as $value) {
     *      $array[] = $value->toArray();
     *  }
     * return $array;
     *
     * @return array
     */
    public function toArray(): array;
}