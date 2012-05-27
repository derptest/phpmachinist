<?php
namespace machinist\driver\testentity\doctrine;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Test Doctrine Entity
 * 
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 * @Entity
 */
class ManyLeft
{
	/**
	 * @Id
	 * @GeneratedValue
	 * @Column(type="integer")
	 */
	private $id;

	/**
	 * @ManyToMany(targetEntity="ManyRight", inversedBy="lefts")
	 * @JoinTable(name="left_right",
	 *   joinColumns={@JoinColumn(name="left_id", referencedColumnName="id")},
	 *   inverseJoinColumns={@JoinColumn(name="right_id", referencedColumnName="id")}
	 * )
	 */
	private $rights;

	public function __construct() {
		$this->rights = new ArrayCollection;
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getManyRights() {
		return $this->rights;
	}

	public function setManyRights(array $lefts = null) {
		$this->rights = new ArrayCollection($lefts);
	}
}