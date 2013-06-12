<?php

######################
#
#    REDIRECT
#
######################
/**
 * Base config file 
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */


$variableToClearAR = array('node','layout', 'user', 'course');

/**
 * Get needed objects
 */
$neededObjAr = array('layout','user');

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
$self = 'index';



include_once 'include/browsing_functions.inc.php';
/**
 * Redirecting on user type basis
 */
if (is_object($userObj)){
  $homepage = $userObj->getHomepage();
  $msg =   translateFN("Ridirezionamento automatico");
  header("Location: $homepage?err_msg=$msg");
  exit;
}  else {
     $homepage = $http_root_dir."/index.php";
     $msg =  urlencode($errObj->msg);
     header("Location: $homepage?err_msg=$msg");
     exit;

}

// old version
/*
if (is_object($userObj)){
        switch ($id_profile){
        case AMA_TYPE_STUDENT:
              $homepage = "$http_root_dir/browsing/user.php";
              $msg =   translateFN("Ridirezionamento automatico");
              header("Location: $homepage?err_msg=$msg");
              exit;
              break;
        case AMA_TYPE_TUTOR:
              $homepage = "$http_root_dir/tutor/tutor.php";
              $msg =   translateFN("Ridirezionamento automatico");
              header("Location: $homepage?err_msg=$msg");
              exit;
              break;
        case AMA_TYPE_AUTHOR:
              $homepage = "$http_root_dir/services/author.php";
              $msg =   translateFN("Ridirezionamento automatico");
              header("Location: $homepage?err_msg=$msg");
              exit;
              break;      
        case AMA_TYPE_SWITCHER:
              $homepage = "$http_root_dir/switcher/switcher.php";
              $msg =   translateFN("Ridirezionamento automatico");
              header("Location: $homepage?err_msg=$msg");
              exit;
              break;
        case AMA_TYPE_ADMIN:
              // vito, 9 mar 2009
              //$homepage = "$http_root_dir/admin/menu.php";
              $homepage = "$http_root_dir/admin/admin.php";
              $msg =   translateFN("Ridirezionamento automatico");
              header("Location: $homepage?err_msg=$msg");
              exit;
              break;
        }
} else {
     $homepage = $http_root_dir."/index.php";
     $msg =  urlencode($errObj->msg);
     header("Location: $homepage?err_msg=$msg");
     exit;

}
*/
?>