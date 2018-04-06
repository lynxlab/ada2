<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\GDPR;

use Ramsey\Uuid\Uuid;

/**
 * Class for a GDPR request
 *
 * @author giorgio
 */
class GdprRequest extends GdprBase {

	/**
	 * table name for this class
	 *
	 * @var string
	 */
	const table =  AMAGdprDataHandler::PREFIX . 'requests';

	protected $uuid;
	protected $generatedBy;
	protected $generatedTs;
	protected $confirmedTs;
	protected $closedBy;
	protected $closedTs;
	protected $type;
	protected $content;
	protected $selfOpened;

	/**
	 * constructor will always generate a new uuid for the object
	 *
	 * @param array $data
	 */
	public function __construct($data = array()) {
		if (is_null($this->fromArray($data)->getUuid())) {
			$this->setUuid(Uuid::uuid4()->toString());
		}
	}

	/**
	 * override fromArray method to handle type that must be
	 * an instance of GdprRequestType
	 *
	 * {@inheritDoc}
	 * @see \Lynxlab\ADA\Module\GDPR\GdprBase::fromArray()
	 */
	public function fromArray($data = array()) {
		if (array_key_exists('type', $data) && intval($data['type'])>0) {
			$result = $GLOBALS['dh']->findBy('GdprRequestType', array('id' => intval($data['type'])));
			if (count($result)>0) {
				$this->setType(reset($result));
			}
			unset($data['type']);
		}
		return parent::fromArray($data);
	}

	/**
	 * Gets the header array for the requests html table
	 *
	 * @param bool $showall true if action column must be shown
	 * @return array
	 */
	public static function getTableHeader($showall = false) {
		$headerArr = array(
			'Numero pratica',
			'Tipo',
			'Creata il',
			'Chiusa il',
			'Testo'
		);
		if ($showall) $headerArr[] = 'Azioni';

		return array_map(function ($el) {
			return ucwords(strtolower(translateFN($el)));
		}, $headerArr);
	}

	/**
	 * @return mixed
	 */
	public function getUuid() {
		return $this->uuid;
	}

	/**
	 * @return mixed
	 */
	public function getGeneratedBy() {
		return $this->generatedBy;
	}

	/**
	 * @return mixed
	 */
	public function getGeneratedTs() {
		return $this->generatedTs;
	}

	/**
	 * @return mixed
	 */
	public function getConfirmedTs() {
		return $this->confirmedTs;
	}

	/**
	 * @return mixed
	 */
	public function getClosedBy() {
		return $this->closedBy;
	}

	/**
	 * @return mixed
	 */
	public function getClosedTs() {
		return $this->closedTs;
	}

	/**
	 * @return GdprRequestType
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return mixed
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @return mixed
	 */
	public function getSelfOpened() {
		return $this->selfOpened;
	}

	/**
	 * @param mixed $uuid
	 */
	public function setUuid($uuid) {
		$this->uuid = $uuid;
		return $this;
	}

	/**
	 * @param mixed $generatedBy
	 */
	public function setGeneratedBy($generatedBy) {
		$this->generatedBy = $generatedBy;
		return $this;
	}

	/**
	 * @param mixed $generatedTs
	 */
	public function setGeneratedTs($generatedTs) {
		$this->generatedTs = $generatedTs;
		return $this;
	}

	/**
	 * @param mixed $confirmedTs
	 */
	public function setConfirmedTs($confirmedTs) {
		$this->confirmedTs = $confirmedTs;
		return $this;
	}

	/**
	 * @param mixed $closedBy
	 */
	public function setClosedBy($closedBy) {
		$this->closedBy = $closedBy;
		return $this;
	}

	/**
	 * @param mixed $closedTs
	 */
	public function setClosedTs($closedTs) {
		$this->closedTs = $closedTs;
		return $this;
	}

	/**
	 * @param GdprRequestType $type
	 */
	public function setType(GdprRequestType $type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * @param mixed $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @param mixed $selfOpened
	 */
	public function setSelfOpened($selfOpened) {
		$this->selfOpened = $selfOpened;
		return $this;
	}
}
