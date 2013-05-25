<?php
namespace DerpTest\Machinist\Driver\TestEntity\Doctrine;

/**
 * Description of Group
 *
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 * @Entity
 * @Table(name="groups")
 */
class Group
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    private $id;

    /**
     * @Column(type="string", length=255)
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