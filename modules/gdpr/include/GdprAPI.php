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
			if (is_null($tester)) {
				if (array_key_exists('sess_selected_tester', $_SESSION)) {
					$tester = $_SESSION['sess_selected_tester'];
				} else if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && strlen($GLOBALS['user_provider'])>0) {
					$tester = $GLOBALS['user_provider'];
				}
			}
			$this->_dh = AMAGdprDataHandler::instance(\MultiPort::getDSN($tester));
		}
		$this->_dh->setObjectClassesFromRequest();
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
		else if (is_numeric($userID)) $userID = intval($userID);
		else $userID = -1;
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
			function(GdprUser $el) use($gdprUserTypes) { return $el->hasTypes($gdprUserTypes, $this); }
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

	/**
	 * Saves a Gdpr Request
	 *
	 * @param array $data
	 * @return \Lynxlab\ADA\Module\GDPR\GdprRequest
	 */
	public function saveRequest($data) {
		return $this->_dh->saveRequest($data);
	}

	/**
	 * Closes a request
	 *
	 * @param string|GdprRequest $request the uuid of the request or a GdprRequest instance
	 * @param number $closedBy id of the user closing the request. null to get it from session user
	 */
	public function closeRequest($request, $closedBy=null) {
		$this->_dh->closeRequest($request, $closedBy);
	}

	/**
	 * Confirms a request
	 *
	 * @param string|GdprRequest $request the uuid of the request or a GdprRequest instance
	 */
	public function confirmRequest($request) {
		$this->_dh->confirmRequest($request);
	}

	/**
	 * Saves a GdprPolicy
	 * @param array $data
	 * @return \Lynxlab\ADA\Module\GDPR\GdprPolicy|mixed
	 */
	public function savePolicy($data) {
		return $this->_dh->savePolicy($data);
	}

	/**
	 * Gets the array of the published policies objects,
	 * either mandatory or not
	 *
	 * @return array
	 */
	public function getPublishedPolicies() {
		return $this->_dh->getPublishedPolicies();
	}

	/**
	 * Gets the array of the mandatory policies objects
	 *
	 * @return array
	 */
	public function getMandatoryPolicies() {
		return $this->_dh->getMandatoryPolicies();
	}

	/**
	 * Gets the array of the policies accepted by the user
	 *
	 * @param integer $userID
	 * @return array
	 */
	public function getUserAcceptedPolicies($userID) {
		return $this->_dh->getUserAcceptedPolicies($userID);
	}

	/**
	 * Gets the array of the policies accepted and rejected by the user
	 *
	 * @param integer $userID
	 * @return array
	 */
	public function getUserPolicies($userID) {
		return $this->_dh->getUserPolicies($userID);
	}

	/**
	 * Saves the policies accepted by the user
	 *
	 * @param integer $data
	 * @return \stdClass
	 */
	public function saveUserPolicies($data) {
		return $this->_dh->saveUserPolicies($data, $this->getPublishedPolicies(), $this->getUserPolicies($data['userId']));
	}

	/**
	 * Checks if the passed users have accepted all the mandatory policies
	 * the returned array will have the following keys:
	 * - accepted: true or false
	 * - details: if accepted is true: null
	 *            if accepted is false: current (holding current policy) and user (holding the accepted privacy version)
	 *            if accepted is false and no user accepted version: null
	 *
	 * @param array $userIDs
	 * @return array
	 */
	public function checkMandatoryPoliciesForUserArray(array $userIDs) {
		$retArr = [];
		$policies = $this->getMandatoryPolicies();
		$userPolicies = $this->_dh->getUserPolicies($userIDs);
		foreach($userIDs as $userID) {
			if (array_key_exists($userID, $userPolicies)) {
				$retArr[$userID] = [];
				$accepted = true;
				/** @var GdprPolicy $policy */
				foreach ($policies as $policy) {
					// user MUST accept all mandatory policies!!
					$accepted = $accepted && self::ckeckAcceptedPolicy($policy, $userPolicies[$userID]);
					if (false === $accepted) {
						$retArr[$userID] = [
							'accepted' => false,
							'details' => [
								'current' => $policy,
								'user' => $userPolicies[$userID][$policy->getPolicy_content_id()]
							],
						];
						// break out of the loop at the first not accepted policy
						break;
					}
				}
				if ($accepted) {
					$retArr[$userID] = [ 'accepted' => $accepted, 'details' => null ];
				}
			} else $retArr[$userID] = [ 'accepted' => false, 'details' => null ];

		}
		return $retArr;
	}

	/**
	 * Checks if the passed user has accepted all the mandatory policies
	 *
	 * @param \ADALoggableUser $userObj
	 * @return boolean
	 */
	public function checkMandatoryPoliciesForUser(\ADALoggableUser $userObj) {
		if ($userObj->getType() == AMA_TYPE_ADMIN) {
			/**
			 * the ADMIN is not required to accept all mandatory policies
			 */
			return true;
		} else {
			/**
			 * other types of users must be checked
			 */
			$policies = $this->getMandatoryPolicies();
			if (count($policies)>0) {
				$okToLogin = true;
				$userPolicies = $this->getUserAcceptedPolicies($userObj->getId());
				/** @var GdprPolicy $policy */
				foreach ($policies as $policy) {
					// user MUST accept all mandatory policies!!
					$okToLogin = $okToLogin && self::ckeckAcceptedPolicy($policy, $userPolicies);
				}
				return $okToLogin;
			} else {
				/**
				 * if no mandatory policies, the user is OK
				 */
				return true;
			}
		}
	}

	/**
	 * checks if the passed policy is accepted by the user
	 *
	 * @param GdprPolicy $policy
	 * @param array $userPolicies
	 * @return boolean
	 */
	private static function ckeckAcceptedPolicy(GdprPolicy $policy, array $userPolicies) {
		return (array_key_exists($policy->getPolicy_content_id(), $userPolicies) &&
		intval($userPolicies[$policy->getPolicy_content_id()]['acceptedVersion']) >= $policy->getVersion());
	}

	/**
	 * Calls the datahandler findBy method
	 *
	 * @param string $className
	 * @param array $whereArr
	 * @param array $orderByArr
	 * @param \Abstract_AMA_DataHandler $dbToUse
	 * @return array
	 */
	public function findBy($className, array $whereArr = null, array $orderByArr = null, \Abstract_AMA_DataHandler $dbToUse = null) {
		return $this->_dh->findBy($className, $whereArr, $orderByArr, $dbToUse);
	}

	/**
	 * Calls the datahandler findAll method
	 *
	 * @param string $className
	 * @param array $orderBy
	 * @param \Abstract_AMA_DataHandler $dbToUse
	 * @return array
	 */
	public function findAll($className, array $orderBy = null, \Abstract_AMA_DataHandler $dbToUse = null) {
		return $this->_dh->findAll($className, $orderBy, $dbToUse);
	}

	/**
	 * Calls the datahandler getObjectClasses method
	 *
	 * @return array|string[]
	 */
	public function getObjectClasses() {
		return $this->_dh->getObjectClasses();
	}

	/**
	 * Calls the datahandler setObjectClasses method
	 *
	 * @param array $objectClasses
	 * @return \Lynxlab\ADA\Module\GDPR\GdprAPI
	 */
	public function setObjectClasses(array $objectClasses) {
		$this->_dh->setObjectClasses($objectClasses);
		return $this;
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
