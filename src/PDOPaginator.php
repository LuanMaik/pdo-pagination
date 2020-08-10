<?php


namespace PDOPaginator;



use InvalidArgumentException;
use PDO;

class PDOPaginator
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var array
     */
    protected $bindValues = [];

    /**
     * @var string
     */
    protected $paginationCollectionClass;

    /**
     * PdoPaginator constructor.
     * @param PDO $pdo
     * @param string $paginationCollectionClass
     */
    public function __construct(PDO $pdo, string $paginationCollectionClass = PDOPaginationCollection::class)
    {
        $this->pdo = $pdo;
        $this->setPaginationCollectionClass($paginationCollectionClass);
    }

    /**
     * @param string $query
     */
    public function query(string $query): void
    {
        $limitPattern = '/([\s\S]*)LIMIT([\s\S])(?!=)/i';
        if (preg_match($limitPattern, $query)) {
            throw new InvalidArgumentException("The informed query must not have LIMIT operator, informed: {$query}");
        }

        $this->query = str_replace(';', '', $query);
    }

    /**
     * @param string $paginationCollectionClass
     */
    public function setPaginationCollectionClass(string $paginationCollectionClass): void
    {
        if (!in_array(PDOPaginationCollectionInterface::class, class_implements($paginationCollectionClass))) {
            throw new InvalidArgumentException("The {$this->paginationCollectionClass} informed must implements ". PDOPaginationCollectionInterface::class);
        }

        $this->paginationCollectionClass = $paginationCollectionClass;
    }


    /**
     * @param $parameter
     * @param $value
     * @param int $dataType
     */
    public function bindValue($parameter, $value, $dataType = PDO::PARAM_STR): void
    {
        $this->bindValues[] = [$parameter, $value, $dataType];
    }


    /**
     * @param int $perPage
     * @param int $page
     * @param null $fetchMode
     * @param null $fetchArgument
     * @return PDOPaginationCollectionInterface
     */
    public function execute(int $perPage, int $page = 1, $fetchMode = null, $fetchArgument = null): PDOPaginationCollectionInterface
    {
        $registers = $this->executePagination($perPage, $page, $fetchMode, $fetchArgument);
        $total = $this->executeTotalPagination();

        // Reset the binds to not effect next pagination
        $this->bindValues = [];

        return new $this->paginationCollectionClass($registers, $page, $perPage, $total);
    }

    /**
     * @param int $perPage
     * @param int $page
     * @param null $fetchMode
     * @param null $fetchArgument
     * @return array
     */
    protected function executePagination(int $perPage, int $page = 1, $fetchMode = null, $fetchArgument = null)
    {
        $query = $this->buildQueryPagination();
        $stmt = $this->pdo->prepare($query);
        foreach ($this->bindValues as $bindValue) {
            $stmt->bindValue(...$bindValue);
        }
        $stmt->bindValue(':offset', $perPage * ($page - 1), PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->execute();

        $fetchConfig = [$fetchMode];
        if ($fetchMode !== null) {
            $fetchConfig[] = $fetchArgument;
        }

        return $stmt->fetchAll(...$fetchConfig);
    }

    /**
     * @return int
     */
    protected function executeTotalPagination(): int
    {
        $queryTotal = $this->buildQueryTotalPagination();
        $stmt = $this->pdo->prepare($queryTotal);
        foreach ($this->bindValues as $bindValue) {
            $stmt->bindValue(...$bindValue);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }


    /**
     * Transform:
     *      SELECT id, name FROM user WHERE active = :active;
     * to:
     *      SELECT id, name FROM user WHERE active = :active LIMIT :offset, :limit;
     *
     * @return string
     */
    protected function buildQueryPagination(): string
    {
        return "{$this->query} LIMIT :offset, :limit;";
    }


    /**
     * Transform:
     *      SELECT id, name FROM user WHERE active = :active;
     * to:
     *      SELECT COUNT(*) FROM user WHERE active = :active;
     *
     * @return string
     */
    protected function buildQueryTotalPagination(): string
    {
        $pattern = '/SELECT([\s\S]*?)FROM/i';
        $totalQuery = preg_replace($pattern, 'SELECT COUNT(*) as total FROM', $this->query);

        // if has GROUP BY
        $groupByPattern = '/([\s\S]*)GROUP([\s\S])BY([\s\S]*)/i';
        if (preg_match($groupByPattern, $totalQuery)) {
            $totalQuery = "SELECT COUNT(*) as total FROM ({$totalQuery}) as temp";
        }

        return $totalQuery;
    }

}