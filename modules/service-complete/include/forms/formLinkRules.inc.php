<?php
/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2013, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version		   0.1
 */

/**
 * form for defining the operations used to say if
 * a course (or an instance) is considered complete
 *
 * @author giorgio
 */
class FormLinkRules
{
	/**
	 * the array of the data passed from the
	 * completerule_link_courses.php page
	 *
	 * @var array
	 */
	private $_data;

	/**
	 * the array of all the courses loaded from the DB
	 *
	 * @var array
	 */
	private $_coursesList;

	/**
	 * this holds the actual form
	 *
	 * @var CBaseElement
	 */
	private $_form;

    /**
     * FormLinkRules constructor.
     */
	public function __construct($data=array(), $coursesList=array())
    {
		$this->_data = $data;
		$this->_coursesList = (!is_null($coursesList) && is_array($coursesList)) ? $coursesList : array();
		$this->_form = $this->content();
    }

    /**
     * renders the HTML of the form
     *
     * @return string
     * @access public
     */
    public function getHtml()
    {
    	return $this->_form->getHtml();
    }

    /**
     * actually generates the form.
     *
     * this form is done through core and not extending the usual
     * FForm object because as of today (03/dic/2013) the generation
     * of forms containing tables and fields of array are not supported
     *
     * @return CBaseElement
     * @access private
     */
    private function content() {
    	$form = CDOMElement::create('form','id:linkCourses, name:linkCourses, method:post');

    	if (isset($this->_data['conditionSetId']))
    	{
    		$form->addChild(CDOMElement::create('hidden','name:conditionSetId,value:'.$this->_data['conditionSetId']));
    	}

    	if (!empty($this->_coursesList))
    	{
    		$labels = array (translateFN('Nome e titolo del corso'), translateFN('collegato alla regola selezionata'));

    		foreach ($this->_coursesList as $id_course=>$courseName)
    		{
    			$selectItem = CDOMElement::create('select','class:linkCourseSelect,name:linkCourse['.$id_course.']');
    			$optionYES  = CDOMElement::create('option','value:1');
    			$optionYES->addChild (new CText(translateFN('Sì')));
    			$optionNO  = CDOMElement::create('option');
    			$optionNO->addChild (new CText(translateFN('No')));

    			$selectItem->addChild($optionYES);
    			$selectItem->addChild($optionNO);

    			if (in_array($id_course,$this->_data['linkedCourses'])) $optionYES->setAttribute('selected', 'selected');
    			else $optionNO->setAttribute('selected', 'selected');

    			$rulesData[] = array (
    					$labels[0]=>$courseName,
    					$labels[1]=>$selectItem->getHtml());
    		}

    		$table = new Table();
    		$table->initTable('0','center','1','1','90%','','','','','1','0','','default','linkedRulesTable');
    		$table->setTable($rulesData,translateFN('Imposta i corsi da collegare'),translateFN('Imposta i corsi da collegare'));
    		$rulesTbl = $table->getTable();
    		$rulesTbl= preg_replace('/class="/', 'class="'.ADA_SEMANTICUI_TABLECLASS.' ', $rulesTbl, 1); // replace first occurence of class
    		$form->addChild (new CText($rulesTbl));

    		$submitdiv = CDOMElement::create('div','id:submitDIV,class:tooltip');
    		$submit = CDOMElement::create('button','type:submit,id:submitButton');
    		$submit->setAttribute('title', translateFN('clicca per salvare i collegamenti'));
    		$submit->addChild (new CText(translateFN('Salva')));
    		$submitdiv->addChild ($submit);
    		$form->addChild ($submitdiv);

    		$warningDIV = CDOMElement::create('div','id:divWarning');
    		$warningDIV->addChild(new CText(translateFN('Attenzione: un corso può essere associato ad una sola regola. '.
    			' Collegare un corso ad una regola sostituirà il collegamento alla regola precedente')));

    		$form->addChild ($warningDIV);


    	} else {
    		$spanEmptyList = CDOMElement::create('span','class:emptyListWarning');
    		$spanEmptyList->addChild (new CText(translateFN('Nessun corso presente')));
    		$form->addChild ($spanEmptyList);
    	}
    	return $form;
    }
}