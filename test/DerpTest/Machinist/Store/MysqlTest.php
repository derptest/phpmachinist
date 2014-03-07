<?php
use DerpTest\Machinist\Store\SqlStore;

class MysqlTest extends PHPUnit_Framework_TestCase
{
    private $driver;
    private $pdo;

    public function setUp()
    {
        $this->pdo = Phake::partialMock('PDO',
            $_ENV['MySQL_Store_DSN'],
            $_ENV['MySQL_Store_User'],
            $_ENV['MySQL_Store_Password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('SET foreign_key_checks = 0');
        $this->pdo->exec('CREATE DATABASE IF NOT EXISTS `machinist_test`;');
        $this->pdo->exec('USE `machinist_test`;');
        $this->pdo->exec('DROP TABLE IF EXISTS `stuff`;');
        $this->pdo->exec('create table `stuff` ( `id` INTEGER PRIMARY KEY AUTO_INCREMENT, `name` varchar(100) );');
        $this->pdo->exec('DROP TABLE IF EXISTS `some_stuff`;');
        $this->pdo->exec('CREATE TABLE `some_stuff` (
												`some_id` int(10) unsigned NOT NULL,
												`stuff_id` int(10) unsigned NOT NULL,
												`name` VARCHAR(100),
												PRIMARY KEY (`some_id`,`stuff_id`));');
        $this->pdo->exec('DROP TABLE IF EXISTS `group`;');
        $this->pdo->exec('create table `group` ( `id` INTEGER PRIMARY KEY AUTO_INCREMENT, `name` VARCHAR(255));');

        $this->pdo->exec('DROP TABLE IF EXISTS `nopk`;');
        $this->pdo->exec('create table `nopk` ( `id` INTEGER, `name` VARCHAR(255));');


        $this->pdo->exec('DROP TABLE IF EXISTS `fkey`;');
        $this->pdo->exec('CREATE TABLE `fkey` 
                                        ( `id` int(11) NOT NULL AUTO_INCREMENT,
                                          `stuff_id` int(11),
                                          PRIMARY KEY (`id`),
                                          KEY `IDX_1` (`stuff_id`),
                                          CONSTRAINT `FK_1` FOREIGN KEY (`stuff_id`) REFERENCES `stuff` (`id`)
                                        ) ENGINE=InnoDB');
        $this->pdo->exec('SET foreign_key_checks = 1');

        $this->driver = SqlStore::fromPdo($this->pdo);
    }

    public function tearDown()
    {
        unset($this->pdo);
    }

    public function testSqlStoreGetsInstance()
    {
        $this->assertInstanceOf('\DerpTest\Machinist\Store\Mysql', SqlStore::fromPdo($this->pdo));
    }

    public function testGetPrimaryKey()
    {
        $this->assertEquals('id', $this->driver->primaryKey('stuff'));
    }

    public function testGetPrimaryKeyNoKeyReturnsAllColumns()
    {
        $this->assertEquals(array('id', 'name'), $this->driver->primaryKey('nopk'));
    }

    public function testInsertReturnsKey()
    {
        $id = $this->driver->insert('stuff', array('name' => 'stupid'));
        $this->assertEquals(1, $id);
    }

    public function testInsertStoresCorrectValue()
    {
        $id = $this->driver->insert('stuff', array('name' => 'stupid'));
        $query = $this->pdo->prepare('SELECT * from stuff where id = ?');
        $query->execute(array($id));
        $row = $query->fetch(PDO::FETCH_OBJ);
        $this->assertEquals("stupid", $row->name);
    }

    public function testFindPullsRow()
    {
        $id = $this->driver->insert('stuff', array('name' => 'stupid'));
        $row = $this->driver->find('stuff', $id);
        $this->assertEquals("stupid", $row->name);
    }

    public function testEmptyNoTruncateDeletesRows()
    {
        $id = $this->driver->insert('stuff', array('name' => 'stupid'));
        $row = $this->driver->find('stuff', $id);
        $this->assertNotEmpty($row);
        $this->driver->wipe('stuff', false);
        $row = $this->driver->find('stuff', $id);
        $this->assertEmpty($row);
    }

    public function testEmptyTruncateDeletesRows()
    {
        $id = $this->driver->insert('stuff', array('name' => 'stupid'));
        $row = $this->driver->find('stuff', $id);
        $this->assertNotEmpty($row);
        $this->driver->wipe('stuff', true);
        $row = $this->driver->find('stuff', $id);
        $this->assertEmpty($row);
    }

    public function testLocatesByColumn()
    {
        $id = $this->driver->insert('stuff', array('name' => 'stupid'));
        $row = $this->driver->find('stuff', array('name' => 'stupid'));
        $this->assertNotEmpty($row);
        $this->assertEquals($row[0]->id, $id);
    }

    public function testFindCompoundPrimareyKey()
    {
        $ids = $this->driver->primaryKey('some_stuff');
        $this->assertEquals(array('some_id', 'stuff_id'), $ids);
    }

    public function testInsertingIntoGroup()
    {
        $what = $this->driver->insert('group', array('name' => "Hello"));
        $found = $this->driver->find('group', array('id' => $what));
        $this->assertEquals("Hello", $found[0]->name);
    }

    public function testTruncatingGroup()
    {
        $this->driver->wipe('group', true);
        $this->assertTrue(true); // if we didn't die, all is well
    }

    public function testTruncateWithForeignKeyConstraint() {
        $this->driver->wipe('fkey', true);
        $this->assertTrue(true); // if we didn't die, all is well
    }

    public function testPrimaryKeyCachesResult()
    {
        $ids = $this->driver->primaryKey('some_stuff');
        $this->assertEquals(array('some_id', 'stuff_id'), $ids);
        Phake::verifyNoFurtherInteraction($this->pdo);
        $ids = $this->driver->primaryKey('some_stuff');
        $this->assertEquals(array('some_id', 'stuff_id'), $ids);
    }
}
