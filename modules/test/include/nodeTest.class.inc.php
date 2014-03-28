<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

require_once(ROOT_DIR.'/include/node_classes.inc.php');
abstract class NodeTest
{
	const NODE_TYPE = null;
	const CHILD_CLASS = null;
	const GET_TOPIC_VAR = 'topic';
	const POST_TOPIC_VAR = 'question';
	const POST_SUBMIT_VAR = 'testSubmit';
	const POST_ANSWER_VAR = 'answer';
	const POST_OTHER_VAR = 'other';
	const POST_EXTRA_VAR = 'extra';
	const POST_ATTACHMENT_VAR = 'attachment';

	protected $id_nodo;
	protected $id_corso;
	protected $id_posizione;
	protected $id_utente;
	protected $id_istanza;
	protected $nome;
	protected $titolo;
	protected $consegna;
	protected $testo;
	protected $tipo;
	protected $data_creazione;
	protected $ordine;
	protected $id_nodo_parent;
	protected $id_nodo_radice;
	protected $id_nodo_riferimento;
	protected $livello;
	protected $versione;
	protected $n_contatti;
	protected $icona;
	protected $colore_didascalia;
	protected $colore_sfondo;
	protected $correttezza;
	protected $copyright;
	protected $didascalia;
	protected $durata;
	protected $titolo_dragdrop;

	protected $_parent = null;
	public $_children = null;
	protected $_session = null;
	protected $display = true;

	static protected $nodesArray = array();

	/**
	 * class constructor
	 * use static function createNode to instantiate a on object
	 *
	 * @access protected
	 *
	 * @param $data database record as array
	 *
	 */
	protected function __construct($data,$parent = null) {
		$this->setParent($parent);

		foreach($data as $k=>$v) {
			$this->{$k} = $v;
		}

		$this->configureProperties();
	}

	/**
	 * generic getter method
	 *
	 * @access public
	 * @param $name attribute to retrieve
	 *
	 * @return attribute value or null if doesn't exist
	 */
    public function __get($name)
    {
		if (!property_exists(get_class($this),$name)) {
            return null;
        }
		else return $this->{$name};
    }

	/**
	 * used to configure object with database's data options
	 *
	 * @access protected
	 *
	 */
	protected abstract function configureProperties();

	/**
	 * return necessaries html objects that represent the object
	 *
	 * @access protected
	 *
	 * @param $ref reference to the object that will contain this rendered object
	 * @param $feedback "show feedback" flag on rendering
	 * @param $rating "show rating" flag on rendering
	 * @param $rating_answer "show correct answer" on rendering
	 * 
	 * @return an object of CDOMElement
	 */
	protected abstract function renderingHtml(&$ref=null,$feedback=false,$rating=false,$rating_answer=false);

	/**
	 * retrieve node's data from database and validates it
	 *
	 * @access private
	 *
	 * @param $data id node or database record as array
     * @return an array with the node's data or an AMA_Error object
	 */
	private static function readData($data) {
		$dh = $GLOBALS['dh'];

		//check if the passed $data is the node id
		if (!is_array($data)) {
			if (intval($data)>0) {
				$data = intval($data);
				$data = $dh->test_getNode($data);
				if (AMA_DataHandler::isError($data)) {
					return $data;
				}
			}
			//or it's not the record's array
			else if (!is_array($data) || empty($data)) {
				return new AMA_Error(AMA_ERR_WRONG_ARGUMENTS);
			}
			//then it is the record's array
		}

		//eventually clean record's array from fields not compliant
		foreach($data as $k=>$v) {
			if (!property_exists(get_class(),$k)) {
				return new AMA_Error(AMA_ERR_INCONSISTENT_DATA);
			}
		}
		return $data;
	}

	/**
	 * Create node retrieving data from database
	 *
	 * @access public
	 *
	 * @param $data id node or database record as array
	 * @param $parent object reference to parent node
     * @return the relative node object or an AMA_Error object
	 */
	public static function readNode($data,$parent = null) {
		//read data from database
		$data = self::readData($data);
		if (is_object($data) && (get_class($data) == 'AMA_Error')) {
			return $data;
		}
		else {
			//and if data is valid, let's check what kind of object we need to instantiate
			//first character
			switch($data['tipo']{0}) {
				default:
					require_once(MODULES_TEST_PATH.'/include/nullTest.class.inc.php');
					return new NullTest($data,$parent);
				break;
				case ADA_TYPE_TEST:
					require_once(MODULES_TEST_PATH.'/include/test.class.inc.php');
					return new TestTest($data,$parent);
				break;
				case ADA_TYPE_SURVEY:
					require_once(MODULES_TEST_PATH.'/include/survey.class.inc.php');
					return new SurveyTest($data,$parent);
				break;
				case ADA_GROUP_TOPIC:
					require_once(MODULES_TEST_PATH.'/include/topic.class.inc.php');
					return new TopicTest($data,$parent);
				break;
				case ADA_GROUP_QUESTION:
					require_once(MODULES_TEST_PATH.'/include/question.class.inc.php');

					//second character
					switch($data['tipo']{1}) {
						case ADA_MULTIPLE_CHECK_TEST_TYPE:
							require_once(MODULES_TEST_PATH.'/include/questionMultipleCheck.class.inc.php');
							return new QuestionMultipleCheckTest($data,$parent);
						break;
						default:
						case ADA_STANDARD_TEST_TYPE:
							require_once(MODULES_TEST_PATH.'/include/questionStandard.class.inc.php');
							return new QuestionStandardTest($data,$parent);
						break;
						case ADA_LIKERT_TEST_TYPE:
							require_once(MODULES_TEST_PATH.'/include/questionLikert.class.inc.php');
							return new QuestionLikertTest($data,$parent);
						break;
						case ADA_OPEN_MANUAL_TEST_TYPE:
							require_once(MODULES_TEST_PATH.'/include/questionOpenManual.class.inc.php');
							return new QuestionOpenManualTest($data,$parent);
						break;
						case ADA_OPEN_AUTOMATIC_TEST_TYPE:
							require_once(MODULES_TEST_PATH.'/include/questionOpenAutomatic.class.inc.php');
							return new QuestionOpenAutomaticTest($data,$parent);
						break;
						case ADA_CLOZE_TEST_TYPE:
							require_once(MODULES_TEST_PATH.'/include/questionCloze.class.inc.php');
							//fourth character
							switch($data['tipo']{3}) {
								case ADA_NORMAL_TEST_SIMPLICITY:
									require_once(MODULES_TEST_PATH.'/include/questionNormalCloze.class.inc.php');
									return new QuestionNormalClozeTest($data,$parent);
								break;
								case ADA_SELECT_TEST_SIMPLICITY:
									require_once(MODULES_TEST_PATH.'/include/questionSelectCloze.class.inc.php');
									return new QuestionSelectClozeTest($data,$parent);
								break;
								case ADA_MEDIUM_TEST_SIMPLICITY:
									require_once(MODULES_TEST_PATH.'/include/questionMediumCloze.class.inc.php');
									return new QuestionMediumClozeTest($data,$parent);
								break;
								case ADA_DRAGDROP_TEST_SIMPLICITY:
									require_once(MODULES_TEST_PATH.'/include/questionDragDropCloze.class.inc.php');
									return new QuestionDragDropClozeTest($data,$parent);
								break;
								case ADA_ERASE_TEST_SIMPLICITY:
									require_once(MODULES_TEST_PATH.'/include/questionEraseCloze.class.inc.php');
									return new QuestionEraseClozeTest($data,$parent);
								break;
								case ADA_SLOT_TEST_SIMPLICITY:
									require_once(MODULES_TEST_PATH.'/include/questionSlotCloze.class.inc.php');
									return new QuestionSlotClozeTest($data,$parent);
								break;
								case ADA_MULTIPLE_TEST_SIMPLICITY:
									require_once(MODULES_TEST_PATH.'/include/questionMultipleCloze.class.inc.php');
									return new QuestionMultipleClozeTest($data,$parent);
								break;
							}
						break;
						case ADA_OPEN_UPLOAD_TEST_TYPE:
							require_once(MODULES_TEST_PATH.'/include/questionOpenUpload.class.inc.php');
							return new QuestionOpenUploadTest($data,$parent);
						break;
					}
				break;
				case ADA_LEAF_ANSWER:
					require_once(MODULES_TEST_PATH.'/include/answer.class.inc.php');
					return new AnswerTest($data,$parent);
				break;
			}
		}
	}

	/**
	 * Create a full structured test
	 *
	 * @access public
	 *
	 * @param $data id node
     * @return the relative nodes structure or an AMA_Error object
	 */
	public static function readTest($id_nodo, $dh=null) {
		if (is_null($dh)) $dh = $GLOBALS['dh'];

		//check if $id_nodo param is an integer and retrieve rows from database
		if (intval($id_nodo)>0) {
			$id_nodo = intval($id_nodo);
			$data = $dh->test_getNodesByRadix($id_nodo);
			if (AMA_DataHandler::isError($data)) {
				return $data;
			}
			else {
				$objects = array();
				$root = null;
				//ciclying all rows to instantiate and attach nodes to form a three
				//the external loop is used to catch all the nodes that doesn't find a father on first tries
				while (!empty($data)) {
					foreach($data as $k=>$v) {
						$tipo = $v['tipo']{0};
						$parent = $v['id_nodo_parent'];
						$id = $v['id_nodo'];

						//this search the root
						if (is_null($root) && ($tipo == ADA_TYPE_TEST || $tipo == ADA_TYPE_SURVEY)) {
							$objects[$id] = NodeTest::readNode($v);
							$root = $objects[$id];
							self::$nodesArray[$root->id_nodo] = $root;
							//once the row is attach, it can be deleted
							unset($data[$k]);
						}
						//this attach nodes to the right element
						else if (!is_null($parent) && isset($objects[$parent])) {
							$objects[$id] = NodeTest::readNode($v,$objects[$parent]);							
							$objects[$parent]->addChild($objects[$id]);
							//once the row is attach, it can be deleted
							unset($data[$k]);
						}
					}
				}
				//free resources
				unset($objects);
				//if $root is still null, the test doesn't exists!
				if (is_null($root)) {
					return new AMA_Error(AMA_ERR_INCONSISTENT_DATA);
				}
				else return $root;
			}
		}
		else {
			return new AMA_Error(AMA_ERR_WRONG_ARGUMENTS);
		}
	}

	/**
	 * Adds a child to object
	 *
	 * @access public
	 *
	 * @param $child child object
     * @return the relative nodes structure or an AMA_Error object
	 */
	public function addChild(NodeTest $child) {
		//use pipes in CHILD_CLASS constant to specifiy more than one possible child class
		$constants = constant(get_class($this).'::CHILD_CLASS');
		$constants = explode('|',$constants);
		foreach($constants as $v) {
			if (get_class($child) == $v || is_subclass_of($child, $v)) {
				if (is_null($this->_children)) $this->_children = array();
				$this->_children[] = $child;
				self::$nodesArray[$child->id_nodo] = &$child;
				return true;
			}
		}
		return false;
	}

	/**
	 * Add parent reference to node
	 *
	 * @param NodeTest $parent parent node reference
	 */
	public function setParent(NodeTest $parent = null) {
		$this->_parent = $parent;
	}

	/**
	 * Render the object structure
	 *
	 * @access public
	 *
	 * @param $return_html choose the return type
	 * @param $feedback "show feedback" flag on rendering
	 * @param $rating "show rating" flag on rendering
	 * @param $rating_answer "show correct answer" on rendering
	 *
	 * @return an object of CDOMElement or a string containing html
	 */
	public function render($return_html=true,$feedback=false,$rating=false,$rating_answer=false) {

		$html = $this->renderingHtml($ref,$feedback,$rating,$rating_answer);

		if (is_null($ref)) $ref = $html;

		if (!empty($this->_children) && $this->display) {
			foreach($this->_children as $v) {
				$ref->addChild($v->render(false,$feedback,$rating,$rating_answer));
			}
		}

		if ($return_html) {
			return $html->getHtml();
		}
		else {
			return $html;
		}
	}

	/**
	 * Return the desired child
	 *
	 * @access public
	 *
	 * @param $i child index
	 * @return false if the requested object doesn't exist, the object otherwise
	 */
	public function getChild($i) {
		if (isset($this->_children[$i])) return $this->_children[$i];
		else return false;
	}

	/**
	 * Return how many children the object has
	 *
	 * @access public
	 *
	 * @return the number of children or false otherwise
	 */
	public function countChildren() {
		if (is_array($this->_children)) {
			return count($this->_children);
		}
		else {
			return false;
		}
	}

	/**
	 * search a node on directly descendant children
	 *
	 * @access public
	 *
	 * @param $value value to find
	 * @param $field field used in comparison
	 * @return reference to object, an array of objects, false if not found
	 */
	public function searchChild($value,$field = 'id_nodo', $forceArray = false) {
		if (!is_array($value)) {
			$value = array($value);
		}

		$results = array();
		if (!empty($this->_children)) {
			foreach($this->_children as $c) {
				if (in_array($c->{$field},$value)) {
					$results[] = $c;
				}
			}
		}

		if (count($results) == 0) {
			return false;
		}
		else if ($forceArray || count($results) > 1) {
			return $results;
		}
		else {
			return $results[0];
		}
	}

	/**
	 * search a node on all children in a breadth-first manner
	 *
	 * @access public
	 *
	 * @param $id_nodo child id_nodo
	 * @return reference to object or false if not found
	 */
	public function searchBreadthChild($id_nodo) {
		$found = false;
		$array = array($this);

		foreach($array as $k=>$f) {
			$found = $f->searchChild($id_nodo);
			if ($found != false) {
				break;
			}
			else {
				for($i=0; $i<$f->countChildren(); $i++) {
					$array[] = $f->getChild($i);
				}
				unset($array[$k]);
			}
		}

		return $found;
	}

	/**
	 * search a node on all children in a breadth-first manner
	 *
	 * @access public
	 *
	 * @param $id_nodo child id_nodo
	 * @return reference to object or false if not found
	 */
	public function searchParent($type) {
		$parent = $this->_parent;
		while(!is_a($parent,$type) && !is_null($parent)) {
			$parent = $parent->_parent;
		}
		return $parent;
	}

	/**
	 * setter method for display variable
	 *
	 * @access public
	 *
	 * @param $value boolean
	 *
	 * @return boolean
	 */
	public function setDisplay($value) {
		if (is_bool($value)) {
			$this->display = $value;
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * method that search and replace media tag found in text
	 *
	 * @access public
	 *
	 * @param $text text (string)
	 *
	 * @return string
	 */
	protected function replaceInternalLinkMedia($text) {
		return Node::parseInternalLinkMedia($text,$this->livello, null, null, null);
	}
}
