<?php

/**
 * 22/10/2014
 * UpdateStudent_level.php - 
 *
 * @package
 * @author		sara <sara@lynxlab.com>
 * @copyright   Copyright (c) 2009-2013, Lynx s.r.l.
 * @license		http:www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * 
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout')
);

$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();

/*
 * YOUR CODE HERE
 */

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

	if (intval($_POST['level'])<0) {
		die (json_encode(array("status"=>"ERROR","msg"=>  translateFN("Il livello non può andare sotto lo zero"),"title"=>  translateFN('Notifica'))));
	}
	$level=$_POST['level'];
	$id_student=$_POST['id_student'];
	$id_instance=$_POST['id_instance'];
	$id_course = $_POST['id_course'];
	    
	$studenti_ar = array($id_student);
	$info_course = $dh->get_course($id_course);
	if (AMA_DataHandler::isError($info_course)) {
	    $retArray=array("status"=>"ERROR","msg"=>  translateFN("Problemi nell'aggiornamento del livello").'<br/>'.translateFN('Provare ad aggiornare il report e ripetere l\'operazione'),"title"=>  translateFN('Notifica'));
	} 
	else {
	    $updated = $dh->set_student_level($id_instance, $studenti_ar, $level);
	    if(AMA_DataHandler::isError($updated)) {
	       $retArray=array("status"=>"ERROR","msg"=>  translateFN("Problemi nell'aggiornamento del livello").'<br/>'.translateFN('Provare ad aggiornare il report e ripetere l\'operazione'),"title"=>  translateFN('Notifica'));
	    } 
	    else {
	        $retArray=array("status"=>"OK","msg"=>  translateFN("Hai aggiornato correttamente il livello dello studente").'<br />'.translateFN('Ricordarsi di aggiornare il report dopo aver finito le modifiche ai livelli degli studenti.'),"title"=>  translateFN('Notifica'));
	    }
	 }
	 echo json_encode($retArray);
}

 