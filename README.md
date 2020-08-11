# PDOPaginator
`composer require luanmaik/pdo-paginator`

This library will help you to create pagination of records easily, using PDO.


## Common Workaround
**Using SQL_CALC_FOUND_ROWS & FOUND_ROWS()**

```sql
# Search data
SELECT SQL_CALC_FOUND_ROWS * FROM user WHERE role = 'admin' LIMIT 10 OFFSET 5;
# Get number rows existents in previous query
SELECT FOUND_ROWS();
```
PROS: Simple

CONS: slow when use complex queries. *FOUND_ROWS()* is deprecated. 
***
**Using COUNT()**

```sql
# Search data
SELECT * FROM user WHERE role = 'admin' LIMIT 10 OFFSET 5;
# Get number rows existents
SELECT count(*) FROM user WHERE role = 'admin';
```
PROS: easy to read and efficiently.

CONS: Verbose. 

## How this libs works?
It uses the _count(*)_ pagination method (as above), but with a simple implementation, 
you set your query and the lib define the _limit_ and _offset_ instruction under the hood 
and running a second query using _count(*)_ to find the total number of registers available.

See the examples in below. 

## Simple usage example

```php
$paginator = new PDOPaginator\PDOPaginator($pdoConnection);
// Don't define the LIMIT instruction in your query
$paginator->query("SELECT * FROM users");
$paginationCollection = $paginator->execute($perPage = 15, $page = 1);

$paginationCollection->getTotal(); // Return total number of data in databse;
$paginationCollection->getData(); // Return array data;
$paginationCollection->getPerPage(); // 15 ... Return the number of registers per page
$paginationCollection->getPage(); // 1 ... Return the number page
$paginationCollection->getTotalPages(); // Return the total number of pages
$paginationCollection->getPaginationArray(); // Return the pagination details in array
```


## Condition query params example
Use `bindValue()` method.
```php
$paginator = new PDOPaginator\PDOPaginator($pdoConnection);
$paginator->query("SELECT * FROM users WHERE role = :role");
$paginator->bindValue(':role', 'admin', PDO::PARAM_STR);
$paginationCollection = $paginator->execute($perPage = 15, $page = 1);
```


## Custom fetch mode
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