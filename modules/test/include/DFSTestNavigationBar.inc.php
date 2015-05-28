<?php
/**
 * @package 	test
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * This class generates the navigation bar for ICoN in the modules test.
 *
 * @author giorgio
 */

require_once ROOT_DIR . '/browsing/include/DFSNavigationBar.inc.php';

class DFSTestNavigationBar extends DFSNavigationBar
{
    public function __construct($test, $params) {
    	$this->_topic = 0;
    	// if topic is passed via $_GET, it will have precedence
    	if (isset($_GET['topic']) && intval($_GET['topic'])>0) $this->_topic = intval($_GET['topic']);
    	else if (isset($params['topic']) && intval($params['topic'])>0) $this->_topic = intval($params['topic']);
    	
		// if it's needed read the node from db and call parent constructor
		if ($this->_topic==0 || $this->_topic >= count($test->_children)-1) {
			parent::__construct (read_node_from_DB($test->id_nodo_riferimento) ,$params);
		}
        
		/**
         * These must be after the parent constructor has been called
         * because must overwrite the properties set there.
         */            
        $this->_currentNode   = $test->id_nodo_riferimento;
        $this->_nextTestNode  = $test->id_nodo;
        $this->_prevTestNode  = $test->id_nodo;        

        if ($this->_topic==0) {
        	// previous node is view, next node is test
        	$this->_prevTestNode = null;
        	$this->_previousNode = $this->_currentNode;
        } else if ($this->_topic >= count($test->_children)-1) {
        	// previous node is test, next node is view
        	$this->_nextTestNode = null;
        }
    }
    
    /**
     * Renders the link to the previous node
     *
     * @return string
     */
    protected function renderPreviousNodeLink() {
    	
    	if ($this->_currentNode != null && $this->_previousNode != null && $this->_prevTestNode == null) {
			return '<a onClick="javascript:unloadParam=\'force\';"  href="'. 
					HTTP_ROOT_DIR . '/browsing/view.php?id_node=' . $this->_previousNode.
					(($this->_currentNode != $this->_previousNode) ? '&nextId='.
					$this->_currentNode : '') . '">' . translateFN ('Indietro') . '</a>';
		} else if ($this->_currentNode != null && $this->_prevTestNode != null) {
			// @author giorgio 08/ott/2013, check if prev node points to a test node			
			return '<a href="' . MODULES_TEST_HTTP . '/index.php?id_test=' . $this->_prevTestNode.
				   (!is_null($this->_topic) ? '&topic='.($this->_topic-1) : '').
				   '">' . translateFN ('Indietro') . '</a>';
		}
		return '<a>'.translateFN ('Indietro').'</a>';
    }
    
    /**
     * Renders the link to the next node
     *
     * @return string
     */
    protected function renderNextNodeLink() {
		if ($this->_currentNode != null && $this->_nextNode != null && $this->_nextTestNode == null) {
			return '<a onClick="javascript:unloadParam=\'force\';" href="'.
					HTTP_ROOT_DIR . '/browsing/view.php?id_node=' . $this->_nextNode.
					(($this->_currentNode != $this->_nextNode) ? '&prevId='.
					$this->_currentNode : '') . '">' . translateFN ('Avanti') . '</a>';
		} else if ($this->_currentNode != null && $this->_nextTestNode != null) {
			// @author giorgio 08/ott/2013, check if next node points to a test node			
			return '<a href="' . MODULES_TEST_HTTP . '/index.php?id_test=' . $this->_nextTestNode.
				   (!is_null($this->_topic) ? '&topic='.($this->_topic + 1) : '').
				   '">' . translateFN ('Avanti') . '</a>';
		}
		return '<a>'.translateFN ('Avanti').'</a>'.'<script type="text/javascript">unloadParam=\'force\';</script>';
	}
}