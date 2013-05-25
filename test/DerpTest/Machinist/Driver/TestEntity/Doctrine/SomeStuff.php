<?php
namespace DerpTest\Machinist\Driver\TestEntity\Doctrine;

/**
 * Test Doctrine Entity
 *
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 * @Entity
 */
class SomeStuff
{
    /**
     * @Id
     * @Column(type="integer")
     */
    private $some_id;

    /**
     * @Id
     * @Column(type="integer")
     */
    private $stuff_id;

    /**
     * @Column(type="string", length=100)
     */
    private $name;

    public function getSomeId()
    {
        return $this->some_id;
    }

    public function setSomeId($some_id)
    {
        $this->some_id = $some_id;
    }

    public function getStuffId()
    {
        return $this->stuff_id;
    }

    public function setStuffId($stuff_id)
    {
        $this->stuff_id = $stuff_id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

}