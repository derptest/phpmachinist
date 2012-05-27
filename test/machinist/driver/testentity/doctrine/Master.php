<?php
namespace machinist\driver\testentity\doctrine;

/**
 * Test Doctrine Entity
 * 
 * @author Adam L. Englander <adam.l.englander@gmail.com>
 * @Entity
 */
class Master
{
	/**
	 * @Id
	 * @GeneratedValue
	 * @Column(type="integer")
	 */
	private $id;

	/**
	 * @ManyToOne(targetEntity="Detail", inversedBy="masters")
	 */
	private $detail;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getDetail() {
		return $this->detail;
	}

	public function setDetail(Detail $detail) {
		$this->detail = $detail;
	}
}