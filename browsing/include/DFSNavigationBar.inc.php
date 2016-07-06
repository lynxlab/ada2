<?php
/**
 * DFSNavigationBar.inc.php
 *
 * Contains the DFSNavigationBar class.
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2011, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
require_once ROOT_DIR . '/include/node_classes.inc.php';
/**
 * This class is responsible for rendering the previous and next node link id,
 * given a node.
 * These two nodes are calculated in respect of a depth first search ordering.
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2011, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
 if (defined('MODULES_TEST') && MODULES_TEST) {
 	require_once MODULES_TEST_PATH . '/include/AMATestDataHandler.inc.php';
 }

class DFSNavigationBar
{
    /**
     *
     * @param Node $n The node for which calculate the previous and next node link
     * @param array $params
     */
    public function  __construct(Node $n, $params = array())
    {
        $this->_currentNode = $n->id;
        if (!isset($params['prevId'])) $params['prevId'] = null;
        if (!isset($params['nextId'])) $params['nextId'] = null;

        $prevId = DataValidator::validate_node_id($params['prevId']);
        if($prevId !== false) {
            $this->_previousNode = $prevId;
        } else {
            $this->findPreviousNode($n, $params['userLevel']);
        }

        $nextId = DataValidator::validate_node_id($params['nextId']);
        if($nextId !== false) {
            $this->_nextNode = $nextId;
        } else {
            $this->findNextNode($n, $params['userLevel']);
        }

        /**
         * set the tester to be used to be the one stored in session...
         */
        if (isset($_SESSION['sess_selected_tester']) && strlen($_SESSION['sess_selected_tester'])) {
        	$this->_testerToUse = $_SESSION['sess_selected_tester'];
        }
        /**
         * ...unless a testerToUse params has been passed, in which case force that
         */
        if (isset($params['testerToUse']) && DataValidator::validate_testername($params['testerToUse'], MULTIPROVIDER)) {
        	$this->_testerToUse = $params['testerToUse'];
        }

        /**
         * @author giorgio 08/ott/2013
         * check if this is a node wich has been generated when creating a test.
         * If it is, next node is the first topic of the test.
         * BUT, I'll pass the computed $this->_nextNode to give a callBack point
         * to be used when user is in the last topic of the test.
         */
        if (defined('MODULES_TEST') && MODULES_TEST) { // && strpos($n->type,(string) constant('ADA_PERSONAL_EXERCISE_TYPE')) === 0) {
        	if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
        	$test_db = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
        	if (!is_null($n->id)) {
	        	$res = $test_db->test_getNodes(array('id_nodo_riferimento'=>$n->id));
        	} else $res = array();

        	if (!empty($res) && count($res) == 1 && !AMA_DB::isError($res)) {
        		$node = array_shift($res);
        		$this->_nextTestNode = $node['id_nodo'];
        	}

        	/**
        	 * @author giorgio 06/nov/2013
        	 * must check if computed $this->_previousNode points to a test
        	 * and get last topic if it does.
        	 */
        	if (!is_null($this->_previousNode)) {
	        	$res = $test_db->test_getNodes(array('id_nodo_riferimento'=>$this->_previousNode));
        	} else $res = array();

        	if (!empty($res) && count($res) == 1 && !AMA_DB::isError($res)) {
        		$node = array_shift($res);
        		$test = NodeTest::readTest($node['id_nodo'], $test_db);
        		$this->_prevTestTopic = count($test->_children);
        		$this->_prevTestNode = $node['id_nodo'];
        	}
        	$test_db->disconnect();
        }
	}
    /**
     * Finds the node preceding $n in a depth first search
     * and sets $this->_previousNode
     *
     * @param Node $n
     */
    protected function findPreviousNode(Node $n, $userLevel=ADA_MAX_USER_LEVEL)
    {
        $dh = $GLOBALS['dh'];

        if($n->parent_id == NULL || $n->parent_id == 'NULL') {
            $this->_previousNode = null;
            return;
        }
        /*
         * Esiste fratello con ordine n-1?
         */
        if($n->order >= 1) {
            $result = $dh->child_exists($n->parent_id, $n->order - 1, $userLevel, '<=');
            if(!AMA_DataHandler::isError($result) && $result != null) {
                $found = false;
                $id = $result;
                while(!$found) {
                    $result = $dh->last_child_exists($id, $userLevel);
                    if(!AMA_DataHandler::isError($result)) {
                        if($result != null) {
                            $id = $result;
                        } else {
                            $this->_previousNode = $id;
                            $found = true;
                        }
                    }
                }
            } else {
               $this->_previousNode = $n->parent_id;
            }
        } else {
            $this->_previousNode = $n->parent_id;
        }
    }
    /**
     * Finds the node following $n in a depth first search
     * and sets $this->_nextNode
     *
     * @param Node $n
     */
    protected function findNextNode(Node $n, $userLevel=ADA_MAX_USER_LEVEL)
    {
        $dh = $GLOBALS['dh'];

        if ($n->type == ADA_GROUP_TYPE) {
            $result = $dh->child_exists($n->id, 0, $userLevel, '>=');
            if (!AMA_DataHandler::isError($result) && $result !== FALSE) {
            	$this->_nextNode = $result;
            	return;
            }
        } else {
            $result = $dh->child_exists($n->parent_id, $n->order + 1, $userLevel, '>=');
            if (!AMA_DataHandler::isError($result) && $result != null) {
                $this->_nextNode = $result;
                return;
            }
        }    
        $found = false;
        $id = $n->id;
        while(!$found) {
            $node_info = $dh->get_node_info($id);
            if(!AMA_DataHandler::isError($node_info)) {
                    $parentId = $node_info['parent_id'];
                    $order = $node_info['ordine'];

                    if ($parentId == NULL || $parentId == 'NULL') {
                       return;
                    }

                    $result = $dh->child_exists($parentId, $order + 1, $userLevel, '>=');
                    if (!AMA_DataHandler::isError($result) && $result != null) {
                        $this->_nextNode = $result;
                        $found = true;
                    } else {
                        $id = $parentId;
                    }
                }
        }
    }
    /**
     * Renders the navigation bar
     *
     * @param string|array $hrefText if what is null, must be an array of ['next','prev'] holding
     * the text to be used in the respective hrefs. if what is a string, than hrefText is the string
     * to be used as the hreftext
     *
     * @return CDOMElement|null
     */
    public function render($hrefText = null)
    {
    	$prevText = null;
    	$nextText = null;

    	if (isset($hrefText['prev']) && strlen($hrefText['prev'])>0) $prevText = $hrefText['prev'];
    	if (isset($hrefText['next']) && strlen($hrefText['next'])>0) $nextText = $hrefText['next'];

    	$prevLink = $this->renderPreviousNodeLink($prevText);
    	$nextLink = $this->renderNextNodeLink($nextText);

    	if (is_null($prevLink) && is_null($nextLink)) {
    		$navigationBar = null;
    	} else {
    		$navigationBar = CDOMElement::create('div','class:dfsNavigationBar ui basic segment');
    		if (!is_null($prevLink)) {
    			$prevLink->setAttribute('class','ui medium left floated red animated button');
    			$iconDIV = CDOMElement::create('div','class:hidden content');
    			$iconDIV->addChild(new CText('<i class="left arrow icon"></i>'));
    			$prevLink->addChild($iconDIV);
    			$navigationBar->addChild($prevLink);
    		}
    		if (!is_null($nextLink)) {
    			$nextLink->setAttribute('class','ui medium right floated teal animated button');
    			$iconDIV = CDOMElement::create('div','class:hidden content');
    			$iconDIV->addChild(new CText('<i class="right arrow icon"></i>'));
    			$nextLink->addChild($iconDIV);
    			$navigationBar->addChild($nextLink);
    		}
    	}

        return $navigationBar;
    }

    /**
     * Renders the navigation bar
     *
     * @param string $what can be one of: <next,prev> to get only the specified link
     * if empty the whole navbar will be returned wrapped in a div with its class
     * @param string|array $hrefText if what is null, must be an array of ['next','prev'] holding
     * the text to be used in the respective hrefs. if what is a string, than hrefText is the string
     * to be used as the hreftext
     *
     * @return string
     */
    public function getHtml($what='', $hrefText = null)
    {
    	$retElement = null;
    	if (preg_match('/^next$/i', $what)>0) $retElement = $this->renderNextNodeLink($hrefText);
    	else if (preg_match('/^prev$/i', $what)>0) $retElement = $this->renderPreviousNodeLink($hrefText);
        else $retElement = $this->render($hrefText);

        return (!is_null($retElement)) ? $retElement->getHtml() : '';
    }
    /**
     * Renders the link to the previous node
     *
     * @return CDOMElement|null
     */
    protected function renderPreviousNodeLink($hrefText=null)
    {
    	if (is_null($hrefText)) $hrefText = translateFN('Precedente');
    	else $hrefText = translateFN($hrefText);

    	$retElement = CDOMElement::create('a');
    	$hrefTextElement = CDOMElement::create('div','class:visible content');
    	$hrefTextElement->addChild(new CText($hrefText));
    	$retElement->addChild($hrefTextElement);
    	$href = null;

        if ($this->_currentNode != null && $this->_previousNode != null && $this->_prevTestNode == null) {
        	$href = HTTP_ROOT_DIR.'/browsing/'.$this->linkScriptForNode($this->_previousNode)
            		.'?id_node=' . $this->_previousNode
                   	. (($this->_currentNode!=$this->_previousNode) ? '&nextId=' . $this->_currentNode : '');
        }
        // @author giorgio 08/ott/2013, check if prev node points to a test node
        else if ($this->_currentNode != null && $this->_prevTestNode != null) {
        	$href = MODULES_TEST_HTTP.'/index.php?id_test=' . $this->_prevTestNode
		   			. (!is_null($this->_prevTestTopic)  ? '&topic='  .($this->_prevTestTopic-1) : '');
        }

        if (!is_null($href)) {
        	$retElement->setAttribute('href', $href);
        	return $retElement;
        }
        return null;
    }
    /**
     * Renders the link to the next node
     *
     * @return CDOMElement|null
     */
    protected function renderNextNodeLink($hrefText=null)
    {
    	if (is_null($hrefText)) $hrefText = translateFN('Successivo');
    	else $hrefText = translateFN($hrefText);

		$retElement = CDOMElement::create('a');
    	$hrefTextElement = CDOMElement::create('div','class:visible content');
    	$hrefTextElement->addChild(new CText($hrefText));
    	$retElement->addChild($hrefTextElement);
    	$href = null;

        if ($this->_currentNode != null && $this->_nextNode != null && $this->_nextTestNode == null) {
        	$href = HTTP_ROOT_DIR.'/browsing/'.$this->linkScriptForNode($this->_nextNode)
            	   .'?id_node=' . $this->_nextNode
                   . (($this->_currentNode!=$this->_nextNode) ? '&prevId=' . $this->_currentNode : '');
        }
        // @author giorgio 08/ott/2013, check if next node points to a test node
        else if ($this->_currentNode != null && $this->_nextTestNode != null) {
        	$href = MODULES_TEST_HTTP.'/index.php?id_test=' . $this->_nextTestNode
		   			. (!is_null($this->_nextTestTopic)  ? '&topic='  .($this->_nextTestTopic+1) : '');
        }

        if (!is_null($href)) {
        	$retElement->setAttribute('href', $href);
        	return $retElement;
        }
        return null;
    }
    /**
     * Returns the parameters needed to invoke the navigation bar
     *
     * @return string
     */
    public function getNavigationBarParameters()
    {
        return 'id_node=' . $this->_currentNode
               . '&prevId=' . $this->_previousNode
               . '&nextId=' . $this->_nextNode;
    }

    /**
     * tell the php script to be called for node_id, if it's view or exercise
     *
     * @param string $node_id
     *
     * @return string
     */
    private function linkScriptForNode($node_id) {
    	$dh = $GLOBALS['dh'];

    	$retLink = 'view.php';
    	$nodeAr =  $dh->get_node_info($node_id);
    	if (!AMA_DB::isError($nodeAr) && Node::isNodeExercise($nodeAr['type'])) {
    		$retLink = 'exercise.php';
    	}
    	return $retLink;
    }

    /**
     * used when it's a test in the test module
     * see DFSTestNavigationBar derived class
     * into modules/test/include
     *
     * @var number
     */
    protected $_topic = null;
    protected $_nextTestTopic = null;
    protected $_prevTestTopic = null;
    /**
     * used when it's a test in the test module
     * see DFSTestNavigationBar derived class
     * into modules/test/include
     * @var string
     */
    protected $_nextTestNode = null;
    /**
     * used when it's a test in the test module
     * see DFSTestNavigationBar derived class
     * into modules/test/include
     * @var string
     */
    protected $_prevTestNode = null;

    /**
     *
     * @var string
     */
    protected $_currentNode = null;
    /**
     *
     * @var string
     */
    protected $_previousNode = null;
    /**
     *
     * @var string
     */
    protected $_nextNode = null;

    /**
     * tester to be used
     *
     * @var string
     */
    protected $_testerToUse = null;
}