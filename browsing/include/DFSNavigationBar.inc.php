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
         * @author giorgio 08/ott/2013
         * check if this is a node wich has been generated when creating a test.
         * If it is, next node is the first topic of the test.
         * BUT, I'll pass the computed $this->_nextNode to give a callBack point
         * to be used when user is in the last topic of the test.
         */
        if (MODULES_TEST && strpos($n->type,(string) constant('ADA_PERSONAL_EXERCISE_TYPE')) === 0) {
        	if (isset($GLOBALS['dh'])) $GLOBALS['dh']->disconnect();
        	$test_db = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
        	$res = $test_db->test_getNodes(array('id_nodo_riferimento'=>$n->id));
        	
        	if (!empty($res) && count($res) == 1 && !AMA_DataHandler::isError($res)) {
        		$node = array_shift($res);
        		$this->_nextTestNode = $node['id_nodo'];
        	}
        	
        	/**
        	 * @author giorgio 06/nov/2013
        	 * must check if computed $this->_previousNode points to a test
        	 * and get last topic if it does.
        	 */
        	
        	$res = $test_db->test_getNodes(array('id_nodo_riferimento'=>$this->_previousNode));
        	if (!empty($res) && count($res) == 1 && !AMA_DataHandler::isError($res)) {
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
            $result = $dh->child_exists($n->parent_id, $n->order - 1, $userLevel);
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
            $result = $dh->child_exists($n->id, 0, $userLevel);
            if(AMA_DataHandler::isError($result) || $result == null) {
                $result = $dh->child_exists($n->id, 1, $userLevel);
                if (!AMA_DataHandler::isError($result)) {
                    $this->_nextNode = $result;
                }
            } else {
                $this->_nextNode = $result;
            }
        } else {
            $result = $dh->child_exists($n->parent_id, $n->order + 1, $userLevel);
            if (!AMA_DataHandler::isError($result) && $result != null) {
                $this->_nextNode = $result;
                return;
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

                    $result = $dh->child_exists($parentId, $order + 1, $userLevel);
                    if (!AMA_DataHandler::isError($result) && $result != null) {
                        $this->_nextNode = $result;
                        $found = true;
                    } else {
                        $id = $parentId;
                    }
                }
            }
        }
    }
    /**
     * Renders the navigation bar
     *
     * @return string
     */
    public function render()
    {

        $navigationBar = '<div class="dfsNavigationBar">'
                       . '<span class="previous">'
                       . $this->renderPreviousNodeLink()
                       . '</span>'
                       . '<span class="next">'
                       . $this->renderNextNodeLink()
                       . '</span>'
                       . '</div>';

        return $navigationBar;
    }
    /**
     * Renders the navigation bar
     *
     * @return string
     */
    public function getHtml($what, $hrefText = null)
    {
    	if (preg_match('/^next$/i', $what)>0) return $this->renderNextNodeLink($hrefText);
    	else if (preg_match('/^prev$/i', $what)>0) return $this->renderPreviousNodeLink($hrefText);
        else return $this->render();
    }
    /**
     * Renders the link to the previous node
     *
     * @return string
     */
    private function renderPreviousNodeLink($hrefText=null)
    {
    	if (is_null($hrefText)) $hrefText = translateFN('Indietro');
        if ($this->_currentNode != null && $this->_previousNode != null && $this->_prevTestNode == null) {
            return '<a href="'.HTTP_ROOT_DIR.'/browsing/view.php?id_node=' . $this->_previousNode
                   . (($this->_currentNode!=$this->_previousNode) ? '&nextId=' . $this->_currentNode : '')
		   .'">'
                   . $hrefText . '</a>';
        }
        // @author giorgio 08/ott/2013, check if prev node points to a test node
        else if ($this->_currentNode != null && $this->_prevTestNode != null) {
	    return '<a href="'.MODULES_TEST_HTTP.'/index.php?id_test=' . $this->_prevTestNode 
		   . (!is_null($this->_prevTestTopic)  ? '&topic='  .($this->_prevTestTopic-1) : '')
		   .'">'
		   . $hrefText . '</a>';
        }
        return '&nbsp;';
    }
    /**
     * Renders the link to the next node
     *
     * @return string
     */
    private function renderNextNodeLink($hrefText=null)
    {
    	if (is_null($hrefText)) $hrefText = translateFN('Avanti'); 
        if ($this->_currentNode != null && $this->_nextNode != null && $this->_nextTestNode == null) {
            return '<a href="'.HTTP_ROOT_DIR.'/browsing/view.php?id_node=' . $this->_nextNode
                   . (($this->_currentNode!=$this->_nextNode) ? '&prevId=' . $this->_currentNode : '')
                   .'">'
                   . $hrefText . '</a>';
        }
        // @author giorgio 08/ott/2013, check if next node points to a test node
        else if ($this->_currentNode != null && $this->_nextTestNode != null) {
	    return '<a href="'.MODULES_TEST_HTTP.'/index.php?id_test=' . $this->_nextTestNode 
		   . (!is_null($this->_nextTestTopic)  ? '&topic='  .($this->_nextTestTopic+1) : '')
		   .'">'
		   . $hrefText . '</a>';
        }
        return '&nbsp;';
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
}