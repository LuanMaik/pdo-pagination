<?php


namespace Test;


use InvalidArgumentException;
use PDO;
use PDOPaginator\PDOPaginationCollectionInterface;
use PDOPaginator\PDOPaginator;

class PDOPaginatorTest extends AbstractDatabaseTestCase
{
    public function test_basic_usage_must_works()
    {
        $paginator = new PDOPaginator($this->getDatabaseConnection());
        $paginator->query('SELECT * FROM user');
        $collection = $paginator->execute(15, 1);

        $this->assertInstanceOf(PDOPaginationCollectionInterface::class, $collection);
        $this->assertCount(5, $collection->getData());
        $this->assertEquals(1, $collection->getCurrentPage());
        $this->assertEquals(15, $collection->getPerPage());
        $this->assertEquals(5, $collection->getTotal());
        $this->assertEquals(1, $collection->getTotalPages());
    }

    public function test_conditional_query_must_works()
    {
        $paginator = new PDOPaginator($this->getDatabaseConnection());
        $paginator->query('SELECT * FROM user WHERE role = :role');
        $paginator->bindValue(':role', 'admin');
        $collection = $paginator->execute(10, 1);

        /**
         * There are only 3 admin users
         */
        $this->assertCount(3, $collection->getData());
        $this->assertEquals(1, $collection->getCurrentPage());
        $this->assertEquals(10, $collection->getPerPage());
        $this->assertEquals(3, $collection->getTotal());
        $this->assertEquals(1, $collection->getTotalPages());
    }

    public function test_custom_collection_must_works()
    {
        $paginator = new PDOPaginator($this->getDatabaseConnection(), CustomPaginationCollection::class);
        $paginator->query('SELECT * FROM user');
        $collection = $paginator->execute(10, 1);

        $this->assertInstanceOf(CustomPaginationCollection::class, $collection);
    }

    public function test_invalid_custom_collection_class_must_not_be_allowed()
    {
        $this->expectException(InvalidArgumentException::class);

        new PDOPaginator($this->getDatabaseConnection(), InvalidCustomPaginationCollection::class);
    }

    public function test_paginate_must_works()
    {
        /**
         * First page
         */
        $paginator = new PDOPaginator($this->getDatabaseConnection());
        $paginator->query('SELECT * FROM user WHERE role = :role');
        $paginator->bindValue(':role', 'admin');
        $collection = $paginator->execute(2, 1);

        $this->assertCount(2, $collection->getData());
        $this->assertEquals(1, $collection->getCurrentPage());
        $this->assertEquals(2, $collection->getPerPage());
        $this->assertEquals(3, $collection->getTotal());
        $this->assertEquals(2, $collection->getTotalPages());

        /**
         * Second page
         */
        $paginator->query('SELECT * FROM user WHERE role = :role');
        $paginator->bindValue(':role', 'admin');
        $collection = $paginator->execute(2, 2);

        $this->assertCount(1, $collection->getData());
        $this->assertEquals(2, $collection->getCurrentPage());
        $this->assertEquals(2, $collection->getPerPage());
        $this->assertEquals(3, $collection->getTotal());
        $this->assertEquals(2, $collection->getTotalPages());
    }

    public function test_query_with_group_by_must_works()
    {
        $paginator = new PDOPaginator($this->getDatabaseConnection());
        $paginator->query('SELECT * FROM user GROUP BY role');
        $collection = $paginator->execute(10, 1);

        $this->assertCount(2, $collection->getData());
        $this->assertEquals(1, $collection->getCurrentPage());
        $this->assertEquals(10, $collection->getPerPage());
        $this->assertEquals(2, $collection->getTotal());
        $this->assertEquals(1, $collection->getTotalPages());
    }

    public function test_query_with_limit_must_not_be_allowed()
    {
        $this->expectException(InvalidArgumentException::class);

        $paginator = new PDOPaginator($this->getDatabaseConnection());
        $paginator->query('SELECT * FROM user LIMIT 5');
    }

    /**
     * Test if the limit identifier pattern is works correctly when using a field name called 'limit'
     */
    public function test_limit_identifier_pattern_not_conflict_with_possible_field_name()
    {
        $paginator = new PDOPaginator($this->getDatabaseConnection());
        $paginator->query('SELECT * FROM creditcard WHERE limit > 1000');
        $paginator->query('SELECT * FROM creditcard WHERE limit < 1000');
        $paginator->query('SELECT * FROM creditcard WHERE limit = 1000');
        $paginator->query('SELECT * FROM creditcard WHERE limit in(1000, 2000)');

        $this->assertTrue(true);
    }

    public function test_collection_pagination_info()
    {
        $paginator = new PDOPaginator($this->getDatabaseConnection());
        $paginator->query('SELECT * FROM user');
        $collection = $paginator->execute(3, 1);

        $info = $collection->getPaginationArray();
        $this->assertArrayHasKey('total', $info);
        $this->assertEquals(5, $info['total']);
        $this->assertArrayHasKey('perPage', $info);
        $this->assertEquals(3, $info['perPage']);
        $this->assertArrayHasKey('currentPage', $info);
        $this->assertEquals(1, $info['currentPage']);
        $this->assertArrayHasKey('totalPages', $info);
        $this->assertEquals(2, $info['totalPages']);

        $this->assertCount(3, $collection->toArray());
    }

    public function test_fetch_mode_class_must_works()
    {
        $paginator = new PDOPaginator($this->getDatabaseConnection());
        $paginator->query('SELECT * FROM user');
        $collection = $paginator->execute(15, 1, PDO::FETCH_CLASS, User::class);

        foreach ($collection as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }
}