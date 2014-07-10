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

			//fifth: create tooltips (html + javascript)
			$tooltips = '';
			foreach($student_subscribed_course_instance as $k=>$v) {
				$link = CDOMElement::create('a');
				$link->setAttribute('id','tooltip'.$k);
				$link->setAttribute('href','javascript:void(0);');
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
			}
			//end


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
$optionsAr = array('onload_func' => "PAGER.showPage('subscribed');");
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
    'messages' => $user_messages->getHtml(),
    'agenda '=> $user_agenda->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr, null, $optionsAr);
