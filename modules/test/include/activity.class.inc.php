<?php
/**
 * @package test
 * @author	giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

require_once(ROOT_DIR.'/comunica/include/MessageHandler.inc.php');
require_once(MODULES_TEST_PATH.'/include/test.class.inc.php');

class ActivityTest extends TestTest
{
	const NODE_TYPE = ADA_TYPE_ACTIVITY;
	/**
	 * @author giorgio 22/ott/2014
	 * 
	 * set it to true if you want to remove
	 * answers from history_answer table when
	 * user clicks back or retry
	 * 
	 * @var boolean
	 */
	const REMOVE_ANSWER_ON_RETRY_OR_BACK = false;
	
	protected $_forceFeedback = false;
	
	/**
	 * true only if the user is doing a
	 * retry of the activity last topic
	 * 
	 * @var boolean
	 */
	protected $_isLastTopicRetry = false;
	
	/**
	 * true only if the user is a tutor
	 * and is displaying the evaluation page
	 * setted in TutorManagementTest::view_history_tests
	 * method, and used when rendering a question
	 * 
	 * @var boolean 
	 */
	protected $_isTutorEvaluating = false;
	
	/**
	 * used to configure object with database's data options
	 * MUST BE OVERRIDDEN BECAUSE OF THE NODE_TYPE const
	 *
	 * @access protected
	 *
	 */
	protected function configureProperties() {
		$this->shuffle_answers = true;
	
		//first character
		if ($this->tipo{0} != self::NODE_TYPE) {
			return false;
		}
	
		//second character ignored because not applicable
		//third character delegated to parent class
		//fourth character delegated to parent class
	
		//fifth character
		switch($this->tipo{4}) {
			default:
			case ADA_NO_TEST_BARRIER:
				$this->barrier = false;
				break;
			case ADA_YES_TEST_BARRIER:
				$this->barrier = true;
				break;
		}
		
		// set forceFeedback property as appropriate
		if (isset ($_GET['forcefeedback']) &&  intval($_GET['forcefeedback'])===1 && !isset($_GET['unload'])) {
			$this->_forceFeedback = true;
		}
		
		$key = $this->getSessionKey();
		
		$this->_isLastTopicRetry = isset($_SESSION[$key]['islasttopicretry']) ? $_SESSION[$key]['islasttopicretry'] : false;
		 
		//sixth character delegated to parent class
	    // MUST BE CALLED THIS WAY BECAUSE OF THE
	    // NODE_TYPE constant
		return RootTest::configureProperties();
	}
	
	/**
	 *  override the render function by just passing the forcedFeedback
	 */
	public function render($return_html = true, $feedback = false, $rating = false, $rating_answer = false) {
		
		if (isset($this->_children[$this->_currentTopic]) && is_a($this->_children[$this->_currentTopic],'TopicTest')) {
			$currentTopicObj = $this->_children[$this->_currentTopic];
			$currentTopicObj->checkAndSetIsAnswered();			
		}
		return parent::render($return_html,$this->_forceFeedback);
	}
	
	/**
	 * @author giorgio 26/feb/2014
	 * 
	 * removes all given answers in the currentTopic from history table 
	 */
	private function removeAnswers () {
		if (self::REMOVE_ANSWER_ON_RETRY_OR_BACK && is_array($this->_children[$this->_currentTopic]->_children) && 
		     count($this->_children[$this->_currentTopic]->_children)>0 ) {
			foreach ($this->_children[$this->_currentTopic]->_children as $question) {
				$todelID[] = $question->id_nodo;
			}
			
			if (isset($todelID)) {
				if ($this->_isLastTopicRetry) {
					/**
					 * call setLastHistoryID to set the _id_history_test
					 * that was unsetted by the closing of the history test row
					 */
					$this->_id_history_test = $this->getLastHistoryID();
				}
				$GLOBALS['dh']->test_removeTestAnswerNodeForSession($todelID,$this->_id_history_test);
			}			
		}		
	}
	
	/**
	 * if the answer session has been cleared and there still is the forcedFeeback
	 * to true, it's most likely that the user has done a reload of the page
	 * showing the correct answer, so we'll redirect to the question page by
	 * removing the forcedfeedback in the URI query string.
	 */
	private function redirectOnEmptySession()
	{
		$key = $this->getSessionKey();
		
		if ($this->_forceFeedback) {
			
			if (!isset($_SESSION[$key]['forcefeedback']) || is_null($_SESSION[$key]['forcefeedback'])) {
				/**
				 * @author giorgio 17/feb/2014
				 * 
				 * Uncomment below if on Retry click the desired behaviour is to close the current
				 * activity session and open and new session starting from the current topic.
				 *  
				 * e.g.:
				 * Let's pretend the user is at: index.php?id_test=543&topic=4&forcefeedback=1
				 * if she clicks retry then her current session will be closed on the db and shall
				 * have saved answers from topic 0 to topic 4 included. And a new session will be
				 * opened that shall store from topic 5 onwards (or until she clicks again retry)
				 * 
				 */
				 // $this->saveTest();
				 
				/**
				 * @author giorgio 23/ott/2014
				 * 
				 * Remember if it's a last topic retry to force calling getLastHistoryID
				 */
				 if ($this->isLastTopic()) $_SESSION[$key]['islasttopicretry'] = true;
				 
				 redirect (preg_replace('/&forcefeedback=\d*/', '', $_SERVER['REQUEST_URI']));
			} 
			else {
				unset ($_SESSION[$key]['forcefeedback']);				
			}
		} else {
			unset ($_SESSION[$key]['islasttopicretry']);
		}
	}
	
	/**
	 * override renderingHtml to add a retry button
	 * if it's showing the feedback page in an 
	 * immediate feedback type of activity
	 * 
	 * @see RootTest::renderingHtml()
	 */
	protected function renderingHtml(&$ref = null,$feedback=false,$rating=false,$rating_answer=false) {
		
		$retval = parent::renderingHtml ( $ref, $feedback, $rating, $rating_answer );
		
		if ($this->_forceFeedback && $this->tipo {2} == ADA_IMMEDIATE_TEST_INTERACTION) {
			$div = CDOMElement::create ( 'div', 'class:submit_test' );
			$button = CDOMElement::create ( 'button', 'id:redo' );
			$button->setAttribute ( 'onclick', 'javascript:self.document.location.reload();' );
			$button->addChild ( new CText ( translateFN ( 'Riprova' ) ) );
			$div->addChild ( $button );
			
			/**
			 * @author giorgio 13/mag/2014
			 * 
			 * add show answers button only on mobile devices
             * removed because of the required of the client  (graffio 30/06/2014)
			 */
			if (false && isset($_SESSION['mobile-detect']) && $_SESSION['mobile-detect']->isMobile()) {
				/**
				 * the class of the following div will be changed by the onclick javascript
				 */
				$button = CDOMElement::create ( 'button', 'id:showAnswersBtn,class:showAnswers');
				$button->setAttribute ( 'onclick', 'javascript:showAnswersMobile();' );
				$button->addChild ( new CText ( translateFN ( 'Mostra Risposte' ) ) );
				
				$div->addChild ( $button );
				
				$hideAnswerLbl = CDOMElement::create('div','id:hideAnswersLbl');
				$hideAnswerLbl->setAttribute('style', 'display:none;');
				$hideAnswerLbl->addChild( new CText ( translateFN ( 'Nascondi Risposte' ) ) );
				
				$showAnswerLbl = CDOMElement::create('div','id:showAnswersLbl');
				$showAnswerLbl->setAttribute('style', 'display:none;');
				$showAnswerLbl->addChild( new CText ( translateFN ( 'Mostra Risposte' ) ) );
				
				$div->addChild ( $hideAnswerLbl );
				$div->addChild ( $showAnswerLbl );
			}
			
			$retval->addChild ( CDOMElement::create ( 'div', 'class:clearfix' ) );
			$retval->addChild ( $div );
			
			if (!isset($_GET['unload']) && $this->isLastTopic()) {
				$this->saveTest();
			}
		}
		
		return $retval;
	}
	
	/**
	 * this function contains (and execute) all object logic
	 * (e.g.: manipulating session / database)
	 * 
	 * In this class the topic is set from GET and 
	 * session is set without current topic
	 *
	 * @access public
	 *
	 */
	public function run($id_history_test = null) {
		if (!is_null($id_history_test) && intval($id_history_test)>0) {
			$this->feedback = true;
			$this->_currentTopic = self::EOT;
			$this->_id_history_test = $id_history_test;
		}
		else {
			/**
			 * Note: previewMode is rendered by setting the
			 * logged user (which is an author) as a fake student
			 * BUT no history must be saved in the DB
			 */						
			if (RootTest::isSessionUserAStudent() || $_SESSION['sess_id_user_type'] == AMA_TYPE_TUTOR) {
				if (!$this->checkStudentLevel() && !$this->previewMode) {
					$this->noLevel = true;
				}
				else if (!$this->checkRepeatable() && !$this->previewMode) {
					$this->noRepeat = true;
				}
				else {
					$this->setSession();
					$this->setCurrentTopicFromGET();
					$this->pickRandomQuestion();
					
					/**
					 * @author giorgio 26/feb/2014
					 * 
					 * the test module index page is always reloaded by the index.js onunload handler
					 * must check if the $_GET['unload'] parameters not to be set, otherwise we
					 * are deleting the user confirmed answers.
					 */
					if (!$this->previewMode && !$this->_forceFeedback && !isset($_GET['unload']) && empty($_POST[self::POST_TOPIC_VAR])) {
						$this->removeAnswers();
					}
		
					if (!$this->previewMode && $this->endOfTest()) {
						$this->saveAnswers();
						$this->saveTest();
					}
					else {
						if ($this->tipo{2}!=ADA_IMMEDIATE_TEST_INTERACTION) {
							$this->setCurrentTopic();
						}
						else if ($this->tipo{2}==ADA_IMMEDIATE_TEST_INTERACTION &&  $this->_forceFeedback) {
							$this->setCurrentTopic();
						}
						
						$this->redirectOnEmptySession();
						
						/**
						 * @author giorgio 26/feb/2014
						 * 
						 * the $_GET['unload'] parameter is set by the DFSTestNavigationBar
						 * when generating the previous and next link, so it should be
						 * quite safe to save the whole test only when that parameter
						 * is set and is equal to the "force" string.
						 * 
						 */
						if (!$this->previewMode) {
							if ($this->_isLastTopicRetry) $this->_id_history_test = $this->getLastHistoryID();
							$this->recordAttempt();
							if (!isset($_GET['unload'])) {
								$this->setTimeLimit();
								$this->saveAnswers();
							}	
						}
						
					}
				}
			} else {
				$this->setCurrentTopicFromGET();
			}
		}	
	} // end run method

	/**
	 * get currentTopic value from $_GET parameter and set it to _currentTopic
	 *
	 * @access protected
	 *
	 * @return returns true if the topic has been setted, false otherwise
	 */
	protected function setCurrentTopicFromGET() {
		if (isset($_GET['topic']) && trim($_GET['topic']) == self::EOT) {
			$this->_currentTopic = self::EOT;
		}
		else {
			parent::setCurrentTopicFromGET();
		}		
	}
	
	/**
	 * Forcefeedback getter
	 * 
	 * @return boolean
	 */
	public function getForceFeedback() {
		return $this->_forceFeedback;
	}
	// end setCurrentTopicFromGET
	
	/**
	 * @author giorgio 23/ott/2014
	 * 
	 * checks if the current topic is the last one
	 * Note: EOT is a special topic beyond the last one
	 * 
	 * @return boolean true if it's the last topic
	 * 
	 * @access private
	 */
	private function isLastTopic() {
		return ($this->_currentTopic >= count($this->_children)-1);
	}
	
	/**
	 * @author giorgio 23/ott/2014
	 * 
	 * get the last used history_test_id for the current user,
	 * in the current instance and in the current course
	 * 
	 * @return NULL|number NULL on error or if nothing is found
	 * 
	 * @access private
	 */
	private function getLastHistoryID() {
		
		return $GLOBALS['dh']->test_getLastHistoryID($_SESSION['sess_id_course_instance'], $_SESSION['sess_id_course'], $_SESSION['sess_id_user']);
	}
	
	/**
	 * This gets called from
	 * TutorManagementTest::view_history_tests
	 * when displaying history and it's needed for
	 * proper history displaying
	 * 
	 * @param string $feedbackType the feedback type to set
	 * 
	 * @access public
	 */	
	public function setFeedBackForHistory($feedbackType = ADA_FEEDBACK_TEST_INTERACTION) {
		$this->tipo{2} = $feedbackType;
	}
	
	/**
	 * isTutorEvaluating setter
	 * 
	 * @param boolean $value
	 * 
	 * @access public
	 */
	public function setTutorEvaluating($value=true) {
		$this->_isTutorEvaluating = $value;
	}
	
	/**
	 * isTutorEvaluating getter
	 * 
	 * @return boolean
	 * 
	 * @access public
	 */
	public function isTutorEvaluating() {
		return $this->_isTutorEvaluating;
	}

}
