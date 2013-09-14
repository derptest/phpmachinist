<?php
namespace DerpTest\Machinist\Store\TestEntity\Doctrine;

/**
 * Description of Stuff
 *
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 * @Entity
 */
class Stuff
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    private $id;

    /**
     * @Column(type="string", length=100)
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
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