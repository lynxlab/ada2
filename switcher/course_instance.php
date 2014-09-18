<?php
/**
 * course_instance file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
/**
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout', 'user','course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_SWITCHER => array('layout','user','course_instance','course')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = tutor!
//ini_set('display_errors','1');
//error_reporting(E_ALL);
require_once 'include/switcher_functions.inc.php';
require_once 'include/Subscription.inc.php';
require_once ROOT_DIR . '/include/Forms/CourseInstanceSubscriptionsForm.inc.php';
/*
 * YOUR CODE HERE
 */
/*
 * 1. ottieni gli studenti iscritti a questa istanza
 * 2. ottieni gli studenti preiscritti a questa istanza
 */
if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentStatus = $_POST['currentStatus'];
    $previousStatus = $_POST['previousStatus'];
    $instanceId = $_POST['instanceId'];
    $data = null;
    $actions = new CText('');
    $subscribedCount = $dh->course_instance_subscribed_students_count($instanceId);
    if(!AMA_DataHandler::isError($subscribedCount)) {
        $statusUpdate = array();
        foreach($currentStatus as $userId => $userStatus) {
            if($previousStatus[$userId] != $userStatus) {

/*
                if($userStatus == ADA_STATUS_SUBSCRIBED &&
                   $subscribedCount >= ADA_COURSE_INSTANCE_STUDENTS_NUMBER) {
                    $result = $dh->course_instance_student_presubscribe_remove($instanceId, $userId);
                    if(!AMA_DataHandler::isError($result)) {
                        $currentInstanceInfo = $dh->course_instance_get($instanceId);
                        if(!AMA_DataHandler::isError($currentInstanceInfo)) {
                            $instanceHa = array(
                                'data_inizio' => $currentInstanceInfo['data_inizio'],
                                'durata' => $currentInstanceInfo['durata'],
                                'data_inizio_previsto' => $currentInstanceInfo['data_inizio_previsto'],
                                'id_layout' => $currentInstanceInfo['id_layout']
                            );
                            $courseId = $currentInstanceInfo['id_corso'];
                            $result = $dh->course_instance_add($courseId, $instanceHa);
                            if(!AMA_DataHandler::isError($result)) {
                                $instanceId = $result;
                                $s = new Subscription($userId, $instanceId);
                                $s->setSubscriptionStatus($userStatus);
                                $subscribedCount = Subscription::addSubscription($s);
                            } else {
                                $data = new CText('Errore in aggiunta nuova istanza corso');
                            }

                        } else {
                            $data = new CText('Errore in ottenimento informazioni istanza corso');
                        }
                    } else {
                        $data = new CText('Errore in rimozione preiscrizione');
                    }
                } else {
*/
                    $s = new Subscription($userId, $instanceId);
                    $s->setSubscriptionStatus($userStatus);
                    $s->setStartStudentLevel(null); // null means no level update
                    $subscribedCount = Subscription::updateSubscription($s);
/*
                }
 *
 */
            }
        }

        if($data == null) {
            $data = new CText(translateFN('Hai aggiornato correttamente lo stato delle iscrizioni'));
        }
    }
    else {
        $data = new CText('');
    }
}
else {
    if(!($courseObj instanceof Course) || !$courseObj->isFull()) {
        $data = new CText(translateFN('Corso non trovato'));
        $actions = new CText('');
    } else if(!($courseInstanceObj instanceof Course_instance) || !$courseInstanceObj->isFull()) {
        $data = new CText(translateFN('Istanza corso non trovata'));
        $actions = new CText('');
    } else {
        $courseId = $courseObj->getId();
        $instanceId = $courseInstanceObj->getId();
        $presubscriptions = Subscription::findPresubscriptionsToClassRoom($instanceId);
       
        $subscriptions = Subscription::findSubscriptionsToClassRoom($instanceId);
        
        $subscribe_users_link = BaseHtmlLib::link(
                "subscriptions.php?id_course=$courseId&id_course_instance=$instanceId",
                translateFN('Upload file'));
        $subscribe_user_link = BaseHtmlLib::link(
                "subscribe.php?id_course=$courseId&id_course_instance=$instanceId",
                translateFN('Iscrivi studente'));
        $actions = BaseHtmlLib::plainListElement('class:inline_menu',array($subscribe_users_link, $subscribe_user_link));


        if(count($presubscriptions) == 0 && count($subscriptions) == 0) {
            $data = new CText(translateFN('Non ci sono studenti iscritti e/o preiscritti'));
        } else {
            /*
             * Dovrebbe mostrare anche
             *  $s->getSubscriberId(),
             *  $s->getSubscriberFullname(),
             *  $s->getSubscriptionDate(),
             *  $s->getSubscriptionStatus()
             */



            //show a pop-up with student subscription for each student

            //first: make associative arrays by ID of presubscription
            $ids_student = array();
            $tmp_presubscriptions = $presubscriptions;
            $presubscriptions = array();
			foreach($tmp_presubscriptions as $k=>$v) {
				$ids_student[] = $v->getSubscriberId();
				$presubscriptions[$v->getSubscriberId()] = $v;
			}
			//second: retrieve data for presubscription
            if (!empty($ids_student)) {
			$student_subscribed_course_instance = $dh->get_students_subscribed_course_instance($ids_student,true);
            }

			//third: make associative arrays by ID of subscription
			$ids_student = array();
			$tmp_subscriptions = $subscriptions;
			$subscriptions = array();
			foreach($tmp_subscriptions as $k=>$v) {
				$ids_student[] = $v->getSubscriberId();
				$subscriptions[$v->getSubscriberId()] = $v;
			}

			//forth: retrieve data for subscription and add it to preexistent array
            if (!empty($ids_student)) {
                foreach($dh->get_students_subscribed_course_instance($ids_student) as $k=>$v) {
                    $student_subscribed_course_instance[$k]=$v;
                }
            }
//var_dump($student_subscribed_course_instance);die();
			//fifth: create tooltips (html + javascript)
			/*$tooltips = '';
			foreach($student_subscribed_course_instance as $k=>$v) {
				$link = CDOMElement::create('a');
				$link->setAttribute('id','tooltip'.$k);
				$link->setAttribute('href','#');
				if (isset($presubscriptions[$k])) {
					$nome = $presubscriptions[$k]->getSubscriberFullname();
					$link->addChild(new CText($nome));
					$presubscriptions[$k]->setSubscriberFullname($link->getHtml());
				}
				else {
					$nome = $subscriptions[$k]->getSubscriberFullname();
					$link->addChild(new CText($nome));
					$subscriptions[$k]->setSubscriberFullname($link->getHtml());
				}
				$ul = CDOMElement::create('ul');
				foreach($v as $i=>$l) {
					$nome_corso = $l['titolo'].(!empty($l['title'])?' '.$l['title']:'');
					$li = CDOMElement::create('li');
					$li->addChild(new CText($nome_corso));
					$ul->addChild($li);
				}

				$tip = CDOMElement::create('div','id:tooltipContent'.$k);
				$tip->addChild(new CText(translateFN('Studente iscritto ai seguenti corsi:<br />')));
				$tip->addChild($ul);
				$tooltips.=$tip->getHtml();
				$js = '<script type="text/javascript">new Tooltip("tooltip'.$k.'", "tooltipContent'.$k.'", {className: "tooltip", offset: {x:-15, y:0}, hook: {target:"leftMid", tip:"rightMid"}});</script>';
				$tooltips.=$js;
			}*/
			//end
            //var_dump($student_subscribed_course_instance);die();
            $arrayUsers=array();
            $arrayUsers= array_merge($arrayUsers,$presubscriptions);
            $arrayUsers= array_merge($arrayUsers,$subscriptions);
           // var_dump($arrayUsers);die();
            $data=array();
            foreach($arrayUsers as $user)
            {
            
                $name = $user->getSubscriberFullname();
                
                /* add tooltip */
                $UserInstances = array();
                $UserInstances = $student_subscribed_course_instance[$user->getSubscriberId()];
                
                if(!empty($UserInstances))
                {
                    $title = 'Studente iscritto ai seguenti corsi :'.'<br>';

                    foreach($UserInstances as $UserInstance)
                    {
                        $title = $title.''.$UserInstance['titolo'].' - '.$UserInstance['title'].'<br>';
                    }
                }
                
                $span_label = CDOMElement::create('span','id:user');
                $span_label->setAttribute('title', $title);
                $span_label->setAttribute('class', 'UserName tooltip');
                $span_label->addChild(new CText($name));
                
                $title = '';
                
                /* select user status */
                
                $select=CDOMElement::create('select', 'id:select_status');  

                $option_Presubscribed = CDOMElement::create('option');
                $option_Presubscribed->setAttribute('value', ADA_STATUS_PRESUBSCRIBED);
                $option_Presubscribed->addChild(new CText(translateFN("Preiscritto")));

                $option_Subscribed = CDOMElement::create('option');
                $option_Subscribed->setAttribute('value', ADA_STATUS_SUBSCRIBED);
                $option_Subscribed->addChild(new CText(translateFN("Iscritto")));

                $option_Removed = CDOMElement::create('option');
                $option_Removed->setAttribute('value', ADA_STATUS_REMOVED);
                $option_Removed->addChild(new CText(translateFN("Rimosso")));
                
                $option_Visitor = CDOMElement::create('option');
                $option_Visitor->setAttribute('value', ADA_STATUS_VISITOR);
                $option_Visitor->addChild(new CText(translateFN("In visita")));
                
                $option_Completed = CDOMElement::create('option');
                $option_Completed->setAttribute('value', ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED);
                $option_Completed->addChild(new CText(translateFN("Completato")));
                
                switch ($user->getSubscriptionStatus()){
                    
                    case ADA_STATUS_PRESUBSCRIBED:
                        $option_Presubscribed->setAttribute('selected','selected');
                        break;
                    case ADA_STATUS_SUBSCRIBED:
                        $option_Subscribed->setAttribute('selected','selected');
                        break;
                    case ADA_STATUS_REMOVED:
                        $option_Removed->setAttribute('selected','selected');
                        break;
                    case ADA_STATUS_VISITOR:
                        $option_Visitor->setAttribute('selected','selected');
                        break;
                    case ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED:
                        $option_Completed->setAttribute('selected','selected');
                        break;
                }
               
                $select->addChild($option_Presubscribed);
                $select->addChild($option_Subscribed);
                $select->addChild($option_Removed);
                $select->addChild($option_Visitor);
                $select->addChild($option_Completed);

                $select->setAttribute('onchange', 'saveStatus(this)');
                
                $livello = $dh->_get_student_level($user->getSubscriberId(),$instanceId);
                
                if(is_int($user->getSubscriptionDate())) //if getSubscriptionDate() return an int means that it is setted in Subscription costructor to time()
                {
                    $data_iscrizione='-';
                }
                else
                {
                    $data_iscrizione = ts2dFN($user->getSubscriptionDate());
                }
                $userArray = array(translateFN('nome')=>$span_label->getHtml(),translateFN('status')=>$select->getHtml(),translateFN('data_iscrizione')=>$data_iscrizione,translateFN('livello')=>$livello);
                
                if(defined('MODULES_CODEMAN') && (MODULES_CODEMAN))
                {
                    $code = $user->getSubscriptionCode();
                    $userArray[translateFN('Codice iscrizione')] = $code;
                }
                
                if(defined('ADA_PRINT_CERTIFICATE') && (ADA_PRINT_CERTIFICATE))
                {
                   $UserObj = Multiport::findUser($user->getSubscriberId(),$instanceId);
                   $certificate = $UserObj->Check_Requirements_Certificate($user->getSubscriberId());
                   if($certificate)
                   {
                       $imgDoc = CDOMElement::create('img','src:'.HTTP_ROOT_DIR.'/layout/'.$_SESSION['sess_template_family'].'/img/document.png');
                       $imgDoc->setAttribute('class', 'imgDoc tooltip');
                       $imgDoc->setAttribute('title', translateFN('stampa certificato'));
                   }
                   else {
                       $imgDoc = CDOMElement::create('img','src:'.HTTP_ROOT_DIR.'/layout/'.$_SESSION['sess_template_family'].'/img/document.png');
                       $imgDoc->setAttribute('class', 'imgDoc tooltip');
                       $imgDoc->setAttribute('title', translateFN('certificato non disponibile'));
                   }
                   $userArray[translateFN('Certificato')] = $imgDoc->getHtml();
                }
                
               
                array_push($data,$userArray); 
            }
            $table=new Table();
            $table->initTable('0','center','1','1','10%','','','','','1','0','','default','course_instance_Table');
            $table->setTable($data);
            $data = new CourseInstanceSubscriptionsForm($presubscriptions, $subscriptions, $instanceId);

        }
    }
}
$help = translateFN('Da qui il provider admin può gestire le iscrizioni alla classe selezionata');

$edit_profile=$userObj->getEditProfilePage();
$edit_profile_link=CDOMElement::create('a', 'href:'.$edit_profile);
$edit_profile_link->addChild(new CText(translateFN('Modifica profilo')));
/*
 * OUTPUT
 */
//$optionsAr = array('onload_func' => "PAGER.showPage('subscribed');");
$optionsAr = array('onload_func' => "initDoc();");

$content_dataAr = array(
    'banner'=> $banner,
    'path' => $path,
    'label' => $label,
    'status'=> $status,
    'user_name'=> $user_name,
    'user_type'=> $user_type,
    'menu' => $menu,
    'help' => $help,
    'edit_switcher'=>$edit_profile_link->getHtml(),
    'data' => $actions->getHtml() . $data->getHtml() . $tooltips,
    'table'=>$table->getTable(),
    'messages' => $user_messages->getHtml(),
    'agenda '=> $user_agenda->getHtml()
);
$layout_dataAr['CSS_filename'] = array (
                JQUERY_UI_CSS,
		JQUERY_DATATABLE_CSS
                );
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_DATATABLE,
                JQUERY_NO_CONFLICT
		);



ARE::render($layout_dataAr, $content_dataAr, null, $optionsAr);
