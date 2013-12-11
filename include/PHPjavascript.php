<?php
/**
 * PHPjavascript.php: used to import PHP defines from ada_config.php
 * as javascript variables.
 *
 * e.g. a PHP define, such as define('HTTP_ROOT_DIR', 'http://localhost');
 * is 'imported' in javascript as var HTTP_ROOT_DIR = 'http://localhost';
 */

/**
 * function extractJavascriptVariablesFromFile: parses the given file in search of PHP define
 * and builds a list of javascript variables
 *
 * @param  string $php_config_file - the full path to a PHP script to parse.
 * @return string $javascript_content
 */
function extractJavascriptVariablesFromFile($php_config_file) {

  if (file_exists($php_config_file)) {
    $contents = @file_get_contents($php_config_file);

    /*
     * import all the defines followed by this comment // *js_import*
     */
    $ereg_define = "/define\('([a-zA-Z_]+)',(?: )*(.*)\);(?: )*\/\/(?: )*\*js_import\*/";
    $matches     = array();
    $defines     = preg_match_all($ereg_define, $contents, $matches );

    $var_names  = $matches[1];
    $var_values = $matches[2];

    /*
     * build a string containing javascript variables for the imported PHP defines
     */
    foreach($var_names as $key => $variable_name) {
      if ($variable_name) {
        $javascript_content .= "var $variable_name = {$var_values[$key]};\n";
      }
    }
    return $javascript_content;
  }
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
header("Content-type: application/x-javascript");
//header("Content-Disposition: attachment; filename=javascript_conf.js");

require_once '../config_path.inc.php';
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_ADMIN, AMA_TYPE_SWITCHER);
$trackPageToNavigationHistory = false;
require_once ROOT_DIR . '/include/module_init.inc.php';

/*
$php_config_file_dir = ROOT_DIR.'/config';
$php_config_files    = array_diff(scandir($php_config_file_dir), array('.','..'));

$javascript_content  = '';

foreach ($php_config_files as $php_config_file) {
	$path_to_php_config_file = "$php_config_file_dir/$php_config_file";
	if (is_file($path_to_php_config_file)) {
		$javascript_content .= extractJavascriptVariablesFromFile($path_to_php_config_file);
	}
}
*/
$JS_i18n = array(
	'confirmDelete' => translateFN('Stai per cancellare l\'elemento in modo definitivo. Confermi?'),
	'confirm' => translateFN('Conferma'),
	'cancel' => translateFN('Annulla'),
	'confirmTabChange' => translateFN('Ci sono dati non salvati in questa scheda. Continuare senza salvarli?'),
	'confirmLeavePage' => translateFN('Ci sono dati non salvati in questa scheda.')
);

/**
 * GIORGIO, this is not needed and exposes a security hole.
 * Removed and placed here on 13/set/2013
 * 
 * // var MODULES_DIR='<?php echo MODULES_DIR;?>';
 */
?>
//main vars
var HTTP_ROOT_DIR='<?php echo HTTP_ROOT_DIR;?>';
var HTTP_UPLOAD_PATH='<?php echo HTTP_UPLOAD_PATH;?>';
<?php if (!empty($_SESSION['sess_template_family'])): ?>
var ADA_TEMPLATE_FAMILY = '<?php echo $_SESSION['sess_template_family'];?>';
<?php else: ?>
var ADA_TEMPLATE_FAMILY = '<?php echo ADA_TEMPLATE_FAMILY;?>';
<?php endif; ?>
<?php if(!empty($_SESSION['sess_user_language'])): ?>
var USER_LANGUAGE = '<?php echo $_SESSION['sess_user_language'];?>';
<?php else: ?>
var USER_LANGUAGE = null;
<?php endif; ?>
<?php if(defined('GCAL_HOLIDAYS_FEED')): ?>
var GCAL_HOLIDAYS_FEED = '<?php echo GCAL_HOLIDAYS_FEED; ?>';
<?php else :?>
var GCAL_HOLIDAYS_FEED = '';
<?php endif; ?>

//media type
var MEDIA_IMAGE = '<?php echo _IMAGE;?>';
var MEDIA_SOUND = '<?php echo _SOUND;?>';
var MEDIA_VIDEO = '<?php echo _VIDEO;?>';
var MEDIA_LINK = '<?php echo _LINK;?>';
var MEDIA_DOC = '<?php echo _DOC;?>';
var MEDIA_EXE = '<?php echo _EXE;?>';
var MEDIA_INTERNAL_LINK = '<?php echo INTERNAL_LINK;?>';
var MEDIA_POSSIBLE_TYPE = '<?php echo POSSIBLE_TYPE;?>';
var MEDIA_PRONOUNCE = '<?php echo _PRONOUNCE;?>';
var MEDIA_FINGER_SPELLING = '<?php echo _FINGER_SPELLING;?>';
var MEDIA_LABIALE = '<?php echo _LABIALE;?>';
var MEDIA_LIS = '<?php echo _LIS;?>';
var MEDIA_MONTESSORI = '<?php echo _MONTESSORI;?>';

//translations
<?php
if (!empty($JS_i18n)) {
	echo "var i18n = Array();\n";
	foreach($JS_i18n as $k=>$v) {
		echo "i18n['".$k."'] = '".str_replace("'","\'",$v)."';\n";
	}
}

//return $javascript_content;
exit();