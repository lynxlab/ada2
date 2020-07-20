<?php
/**
 * InstancePaypalForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
require_once 'lib/classes/FForm.inc.php';
/**
 *
 */
class InstancePaypalForm extends FForm {
    public function  __construct() {
        parent::__construct();

        $action = PAYPAL_ACTION;
        $this->setAction($action);
        $submitValue = translateFN('Paga Ora');
        $this->setSubmitValue($submitValue);
        $this->setId('paypal-ada-form');

        $this->addHidden('cmd');
        $this->addHidden('business');
        $this->addHidden('id_course_instance');
        $this->addHidden('currency_code');
        $this->addHidden('notify_url');
        $this->addHidden('return');
        $this->addHidden('upload');
        $this->addHidden('address1');
        $this->addHidden('city');
        $this->addHidden('country');
        $this->addHidden('zip');
        $this->addHidden('first_name');
        $this->addHidden('last_name');
        $this->addHidden('address_override');
        $this->addHidden('email');
        $this->addHidden('amount_1');
        $this->addHidden('item_name_1');
        $this->addHidden('rm');
        $this->addHidden('no_shipping');

        /*$this->addHidden('id_layout')->withData(0);

        $this->addTextInput('data_inizio_previsto', translateFN('Data inizio previsto'))
             ->setRequired()
             ->setValidator(FormValidator::DATE_VALIDATOR);

        $this->addTextInput('durata', translateFN('Durata'))
             ->setRequired()
             ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR);

        $this->addRadios(
                'started',
                translateFN('Iniziato'),
                array(0 => translateFN('No'), 1 => translateFN('Si')),
                0);
         *
         */

    }
}