<?php


namespace Test;


use PDO;
use PHPUnit\Framework\TestCase;

abstract class AbstractDatabaseTestCase extends TestCase
{
    /**
     * @var PDO
     */
    static private $pdo = null;


    final public function getDatabaseConnection()
    {
        if (self::$pdo == null) {
            self::$pdo = new PDO('sqlite::memory:');
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->seedDatabase();
        }

        return self::$pdo;
    }

    final private function seedDatabase()
    {
        $pdo = self::$pdo;
        $this->deleteDatabase();
        $pdo->exec("CREATE TABLE user (
                    id INTEGER PRIMARY KEY, 
                    name TEXT, 
                    email TEXT, 
                    role TEXT)");

        $users = [
            [1, 'Luan Maik', 'luanmaik1994@gmail.com', 'admin'],
            [2, 'Creidson Roberto', 'creidson@gmail.com', 'admin'],
            [3, 'Robersvaldo Tenório', 'robersvaldo@gmail.com', 'admin'],
            [4, 'Cleidomiro Batista', 'cleidomiro@gmail.com', 'guest'],
            [5, 'Manuelson Camargo', 'manuelson@gmail.com', 'guest']
        ];

        foreach ($users as $user) {
            $stmt = $pdo->prepare("INSERT INTO user VALUES (:id, :name, :email, :role)");
            $stmt->bindValue(':id', $user[0]);
            $stmt->bindValue(':name', $user[1]);
            $stmt->bindValue(':email', $user[2]);
            $stmt->bindValue(':role', $user[3]);
            $stmt->execute();
        }
    }

    final private function deleteDatabase()
    {
        self::$pdo->exec("DROP TABLE IF EXISTS user;");
    }


    public function setUp(): void
    {
        parent::setUp();
        $this->getDatabaseConnection();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

}