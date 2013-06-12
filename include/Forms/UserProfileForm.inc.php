<?php
/**
 * UserSubscriptionForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
require_once 'UserRegistrationForm.inc.php';
require_once ROOT_DIR . '/include/Layout.inc.php';

class UserProfileForm extends UserRegistrationForm {
	/**
	 * @author giorgio 29/mag/2013
	 * 
	 * added extra parameter to constructor to allow editing of student confirmed registration
	 * 
	 */
    public function  __construct($languages=array(),$allowEditProfile=false,$allowEditConfirm=false) {
        parent::__construct();
        $this->addHidden('id_utente')->withData(0);
        $this->addPasswordInput('password', translateFN('Password'));
             //->setValidator(FormValidator::PASSWORD_VALIDATOR);

        $this->addPasswordInput('passwordcheck', translateFN('Conferma la password'));
        
        /**
         * @author giorgio 29/mag/2013
         * 
         * added select field to allow editing of user confirmed registration status
         */
        if ($allowEditConfirm) {
        	$this->addSelect(
        			'stato',
        			translateFN('Confermato'), 
        			array(
        				  ADA_STATUS_PRESUBSCRIBED=>translateFN('No'), 
        				  ADA_STATUS_REGISTERED=>translateFN('Si')
        	             ), 
        			0);
        }
             //->setValidator(FormValidator::PASSWORD_VALIDATOR);
        if ($allowEditProfile) {
            $this->addTextarea('profilo', translateFN('Il tuo profilo utente'));
        }

        $layoutObj = new UILayout();
        $this->addSelect(
            'layout',
             translateFN('Layout'),
             $layoutObj->getAvailableLayouts(),
             0);

        if(is_array($languages) && count($languages) > 0) {

            $this->addSelect(
                'lingua',
                 translateFN('Lingua'),
                 $languages,
                 0);
        }
    }
}