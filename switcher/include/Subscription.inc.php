<?php
/**
 * Subscription.inc.php file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
/**
 * Description of Subscription
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Subscription
{
    /*
     * Andranno in Subscription_Mapper
     */

    /**
     * Given a classroom identifier, retrieves all the presubscriptions to the
     * classroom, if any.
     *
     * @param integer $classRoomId
     * @return array an array of Subscription objects
     */
    static public function findPresubscriptionsToClassRoom($classRoomId) {
        $dh = $GLOBALS['dh'];
        $result = $dh->get_presubscribed_students_for_course_instance($classRoomId);

        if(AMA_DataHandler::isError($result)) {
            return array();
        } else {
            $subscriptionsAr = array();

            foreach($result as $r) {
                $subscription = new Subscription($r['id_utente'], $classRoomId,$r['data_iscrizione']);
                $subscription->setSubscriberFullname($r['nome'] . ' ' . $r['cognome']);
                $subscription->setSubscriptionStatus($r['status']);
                $subscription->setLastStatusUpdate($r['laststatusupdate']);
                $subscription->_loadedStatus = $subscription->getSubscriptionStatus();
                if(defined('MODULES_CODEMAN') && (MODULES_CODEMAN))
                {
                    $subscription->setSubscriptionCode($r['codice']);
                }
                $subscriptionsAr[] = $subscription;
            }

            return $subscriptionsAr;
        }
    }

    /**
     * Given a classroom identifier, retrieves all the presubscriptions to the
     * classroom, if any.
     *
     * @param integer $classRoomId
     * @return array an array of Subscriptions
     */
    static public function findSubscriptionsToClassRoom($classRoomId, $all=false) {
        $dh = $GLOBALS['dh'];
        $result = $dh->get_students_for_course_instance($classRoomId, $all);

        if(AMA_DataHandler::isError($result)) {
            return array();
        } else {
            $subscriptionsAr = array();

            foreach($result as $r) {
                $subscription = new Subscription($r['id_utente'], $classRoomId,$r['data_iscrizione']);
                $subscription->setSubscriberFullname($r['nome'] . ' ' . $r['cognome']);
                $subscription->setSubscriberFirstname($r['nome']);
                $subscription->setSubscriberLastname($r['cognome']);
                $subscription->setSubscriptionStatus($r['status']);
                $subscription->setLastStatusUpdate($r['laststatusupdate']);
                $subscription->_loadedStatus = $subscription->getSubscriptionStatus();
                if(defined('MODULES_CODEMAN') && (MODULES_CODEMAN))
                {
                    $subscription->setSubscriptionCode($r['codice']);
                }
                $subscriptionsAr[] = $subscription;
            }

            return $subscriptionsAr;
        }

    }

    static public function addSubscription(Subscription $s) {
        $dh = $GLOBALS['dh'];
        if($s->getSubscriptionStatus() == ADA_STATUS_SUBSCRIBED) {

            $result = $dh->course_instance_student_presubscribe_add(
                    $s->getClassRoomId(),
                    $s->getSubscriberId(),
                    $s->getStartStudentLevel()
            );

            if(!AMA_DataHandler::isError($result)) {
                $result = $dh->course_instance_student_subscribe(
                        $s->getClassRoomId(),
                        $s->getSubscriberId(),
                        ADA_STATUS_SUBSCRIBED,
                        $s->getStartStudentLevel(),
                		$s->getLastStatusUpdate()
                );
            }

            if(AMA_DataHandler::isError($result)) {
               //print_r($result);
            }
        } else {
            //echo 'sono qui';
        }

    }
    static public function updateSubscription(Subscription $s) {
        $dh = $GLOBALS['dh'];
        if($s->getSubscriptionStatus() == ADA_STATUS_REMOVED) {
            $result = $dh->course_instance_student_presubscribe_remove(
                    $s->getClassRoomId(),
                    $s->getSubscriberId()
            );
        }
        else {
            $result = $dh->course_instance_student_subscribe(
                    $s->getClassRoomId(),
                    $s->getSubscriberId(),
                    $s->getSubscriptionStatus(),
            		$s->getStartStudentLevel(),
            		$s->getLastStatusUpdate()
            );
        }
        if(AMA_DataHandler::isError($result)) {

        }

        return $result;
    }
    static public function deleteSubscription(Subscription $s) {}

    static public function deleteAllSubscriptionsToClassRoom($classRoomId) {
        $dh = $GLOBALS['dh'];
        $result = $dh->course_instance_students_subscriptions_remove_all($classRoomId);
        if(AMA_DataHandler::isError($result)) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param integer $userId the id of the subscribing user
     * @param integer $classRoomId the id of the classroom
     * @param integer $subscriptionDate the timestamp of the subscription
     */
    public function __construct($userId, $classRoomId,$subscriptionDate=0, $startStudentLevel=1) {
        $this->_subscriberId = $userId;
        $this->_classRoomId = $classRoomId;
        $this->_startStudentLevel = $startStudentLevel;

        if($subscriptionDate == 0) {
            $this->_subscriptionDate = time();
        } else {
            $this->_subscriptionDate = $subscriptionDate;
        }

        $this->_subscriberFullname = '';
        $this->_subscriberUsername = '';
        $this->_subscriptionStatus = ADA_STATUS_PRESUBSCRIBED;
    }
    /**
     *
     * @return integer the StartStudentLevel of the subscriber
     */
    public function getStartStudentLevel() {
        return $this->_startStudentLevel;
    }

    /**
     *
     * @return integer the id of the subscriber
     */
    public function getSubscriberId() {
        return $this->_subscriberId;
    }
    /**
     *
     * @return integer the id of the classroom
     */
    public function getClassRoomId() {
        return $this->_classRoomId;
    }
    /**
     *
     * @return string the fullname of the subscriber
     */
    public function getSubscriberFullname() {
        return $this->_subscriberFullname;
    }
    /**
     *
     * @return string the firstname of the subscriber
     */
    public function getSubscriberFirstname() {
        return $this->_subscriberFirstname;
    }
    /**
     *
     * @return string the lastname of the subscriber
     */
    public function getSubscriberLastname() {
        return $this->_subscriberLastname;
    }
    /**
     *
     * @return string a string representation of the subscription date
     */
    public function getSubscriptionDate() {
        return $this->_subscriptionDate;
    }
    /**
     *
     * @return string the subscription status as string
     */
    public function getSubscriptionStatus() {
        return $this->_subscriptionStatus;
    }
    /**
     *
     * @return string the subscription code as string
     */
    public function getSubscriptionCode() {
        return $this->_subscriptionCode;
    }

    /**
     * @return number last status update timestamp
     */
    public function getLastStatusUpdate() {
    	return $this->_lastStatusUpdate;
    }

    public function setSubscriberFullname($fullname) {
        $this->_subscriberFullname = $fullname;
    }
    public function setSubscriberFirstname($firstname) {
        $this->_subscriberFirstname = $firstname;
    }
    public function setSubscriberLastname($lastname) {
        $this->_subscriberLastname = $lastname;
    }
    public function setSubscriptionStatus($status) {
        $this->_subscriptionStatus = $status;
        if ($this->_loadedStatus != $status) $this->setLastStatusUpdate(time());
    }
    public function setStartStudentLevel($startStudentLevel) {
    	$this->_startStudentLevel = $startStudentLevel;
    }
    public function setSubscriptionCode($code) {
    	$this->_subscriptionCode = $code;
    }
    public function setLastStatusUpdate($timestamp) {
    	$this->_lastStatusUpdate = $timestamp;
    }

    public function subscriptionStatusAsString() {
    	return self::subscriptionStatusArray()[$this->_subscriptionStatus];
    }

    public static function subscriptionStatusArray() {
        return array(
            ADA_STATUS_REGISTERED => translateFN('Registrato'),
            ADA_STATUS_PRESUBSCRIBED => translateFN('Preiscritto'),
            ADA_STATUS_SUBSCRIBED => translateFN('Iscritto'),
            ADA_STATUS_REMOVED => translateFN('Rimosso'),
            ADA_STATUS_VISITOR => translateFN('In visita'),
        	ADA_STATUS_COMPLETED => translateFN('Completato'),
        	ADA_STATUS_TERMINATED => translateFN('Terminato')
        );
    }

    private $_subscriberId;
    private $_subscriberFullname;
    private $_subscriberFirstname;
    private $_subscriberLastname;
    private $_classRoomId;
    private $_subscriptionDate;
    private $_subscriptionStatus;
    private $_subscriptionCode;
    private $_lastStatusUpdate;
    private $_loadedStatus;
}