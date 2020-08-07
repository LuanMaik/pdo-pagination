# PDOPaginator
Because pagination doesn't have to be difficult.

This library will help you to create pagination of records easily, using PDO.


## Simple example

```php
$paginator = new PDOPaginator\PDOPaginator($pdoConnection);
$paginator->query("SELECT * FROM users");
$paginationCollection = $paginator->execute($perPage = 15, $page = 1);

$paginationCollection->getTotal(); // Return total number of data in databse;
$paginationCollection->getData(); // Return array data;
$paginationCollection->getPerPage(); // 15 ... Return the number of registers per page
$paginationCollection->getPage(); // Return the number page
$paginationCollection->getTotalPages(); // Return the total number of pages
$paginationCollection->getPaginationArray(); // Return the pagination details in array
```


## Condition query example
Use `bindValue()` method.
```php
$paginator = new PDOPaginator\PDOPaginator($pdoConnection);
$paginator->query("SELECT * FROM users WHERE role = :role");
$paginator->bindValue(':role', 'admin', PDO::PARAM_STR);
$paginationCollection = $paginator->execute($perPage = 15, $page = 1);
```


## Custom FETCH MODE
Use the third and fourth params in `execute()` method.
```php
$paginator = new PDOPaginator\PDOPaginator($pdoConnection);
$paginator->query("SELECT * FROM users");
$paginationCollection = $paginator->execute($perPage = 15, $page = 1, PDO::FETCH_CLASS, User::class);

$paginationCollection->getData(); // returns User[]
```


## Custom Collection
Use the second param in `__construct()` method. The custom class MUST implements `\PDOPaginator\PDOPaginationCollectionInterface`. 
```php
// Create a custom collection
class MyCustomCollection extends \ArrayIterator implements \PDOPaginator\PDOPaginationCollectionInterface {
    //...
}
//OR you can extends the default collection implementation
class MyCustomCollection extends \PDOPaginator\PDOPaginationCollection {
    // overwrite some methods like toArray(), getPaginationArray(), etc.
}

$paginator = new PDOPaginator\PDOPaginator($pdoConnection, MyCustomCollection::class);
$paginator->query("SELECT * FROM users");
$paginationCollection = $paginator->execute($perPage = 15, $page = 1, PDO::FETCH_CLASS, User::class);
```