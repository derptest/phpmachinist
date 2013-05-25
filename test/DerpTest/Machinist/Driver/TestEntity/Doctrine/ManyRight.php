<?php
namespace DerpTest\Machinist\Driver\TestEntity\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Test Doctrine Entity
 *
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 * @Entity
 */
class ManyRight
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    private $id;

    /**
     * @ManyToMany(targetEntity="ManyLeft", mappedBy="rights")
     */
    private $lefts;

    public function __construct()
    {
        $this->lefts = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getManyLefts()
    {
        return $this->lefts;
    }

    public function setManyLefts(array $lefts = null)
    {
        $this->lefts = new ArrayCollection($lefts);
    }
}