<?php
/**
 * UserController.inc.php
 *
 * @package        API
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           API
 * @version		   0.1
 */
namespace AdaApi;
class UserController extends AbstractController implements AdaApiInterface {
		
	public function get (array $params = array()) {
		if (!empty($params)) {
			// $userArr = $this->common_dh->get_student($params['id']);
			$testersArr = $this->common_dh->get_testers_for_user(intval($params['id']));
			$dataHandler = \AMA_Tester_DataHandler::instance(\MultiPort::getDSN(reset($testersArr)));
			$userArr = $dataHandler->get_student(intval($params['id']));
			if (!\AMA_DB::isError($userArr)) return $userArr;
			else {
				$this->slimApp->halt(404, 'No User Found');
			}
		} else $this->slimApp->notFound();
	}
	
	public function post (array $params = array()) {
		if (!empty($params)) {
			$userArr = $this->common_dh->get_student($params['id']);
			$userArr['method'] = 'POST';
			if (!\AMA_DB::isError($userArr)) echo json_encode($userArr);
			return $userArr;
		}
	}
	
	public function put() {
		
	}
	
	public function delete() {
		
	}
}

?>