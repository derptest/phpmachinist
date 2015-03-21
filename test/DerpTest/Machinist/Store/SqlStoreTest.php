<?php

class SqlStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @Mock
     * @var \PDO
     */
    private $pdo;

    protected function setUp()
    {
        \Phake::initAnnotations($this);
        \Phake::when($this->pdo)->getAttribute(\Phake::anyParameters())->thenReturn("sqlite");
    }

    protected function tearDown()
    {
        $this->pdo = null;
    }

    public function testFromPdoDefaultsErrorModeToException()
    {
        \DerpTest\Machinist\Store\SqlStore::fromPdo($this->pdo);
        \Phake::verify($this->pdo)->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function testFromPdoSetsErrorMode()
    {
        \DerpTest\Machinist\Store\SqlStore::fromPdo($this->pdo, \PDO::ERRMODE_WARNING);
        \Phake::verify($this->pdo)->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
    }
}
