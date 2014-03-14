<?php
/**
 * SubscriptionController.inc.php
 *
 * @package        API
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           API
 * @version		   0.1
 */
namespace AdaApi;

/**
 * Subscription controller for handling /testers API endpoint
 * 
 * @author giorgio
 */
class SubscriptionController extends AbstractController implements AdaApiInterface {
	
	/**
	 * POST method.
	 * 
	 * Subscribes a student to a course instance.
	 * The params array must have the id_course_instance and
	 * username parameters properly set.
	 * 
	 * (non-PHPdoc)
	 * @see \AdaApi\AdaApiInterface::post()
	 */
	public function post (array $params = array()) {

		/**
		 * Check if header says it's json
		 */
		if (strcmp($this->slimApp->request->getContentType(),'application/json')===0) {
				
			/**
			 *  SLIM has converted the body to an array alreay
			 */
			$subscriptionArr = $this->slimApp->request->getBody();
		} else if (!empty($params) && is_array($params)) {
				
			/**
			 * Assume we've been passed an array
			 */
			$subscriptionArr = $params;
		} else {
			throw new APIException('Wrong Parameters', 400);
		}
		
		/**
		 * Check if passed username and id_course_instance are OK
		 */
		if ( isset($subscriptionArr['username']) && \DataValidator::validate_username($subscriptionArr['username']) &&
		     isset($subscriptionArr['id_course_instance']) && \DataValidator::is_uinteger($subscriptionArr['id_course_instance'])) {
			
			/**
			 * Some ADA files are needed for the computation
			 */
			require_once ROOT_DIR.'/include/courses_classes.inc.php';
			require_once ROOT_DIR.'/include/CourseInstance.inc.php';
			require_once ROOT_DIR.'/switcher/include/Subscription.inc.php';
			
			/**
			 * This GLOBAL is needed by the MultiPort and Translator class
			 */
			$GLOBALS['common_dh'] = $this->common_dh;
			
			/*
			 * This GLOBAL is needed by almost everyone
			 */
			$GLOBALS['dh'] = new \AMA_DataHandler(\MultiPort::getDSN($this->authUserTesters[0]));
			$dh = $GLOBALS['dh'];
			
			$canSubscribeUser = false;
			$courseInstanceObj = new \Course_instance(intval(trim($subscriptionArr['id_course_instance'])));
			
			if($courseInstanceObj instanceof  \Course_instance && $courseInstanceObj->isFull()) {
				$startStudentLevel = $courseInstanceObj->start_level_student;
				
				$subscriberObj = \MultiPort::findUserByUsername($subscriptionArr['username']);
				if ($subscriberObj instanceof \ADAUser) {
					$result = $dh->student_can_subscribe_to_course_instance($subscriberObj->getId(), $courseInstanceObj->getId());
					if (!\AMA_DataHandler::isError($result) && $result !== false) {
						$canSubscribeUser = true;
					}

					if ($canSubscribeUser) {
						$s = new \Subscription($subscriberObj->getId(), $courseInstanceObj->getId(), 0, $startStudentLevel);
						$s->setSubscriptionStatus(ADA_STATUS_SUBSCRIBED);
						\Subscription::addSubscription($s);
						
						/**
						 * Subscribed successfully
						 */
						$saveResults = array( 'status'=>'SUCCESS',
											  'message'=>'User successfully subscribed to course instance');
					} else {
						
						/**
						 * An error occoured
						 */
						$saveResults = array( 'status'=>'FAILURE',
											  'message'=>'Cannot complete request, perhaps user is subscribed already?');
					}
				} else {
					throw new APIException('No User Found', 404);
				}
			} else {
				throw new APIException('Course Instance Not Found',404);
			}
		} else {
			throw new APIException('Wrong Parameters', 400);
		}
		
		/**
		 * Final check: if all OK return the data else throw the exception
		 */
		if (is_array($saveResults)) {
			return $saveResults;
		} else {
			throw new APIException('Unkonwn error in subscription post method', 500);
		}
		
	}
	
	public function put    (array $params = array()) {}
	public function delete (array $params = array()) {}
	public function get    (array $params = array()) {}
}

?>