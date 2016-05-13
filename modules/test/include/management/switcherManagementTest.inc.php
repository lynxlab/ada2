<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class SwitcherManagementTest {
	protected $courseObj;

	/**
	 * SwitcherManagementTest constructor
	 *
	 * @param Course $courseObj course reference
	 */
	public function __construct(Course $courseObj) {
		$this->courseObj = $courseObj;
	}

	/**
	 * add course - test / survey association
	 * adds a course node that contains link to test / survey too
	 *
	 * @global db $dh
	 *
	 * @param int $id_test
	 *
	 * @return boolean
	 */
	public function add($id_test) {
		$dh = $GLOBALS['dh'];

		$test = $dh->test_getNode($id_test);
		if ($dh->isError($test)) {
			return false;
		}

		//creo nodo di riferimento
		$last_node = explode('_', get_max_idFN($this->courseObj->id));
		$new_id = $last_node[1] + 1;
		$new_node_id = $this->courseObj->id.'_'.$new_id;

		$url = MODULES_TEST_HTTP.'/index.php?id_test='.$id_test;
		$link = CDOMElement::create('a');
		$link->setAttribute('href',$url);
		$link->addChild(new CText($url));

		$nodo_test['id']				= $new_node_id;
		$nodo_test['id_node_author']	= $test['id_utente'];
		$nodo_test['title']				= $test['titolo'];
		$nodo_test['name']				= $test['titolo'];
		$nodo_test['text']				= $link->getHtml();
		$nodo_test['type']				= ADA_CUSTOM_EXERCISE_TEST;
		$nodo_test['parent_id']			= $this->courseObj->id.'_0';
		$nodo_test['order']				= 999;
		$nodo_test['creation_date']		= today_dateFN();
		$nodo_test['pos_x0']			= 0;
		$nodo_test['pos_y0']			= 0;
		$nodo_test['pos_x1']			= 0;
		$nodo_test['pos_y1']			= 0;
		$id_node = $dh->add_node($nodo_test);
		
		if (empty($id_node) || $dh->isError($id_node)) {
			return false;
		}

		$res = $dh->test_addCourseTest($this->courseObj->id,$id_test,$id_node);
		if (!$dh->isError($res)) {
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * delete course - test / survey association
	 * deletes course node previously generated too
	 *
	 * @global db $dh
	 *
	 * @param int $id_test
	 *
	 * @return boolean
	 */
	public function delete($id_test) {
		$dh = $GLOBALS['dh'];
		
		$coursetest = $dh->test_getCourseSurveys(array('id_corso'=>$this->courseObj->id,'id_test'=>$id_test));
		if ($dh->isError($coursetest) || empty($coursetest[0])) {
			return false;
		}
		$id_nodo = $coursetest[0]['id_nodo'];

		$res = $dh->test_removeCourseTest($this->courseObj->id, $id_test);
		
		if ($dh->isError($res)) {
			return false;
		}

		$res = $dh->remove_node($id_nodo);
		//don't mind the return of this last remove..
		//this node can be non existent because removed by an author!
		return true;
	}

	/**
	 * function that executes switcher management logic
	 *
	 * @return array an array that contains 'html', 'path' and 'title' keys
	 */
	public function run() {
		if ($_POST) {
			//delete
			if (!empty($_POST['delete_test'])) {
				foreach($_POST['delete_test'] as $v) {
					$this->delete($v);
				}
			}

			//add
			if (!empty($_POST['id_test'])) {
				$this->add($_POST['id_test']);
			}
			redirect($_SERVER['REQUEST_URI']);
		}

		require_once(MODULES_TEST_PATH.'/include/forms/switcherFormTest.inc.php');
		$form = new SwitcherFormTest($this->courseObj->id);
		
		$return = array(
			'html' => $form->getHtml(),
			'path' => $this->courseObj->titolo,
			'title' => translateFN('Gestione Sondaggi'),
		);
		return $return;
	}

	/**
	 * alias of run method
	 *
	 * @return array
	 *
	 * @see run
	 */
	public function render() {
		return $this->run();
	}
}
