<?php
/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\GDPR;

/**
 * class for managing Gdpr API to be used by external modules
 *
 * @author giorgio
 */
require_once MODULES_GDPR_PATH .'/config/config.inc.php';

class GdprAPI {

	/**
	 * @var AMAGdprDataHandler
	 */
	private $_dh;

	/**
	 * constructor
	 */
	public function __construct($tester = null) {
		if (isset($GLOBALS['dh']) && $GLOBALS['dh'] instanceof AMAGdprDataHandler) {
			$this->_dh = $GLOBALS['dh'];
		} else {
			if (is_null($tester)) $tester = $_SESSION['sess_selected_tester'];
			$this->_dh = AMAGdprDataHandler::instance(\MultiPort::getDSN($tester));
		}
	}

	/**
	 * destructor
	 */
	public function __destruct() {
		$this->_dh->disconnect();
	}

	/**
	 * Gets all the GdprUserType' objects
	 * @return array
	 */
	public function getGdprUserTypes() {
		return $this->_dh->findAll('GdprUserType');
	}

	/**
	 * Gets the GdprUserTypes marked as 'none'
	 *
	 * @return array
	 */
	public function getGdprNoneUserTypes() {
		$noneTypes = array(GdprUserType::NONE);
		return array_filter($this->getGdprUserTypes(), function($el) use ($noneTypes) {
			return in_array($el->getId(), $noneTypes);
		});
	}

	/**
	 * Loads a GdprUser from the passed user
	 *
	 * @param integer|\ADALoggableUser $userID
	 * @return GdprUser
	 */
	public function getGdprUserByID($userID) {
		if ($userID instanceof \ADALoggableUser) $userID = $userID->getId();
		$res = $this->_dh->findBy('GdprUser', array('id_utente' => $userID));
		return reset($res);
	}

	/**
	 * Checks if a user is of the passed GdprUserType
	 *
	 * @param integer|\ADALoggableUser $user
	 * @param integer|array $gdprUserTypes array of GdprUserType ids
	 * @return boolean
	 */
	public function isGdprUserType($user, $gdprUserTypes) {
		if ($user instanceof \ADALoggableUser) $user = $user->getId();
		if (!is_array($gdprUserTypes)) $gdprUserTypes = array($gdprUserTypes);
		$result = array_filter(
			$this->_dh->findBy('GdprUser', array('id_utente' => intval($user))),
			function(GdprUser $el) use($gdprUserTypes) { return in_array($el->getType()->getId(), $gdprUserTypes); }
		);
		return (count($result)>0);
	}

	/**
	 * Saves a GdprUser object
	 *
	 * @param GdprUser $gdprUser
	 */
	public function saveGdprUser(GdprUser $gdprUser) {
		return $this->_dh->saveGdprUser($gdprUser->toArray());
	}

	public function saveRequest($data) {
		return $this->_dh->saveRequest($data);
	}

	public function closeRequest($request, $closedBy=null) {
		return $this->_dh->closeRequest($request, $closedBy);
	}

	public function savePolicy($data) {
		return $this->_dh->savePolicy($data);
	}

	public function findBy($className, array $whereArr = null, array $orderByArr = null, \Abstract_AMA_DataHandler $dbToUse = null) {
		return $this->_dh->findBy($className, $whereArr, $orderByArr, $dbToUse);
	}

	public function findAll($className, array $orderBy = null, \Abstract_AMA_DataHandler $dbToUse = null) {
		return $this->_dh->findAll($className, $orderBy, $dbToUse);
	}

	/**
	 * Builds a GdprUser from the passed ADALoggableUser
	 *
	 * @param \ADALoggableUser $user
	 * @return \Lynxlab\ADA\Module\GDPR\GdprUser
	 */
	public static function createGdprUserFromADALoggable(\ADALoggableUser $user) {
		return new GdprUser(array('id_utente' => $user->getId()));
	}

} // class ends here
