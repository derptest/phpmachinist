<?php
namespace DerpTest\Machinist\Driver\TestEntity\Doctrine;

/**
 * Test Doctrine Entity
 *
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 * @Entity
 */
class Detail
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

    /**
     * @OneToMany(targetEntity="Master", mappedBy="detail")
     */
    private $masters;

    public function __conctruct()
    {
        $this->masters = new \Doctrine\Common\Collections\ArrayCollection();
    }

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

    public function getMasters()
    {
        return $this->masters;
    }
}