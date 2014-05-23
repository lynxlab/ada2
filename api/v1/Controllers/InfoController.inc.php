<?php
/**
 * InfoController.inc.php
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
 * Info controller for handling /info API endpoint
 *
 * @author giorgio
 */
class InfoController extends AbstractController implements AdaApiInterface {

	/**
	 * Info own array key mappings
	 * 
	 * @var array
	 */
	private static $_userKeyMappings = array (
	);

	/**
	 * GET method.
	 * 
	 * 
	 * (non-PHPdoc)
	 * @see \AdaApi\AdaApiInterface::get()
	 */
	public function get(array $params = array()) {
		/**
		 * Get all published courses:
		 * - for all providers if it's MULTIPROVIDER and
		 *   a provider has not been passed
		 * - for the passed provider if it's MULTIPROVIDER and
		 *   a provider has been passed
		 * - for the selected provider if it's NOT MULTIPROVIDER
		 *   and a provider has been selected by 3d level domain
		 */
		if (MULTIPROVIDER) {
			if (count($params)===1 && isset($params['provider']) && strlen($params['provider'])>0) {
				// a tester has been passed
				$userProvider = $params['provider'];
			} else if (count($params)>1) {
				throw new APIException('Wrong Parameters: please pass the provider only', 400);
			}
		} else {
			if (empty($params)) {
				if (isset($GLOBALS['user_provider']) && strlen($GLOBALS['user_provider'])>0) {
					$userProvider = $GLOBALS['user_provider'];
				} else {
					throw new APIException('Server error: provider is not set',500);
				}
			} else {
				throw new APIException('Wrong Parameters', 400);
			}
		}
		
		if (isset($userProvider)) {
			$userProviderInfo = $this->common_dh->get_tester_info_from_pointer($userProvider);
			$user_provider_id = (!\AMA_DB::isError($userProviderInfo)) ? $userProviderInfo[0] : null;
		} else {
			// this means to get courses on all testers
			$user_provider_id = null;
		}	
		
		if (!MULTIPROVIDER && is_null($user_provider_id)) {
			throw new APIException('Selected provider '.$userProvider.' is not found in the DB',404);
		}
		
		$publishedServices = $this->common_dh->get_published_courses($user_provider_id);
		
		/**
		 * following code reflects info.php ada file
		 */
		foreach ($publishedServices as $service) {
			
			$serviceId = $service ['id_servizio'];
			$coursesAr = $this->common_dh->get_courses_for_service ($serviceId);
			if (! \AMA_DB::isError ($coursesAr)) {
				$currentTesterId = 0;
				$currentTester = '';
				$tester_dh = null;
				foreach ($coursesAr as $courseData) {
					$courseId = $courseData ['id_corso'];
					$Flag_course_has_instance = false;
					if ($courseId != PUBLIC_COURSE_ID_FOR_NEWS) {
						$newTesterId = $courseData ['id_tester'];
						if ($newTesterId != $currentTesterId) { // stesso corso su altro tester ?
							$testerInfoAr = $this->common_dh->get_tester_info_from_id ($newTesterId);
							if (! \AMA_DB::isError ($testerInfoAr)) {
								$tester = $testerInfoAr [10];
								$tester_dh = \AMA_DataHandler::instance (\MultiPort::getDSN ($tester));
								$currentTesterId = $newTesterId;
								$course_dataHa = $tester_dh->get_course ($courseId);
								$instancesAr = $tester_dh->course_instance_subscribeable_get_list (array (
										'data_inizio_previsto',
										'durata',
										'data_fine',
										'title' 
								), $courseId);
								if (is_array ($instancesAr) && count ($instancesAr) > 0) {
									$Flag_course_has_instance = true;
								}
							}
						}
						if ($Flag_course_has_instance) {
							$more_info_link = HTTP_ROOT_DIR . "/info.php?op=course_info&id=".$serviceId;
						} else {
							$more_info_link = null;
						}
						// giorgio 13/ago/2013 if it's not the news course, add it to the displayed results
						if (defined ('PUBLIC_COURSE_ID_FOR_NEWS') && intval (PUBLIC_COURSE_ID_FOR_NEWS) > 0 && PUBLIC_COURSE_ID_FOR_NEWS != $courseData ['id_corso']) {
							$returnArray [] = array (
									'name'=>$service ['nome'],
									'description'=>$service ['descrizione']
							);
							if (!is_null($more_info_link)) {
								$returnArray[count($returnArray)-1]['link'] = $more_info_link;
							}
						}
					}
				}
			}
		}
		
		if (count($returnArray)) {
			return $returnArray;
		} else {
			throw new APIException('No courses found',404);
		}
	}
	
	public function post   (array $params = array()) {}	
	public function put    (array $params = array()) {}
	public function delete (array $params = array()) {}
}
?>