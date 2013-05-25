<?php
namespace DerpTest\Machinist\Behat\Functions;
use \DerpTest\Machinist\Machinist;
use \DerpTest\Machinist\Driver\SqlStore;
use \stdClass;

class SweetTestTable
{
    public function __construct($hash)
    {
        $this->hash = $hash;
    }

    public function getHash()
    {
        return $this->hash;
    }
}

class FunctionTest extends \PHPUnit_Framework_TestCase
{
    private $pdo;

    public function setup()
    {
        $this->pdo = new \PDO("sqlite::memory:");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('DROP TABLE IF EXISTS `box`');
        $this->pdo->exec('DROP TABLE IF EXISTS `stuff`');
        $this->pdo->exec('create table `box` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` varchar(100), `other_field` varchar(100));');
        $this->pdo->exec('create table `stuff` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` varchar(100), `box_id` INTEGER NULL DEFAULT NULL);');
        \DerpTest\Machinist\Machinist::reset();
        \DerpTest\Machinist\Machinist::Store(SqlStore::fromPdo($this->pdo));
    }

    public static function relationalOverrideProvider()
    {
        return array(
            array("column: this is my value", array('column' => 'this is my value')),
            array("col1: this is my value, col2: val2", array('col1' => 'this is my value', 'col2' => 'val2'))
        );
    }

    /**
     * @dataProvider relationalOverrideProvider
     */
    public function testRelationalOverridesWithSingleValue($value, $expected)
    {
        $actual = findRelationalOverrides($value);
        $this->assertEquals($expected, $actual);
    }

    public function testCreateMachinesFromTable()
    {
        $box_bp = Machinist::Blueprint("box", array(
            'name' => "My Box"
        ));
        $stuff_bp = Machinist::Blueprint("stuff", array(
            'name' => "My Stuff",
            'Box' => Machinist::Relationship($box_bp)->local('box_id')
        ));

        $sweet_box = $box_bp->make(array('name' => 'amazing'));

        $world = new stdClass();

        $blueprint = "stuff";
        $table = new SweetTestTable(array(array('name' => 'sweet', "Box" => "name: amazing")));
        createMachinesFromTable($world, $blueprint, $table);
        $this->assertEquals(1, count($world->stuff));
        $this->assertEquals("amazing", $world->stuff[0]->Box->name);
        $this->assertEquals("sweet", $world->stuff[0]->name);
    }

    public function testCreateMachinesFromTableWith2Fields()
    {
        $box_bp = Machinist::Blueprint("box", array(
            'name' => "My Box",
            'other_field' => 'My Other Field'
        ));
        Machinist::Blueprint("stuff", array(
            'name' => "My Stuff",
            'Box' => Machinist::Relationship($box_bp)->local('box_id')
        ));

        $box_bp->make(array('name' => 'amazing'));

        $world = new stdClass();

        $blueprint = "stuff";
        $table = new SweetTestTable(array(array('name' => 'sweet', "Box" => "name: amazing, other_field: awesome")));
        createMachinesFromTable($world, $blueprint, $table);
        $this->assertEquals(1, count($world->stuff));
        $this->assertEquals("amazing", $world->stuff[0]->Box->name);
        $this->assertEquals("awesome", $world->stuff[0]->Box->other_field);
        $this->assertEquals("sweet", $world->stuff[0]->name);
    }
}
