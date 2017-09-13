<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class SurveyTest extends RootTest
{
	const NODE_TYPE = ADA_TYPE_SURVEY;
	const CHILD_CLASS = 'TopicTest';

	/**
	 * used to configure object with database's data options
	 *
	 * @access protected
	 *
	 */
	protected function configureProperties() {
		//first character
		if ($this->tipo{0} != self::NODE_TYPE) {
			return false;
		}

		//second character ignored because not applicable
		//third character delegated to parent class
		//fourth character delegated to parent class
		//fifth character ignored because not applicable
		//sixth character delegated to parent class

		return parent::configureProperties();
	}

	/**
	 * Render the object structure when the test cannot be repeated
	 *
	 * @access protected
	 *
	 * @param $return_html choose the return type
	 *
	 * @return an object of CDOMElement
	 */
	protected function renderNoRepeat($return_html=true) {
		$html = CDOMElement::create('div');
		$html->addChild(new CText(translateFN('Non puoi ripetere questo sondaggio')));

		if ($return_html) {
			return $html->getHtml();
		}
		else {
			return $html;
		}
	}

	/**
	 * Render the object structure when the test/survet cannot be accessed by student
	 *
	 * @access protected
	 *
	 * @param $return_html choose the return type
	 *
	 * @return an object of CDOMElement
	 */
	protected function renderNoLevel($return_html=true) {
		$html = CDOMElement::create('div');
		$html->addChild(new CText(translateFN('Non puoi accedere a questo sondaggio')));

		if ($return_html) {
			return $html->getHtml();
		}
		else {
			return $html;
		}
	}

	/**
	 * Builds an array with 'surveys' key having elements as returned by toArray method,
	 * enriched with 'count' key in each 'answer' element, counting givenAnswers for that answer
	 * and a '-1' counting how many questions have no answer (but have been submitted)
	 *
	 * Basically:
	 *
	 * For each survey linked to the passed instance's course:
	 * - get all topics
	 * - get all questions that have at least one associated answer
	 * - for each answer count how many people submitted that answer
	 *
	 * @param Course_instance $course_instanceObj
	 * @param AMATestDataHandler $dh the datahandler to use, or null to get it from GLOBALS
	 *
	 * @return array[]|NULL[]
	 */
	public static function getSurveysReportForCourseInstance(Course_instance $course_instanceObj, AMATestDataHandler $dh = null) {
		if (is_null($dh)) $dh = $GLOBALS['dh'];
		$noAnswerIndex = -1; // special index to store not answered questions
		$noAnswerLabel = translateFN('Non risponde');
		$totalIndex = 'total';
		$totalLabel = translateFN('Totale');

		/*
		 $test_list will be an array of elements, each one is like:
		 array (size=5)
		 'id_corso' => string '137' (length=3)
		 'id_test' => string '1143' (length=4)
		 'id_nodo' => string '137_21' (length=6)
		 'titolo' => string 'Valutazione della Formazione' (length=28)
		 'data_creazione' => string '1462974190' (length=10)
		 */
		$test_list = $dh->test_getCourseSurveys(array('id_corso'=>$course_instanceObj->getCourseId()));
		$reportData = array();
		if (!empty($test_list)) {
			$reportData['surveys'] = array();
			foreach($test_list as $test_listEL) {
				$survey = NodeTest::readTest($test_listEL['id_test']);
				$reportData['surveys'] += $survey->toArray();

				// survey loop ended, do current survey computations
				$historyArr = $dh->test_getHistoryTest(array(
						'id_corso' => $course_instanceObj->getCourseId(),
						'id_istanza_corso' => $course_instanceObj->getId(),
						'id_nodo' => $survey->id_nodo,
						'consegnato' => 1
				));

				if (!AMA_DB::isError($historyArr) && count($historyArr)>0) {
					foreach ($historyArr as $historyEl) {
						$givenAnswers = $dh->test_getGivenAnswers($historyEl['id_history_test']);
						foreach ($givenAnswers as $givenAnswer) {
							// get a reference to the answers array to add report data
							$targetArr = &$reportData['surveys'][$survey->id_nodo]['topics'][$givenAnswer['id_topic']]['questions'][$givenAnswer['id_nodo']]['answers'];
							if (count($targetArr) == 0) {
								// unset whole question if empty answers
								unset ($reportData['surveys'][$survey->id_nodo]['topics'][$givenAnswer['id_topic']]['questions'][$givenAnswer['id_nodo']]);
							} else {
								foreach (array_keys($targetArr) as $targetI) {
									if(!isset($targetArr[$targetI]['count'])) {
										$targetArr[$targetI]['count'] = 0;
									}
								}
								if (!isset($targetArr[$noAnswerIndex])) $targetArr[$noAnswerIndex] = array('titolo' => $noAnswerLabel, 'count'=>0);
								if (!isset($targetArr[$totalIndex])) $targetArr[$totalIndex] = array('titolo' => $totalLabel, 'count'=>0);

								if (array_key_exists('risposta', $givenAnswer)) {
									$givenAnswerArr = unserialize($givenAnswer['risposta']);
									if (false !== $givenAnswerArr && array_key_exists('answer', $givenAnswerArr)) {
										if (isset($targetArr[$totalIndex]['count'])) $targetArr[$totalIndex]['count']++;
										if (!is_array($givenAnswerArr['answer']) && strlen($givenAnswerArr['answer'])<=0) {
											if (isset($targetArr[$noAnswerIndex]['count'])) $targetArr[$noAnswerIndex]['count']++;
										} else {
											if (!is_array($givenAnswerArr['answer'])) $givenAnswerArr['answer'] = array($givenAnswerArr['answer']);
											foreach ($givenAnswerArr['answer'] as $anAnswer) {
												if (isset($targetArr[$anAnswer]['count'])) $targetArr[$anAnswer]['count']++;
											}
										}
									}
								}
							}
						}
					}
				} else {
					// set all counts to zero
					foreach ($reportData['surveys'] as $surveyID => $surveyData) {
						foreach ($surveyData['topics'] as $topicID => $topicData) {
							foreach ($topicData['questions'] as $questionID => $questionData) {
								foreach ($questionData['answers'] as $answerID => $answerData) {
									$reportData['surveys'][$surveyID]['topics'][$topicID]['questions'][$questionID]['answers'][$answerID]['count'] = 0;
									$reportData['surveys'][$surveyID]['topics'][$topicID]['questions'][$questionID]['answers'][$noAnswerIndex] = array('titolo' => $noAnswerLabel, 'count'=>0);
								}
							}
						}
					}
				}
			}
		}
		return $reportData;
	}

	/**
	 * Builds a table CBaseElement describing the Report Table for the passed surveyData,
	 * which is an element of the array returned by getSurveysReportForCourseInstance
	 *
	 * @param array $surveyData
	 * @param boolean $asArray true to return the table rows as an array. defaults to false
	 *
	 * @return CBaseElement
	 */
	public static function buildSurveyReportTable($surveyData, $asArray = false) {
		$doAddTable = false;
		$rowsArray = array();
		$rowsCount = 0;
		$surveyTable = CDOMElement::create('table','class:survey');
		$surveyTable->setAttribute('data-surveyid', $surveyData['id']);
		$caption = $surveyData['nome'];
		if (strlen($surveyData['titolo'])>0 && strcasecmp($surveyData['nome'], $surveyData['titolo']) !==0 ) {
			$caption .= ' - '.$surveyData['titolo'];
		}
		if ($asArray) $rowsArray[$rowsCount++] = array($caption);
		$captionel = CDOMElement::create('caption');
		$captionel->addChild(new CText($caption));
		$surveyTable->addChild($captionel);
		$surveyBody = CDOMElement::create('tbody');
		$surveyTable->addChild($surveyBody);

		// fake header needed by the dataTable
		$surveyHeader = CDOMElement::create('thead');
		$surveyTable->addChild($surveyHeader);
		$htr = CDOMElement::create('tr');
		$htd = CDOMElement::create('th');
		$htr->addChild($htd);
		$htd->addChild(new CText('header'));
		$surveyHeader->addChild($htr);

		if (count($surveyData['topics']) >0) {
			foreach ($surveyData['topics'] as $topicID => $topicData) {
				$topicRow = CDOMElement::create('tr','class:topic');
				$topicRow->setAttribute('data-topicid', $topicID);
				$topicCell = CDOMElement::create('td');
				if (strlen($topicData['titolo'])>0) $label = $topicData['titolo'];
				else if (strlen($topicData['nome'])>0) $label = $topicData['nome'];

				if (count($topicData['questions'])>0) {
					$topicCell->addChild(new CText(nl2br(trim(strip_tags($label)))));
					$topicRow->addChild($topicCell);
					$surveyBody->addChild($topicRow);
					if ($asArray) $rowsArray[$rowsCount++] = array(trim(strip_tags($label)));
					$qCount = 0;
					foreach ($topicData['questions'] as $questionID => $questionData) {
						$questionRow = CDOMElement::create('tr','class:questionrow');
						$questionRow->setAttribute('data-questionid', $questionID);
						$questionCell = CDOMElement::create('td');
						$questionRow->addChild($questionCell);

						$questionTable = CDOMElement::create('table','class:default_table doDataTable questioninners celled '.ADA_SEMANTICUI_TABLECLASS);

						$questionBody = CDOMElement::create('tbody');
						$questionTable->addChild($questionBody);
						$questionCell->addChild($questionTable);

						$questionLabelRow = CDOMElement::create('tr','class:labels');
						$labelCell = CDOMElement::create('td');
						$label = translateFN('Domanda').' #'.++$qCount;
						if ($asArray) {
							$rowsArray[$rowsCount] = array($label);
							$questionLabelRowCount = $rowsCount++;
						}
						$labelCell->addChild(new CText($label));
						$questionLabelRow->addChild($labelCell);
						$questionBody->addChild($questionLabelRow);

						$questionCountRow = CDOMElement::create('tr','class:counts');
						$countCell = CDOMElement::create('td');
						if (strlen($questionData['titolo'])>0) $label = $questionData['titolo'];
						else if (strlen($questionData['consegna'])>0) $label = $questionData['consegna'];
						if ($asArray) {
							$rowsArray[$rowsCount] = array(trim(strip_tags($label)));
							$questionCountRowCount = $rowsCount++;
						}
						$countCell->addChild(new CText(nl2br(trim(strip_tags($label)))));
						$questionCountRow->addChild($countCell);
						$questionBody->addChild($questionCountRow);

						if (count($questionData['answers']) > 0) {
							$doAddTable = true;
							foreach ($questionData['answers'] as $answerID => $answerData) {
								$label = '&nbsp;';
								if (strlen($answerData['titolo'])>0) $label = $answerData['titolo'];
								else if (strlen($answerData['testo'])>0) $label = $answerData['testo'];
								else if (strlen($answerData['nome'])>0) $label = $answerData['nome'];

								$labelCell= CDOMElement::create('td');
								$labelCell->setAttribute('data-answerid', $answerID);
								$labelCell->addChild(new CText(nl2br(trim(($label)))));
								if ($asArray) array_push($rowsArray[$questionLabelRowCount], trim($label));
								$questionLabelRow->addChild($labelCell);

								$countCell = CDOMElement::create('td');
								$countCell->addChild(new CText($answerData['count']));
								if ($asArray) array_push($rowsArray[$questionCountRowCount], $answerData['count']);
								$questionCountRow->addChild($countCell);
							}

						}

						// question row contains a cell holding the question inner table
						// add it to the main table only if (count($questionData['answers']) > 0)
						if ($doAddTable) $surveyBody->addChild($questionRow);
					}
				}
			}
		}
	return ($asArray ? $rowsArray : $surveyTable);
	}
}
