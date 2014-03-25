<?php
/**
 * UserSkillsForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    giorgio <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
require_once 'lib/classes/FForm.inc.php';


/**
 * Description of UserSkillsForm
 *
 * @package   Default
 * @author    giorgio <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class UserExtraForm extends FForm
{
    public function  __construct($action=NULL) {
        parent::__construct();
        
        if ($action != NULL) {
            $this->setAction($action);
        }
        $this->setName('extraDataForm');
        $this->setSubmitValue(translateFN('Salva'));
        /**
         * Following value to be set with a call
         * to fillWithArrayData made by the code
         * who's actually using this form
         */
        $this->addHidden('id_utente')->withData(0);
        
        self::addExtraControls($this);        
    }
    
    public static function addExtraControls (FForm $theForm)
    {
    	$theForm->addTextInput('samplefield', translateFN('Esempio'))
    	->setRequired()
    	->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

    	// add an extra field if we're embedding the controls
    	// in the standard edit_user form
    	if (!isset($this))
    	{
    		$theForm->addHidden('forceSaveExtra')->withData(true);
    	}
    }
}
