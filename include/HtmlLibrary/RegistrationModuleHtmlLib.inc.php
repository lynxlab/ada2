<?php
/**
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

require_once CORE_LIBRARY_PATH .'/includes.inc.php';
require_once ROOT_DIR . '/include/HtmlLibrary/FormElementCreator.inc.php';

/**
 * used in browsing/registration.php
 *
 */
class RegistrationModuleHtmlLib
{
  public static function registrationForm($form_dataAr = array(), $errorsAr = array()) {

    $firstname = $form_dataAr['firstname'];
    $lastname = $form_dataAr['lastname'];
    $username = $form_dataAr['username'];
    $password = $form_dataAr['password'];
    $email = $form_dataAr['email'];
    $error_message = $form_dataAr['error_message'];
    $id_course = $form_dataAr['id_course'];

    $form = CDOMElement::create('form','id:services_request, name:services_request, class:fec, method:post, action:registration.php');
    $form->setAttribute('onsubmit',"return checkNec();");
    $form->setAttribute('enctype','multipart/form-data');

    $id_course_hidden  = CDOMElement::create('hidden','id:id_course, name:id_course');
    $id_course_hidden->setAttribute('value',$id_course);
    $form->addChild($id_course_hidden);

    $name = FormElementCreator::addTextInput('name','nome',$form_dataAr, $errorsAr,'',true);
    $form->addChild($name);

    $surname = FormElementCreator::addTextInput('surname','cognome',$form_dataAr, $errorsAr,'',true);
    $form->addChild($surname);

    $birthdate = FormElementCreator::addTextInput('birthdate','Data di Nascita',$form_dataAr, $errorsAr);
    $form->addChild($birthdate);
    
    $birthcity = FormElementCreator::addTextInput('birthcity','Comune o stato estero di nascita',$form_dataAr, $errorsAr);
    $form->addChild($birthcity);
    
    $birthprovince = FormElementCreator::addTextInput('birthprovince','Provincia di nascita',$form_dataAr, $errorsAr);
    $form->addChild($birthprovince);

    $genderAr = array('0'=>translateFN('Scegli un genere'),'M'=>translateFN('maschio'),'F'=>translateFN('femmina'));
    $gender = FormElementCreator::addSelect('gender','sesso',$genderAr,$form_dataAr,$errorsAr);
    $form->addChild($gender);

    $email = FormElementCreator::addTextInput('email','email',$form_dataAr, $errorsAr,'',true);
    $form->addChild($email);

    $telephone = FormElementCreator::addTextInput('telephone','telefono',$form_dataAr, $errorsAr,'',true);
    $form->addChild($telephone);

    $countryAr = array(
    	'0' => translateFN("Scegli il tuo paese"),
    	'1' => translateFN('Bulgaria'),
    	'2' => translateFN('Espana'),
    	'3' => translateFN('Islanda'),
    	'4' => translateFN('Italia'),
    	'5' => translateFN('Romania')
    );
    $country = FormElementCreator::addSelect('country','nazione',$countryAr,$form_dataAr,$errorsAr,'onchange:CreateProvince()',true);
    $form->addChild($country);

    $provinceAr = array(translateFN('Scegli una provincia'));
    $Province     =  FormElementCreator::addSelect('Province','provincia',$provinceAr,$form_dataAr,$errorsAr,'',true);
    $form->addChild($Province);

    $city = FormElementCreator::addTextInput('city','città',$form_dataAr, $errorsAr,'',true);
    $form->addChild($city);

    $address = FormElementCreator::addTextInput('address','indirizzo',$form_dataAr, $errorsAr,'',true);
    $form->addChild($address);

    $locationAr = array(
     	'0' => translateFN("Scegli un luogo"),
     	'1' => translateFN('from home'),
     	'2' => translateFN('eg-kiosk'),
     	'3' => translateFN('eg-station')
    );
    $location = FormElementCreator::addSelect('location','preferenze di accesso',$locationAr,$form_dataAr,$errorsAr);
    $form->addChild($location);

    $fiscal_code = FormElementCreator::addTextInput('fiscal_code','codice fiscale',$form_dataAr, $errorsAr);
    $form->addChild($fiscal_code);
/*
   $buttons = CDOMElement::create('div');
    $submit_button_text = translateFN('Invia');
    $buttons->addChild(CDOMElement::create('submit',"id:submit,name:submit, value:$submit_button_text"));
    $buttons->addChild(CDOMElement::create('reset'));
    $form->addChild($buttons);
*/
    $buttons = FormElementCreator::addSubmitAndResetButtons();
    $form->addChild($buttons);

    $div  = CDOMElement::create('div');
    $div->addChild(new Ctext(translateFN("If not yet registered, please insert yout personal data and click on 'Submit'")));
    $div->addChild($form);
    $div->addChild(new Ctext(translateFN("By clicking Submit, you confirm that you have read the following User Agreements, that you
understand them and that you agree to be bound by them.")));
    $privacy_linkObj = CDOMElement::create('a', 'href:'.HTTP_ROOT_DIR.'/privacy.php?lan='.$_SESSION['sess_user_language']);
    $privacy_linkObj->setAttribute('target','_blank');
    $privacy_linkObj->addChild(new CText(translateFN("Read the User Agreements")));
    $privacy_link = $privacy_linkObj->getHtml();
    $div->addChild($privacy_linkObj);

    return $div;
  }
  /**
   * generates the action menu for the registration page
   *
   * @return CORE object
   */
  public static function registrationMenu() {
    $menu = CDOMElement::create('ul');
    $home = CDOMElement::create('li');
    $home_link = CDOMElement::create('a','href:../index.php');
    $home_link->addChild(new CText(translateFN('home')));
    $home->addChild($home_link);
    $menu->addChild($home);

    $courses = CDOMElement::create('li');
    $courses_link = CDOMElement::create('a','href:../info.php');
    $courses_link->addChild(new CText(translateFN('elenco corsi')));
    $courses->addChild($courses_link);
    $menu->addChild($courses);

    return $menu;
  }
}
?>