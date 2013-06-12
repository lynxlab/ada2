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
}
