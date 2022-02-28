<?php
/**
 * File edit_news.php
 *
 * The admin can use this module to update the informations displyed in home page.
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @author  	giorgio <g.consorti@lynxlab.com>
 * @copyright           Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_ADMIN);

if (!MULTIPROVIDER) { array_push($allowedUsersAr, AMA_TYPE_SWITCHER); }

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_SWITCHER => array('layout')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();  // = admin!

include_once 'include/admin_functions.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
AdminHelper::init($neededObjAr);

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/CourseModelForm.inc.php';
$options = '';
$languages = Translator::getSupportedLanguages();
/**
 * giorgio 12/ago/2013
 * sets files path if it's switcher or admin
 */
if (!MULTIPROVIDER && $userObj->getType()==AMA_TYPE_SWITCHER)
{
	$testers = $userObj->getTesters();
    $filePath = '/clients/'.$testers[0];
    $availableTypes = dirTree (ROOT_DIR . DIRECTORY_SEPARATOR . $filePath.'/docs');
}
else
{
    $filePath ='';
    // @author giorgio 08/mag/2013
    // extract available types from docs subdirectories
    $availableTypes = dirTree (ROOT_DIR.'/docs');
}


// @author giorgio 08/mag/2013
// get requested type from querystring
$reqType = (isset($_REQUEST['type'])) ? trim ($_REQUEST['type']) : '';
// set 'news' as default type if passed is invalid or not set
if (!in_array($reqType,$availableTypes)) $reqType = 'news';


if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
   $newsfile =  $_POST['file_edit'];
   $n = fopen($newsfile,'w');
   if (get_magic_quotes_gpc()) {
           $res = fwrite($n,stripslashes($_POST[$reqType]));
    }else{
           $res = fwrite($n,$_POST[$reqType]);
    }
   $res = fclose($n);
}

// @author giorgio 08/mag/2013
// this must be here to list newly created files possibly generated
// when handling $_POST datas.
$files_news = read_dir(ROOT_DIR. $filePath .'/docs/'.$reqType,'txt');
//print_r($files_news);


$codeLang = isset($_GET['codeLang']) ? $_GET['codeLang'] : null;
if (!isset($op)) $op=null;
switch ($op) {
    case 'edit':
        $newsmsg = array();
        // @author giorgio 08/mag/2013
        // builds something like docs/news/news_it.txt
        $fileToOpen = ROOT_DIR . $filePath . '/docs/'. $reqType .'/'. $reqType .'_'.$codeLang.'.txt';
        $newsfile = $fileToOpen;
        if ($fid = @fopen($newsfile,'r')){
        	if (!isset($newsmsg[$reqType])) $newsmsg[$reqType] = '';
            while (!feof($fid))
                $newsmsg[$reqType] .= fread($fid,4096);
            fclose($fid);
         } else {
            $newsmsg[$reqType] = translateFN("Non ci sono contenuti del tipo richiesto");
         }
         $data = AdminModuleHtmlLib::getEditNewsForm($newsmsg, $fileToOpen, $reqType);
         $body_onload = "includeFCKeditor('". $reqType ."');";
         $options = array('onload_func' => $body_onload);

        break;

    default:
        $files_to_edit = array();
        /*
        for ($index = 0; $index < count($files_news); $index++) {
            $file = $files_news[$index]['file'];
            $expr = '/^news_([a-z]{2})/';
            preg_match($expr, $file, $code_lang);
            $languageName = translator::getLanguageNameForLanguageCode($code_lang[1]);
            $href = HTTP_ROOT_DIR .'/admin/edit_news.php?op=edit&codeLang='.$code_lang[1];
            $text = translateFN('edit news in') .' '. $languageName;
            $files_to_edit[$index]['link'] = BaseHtmlLib::link($href, $text);
            $files_to_edit[$index]['data'] = translateFN('last change').': '.$files_news[$index]['data'];
        }
         *
         */
        for ($index = 0; $index < count($languages); $index++) {
            $languageName = $languages[$index]['nome_lingua'];
            $codeLang = $languages[$index]['codice_lingua'];
            $href = HTTP_ROOT_DIR .'/admin/edit_content.php?op=edit&codeLang='.$codeLang.'&type='.$reqType;
            $text = translateFN('edit content in') .' '. $languageName;
            $files_to_edit[$index]['link'] = BaseHtmlLib::link($href, $text);
            // @author giorgio 08/mag/2013
            // builds something like docs/news/news_it.txt
            $fileNews = ROOT_DIR . $filePath .'/docs/'. $reqType .'/'. $reqType .'_'.$codeLang.'.txt';
            $lastChange = 'no file';
            if (is_array($files_news) && count($files_news)>0) {
	            foreach ($files_news as $key => $value) {
	//                print_r(array($fileNews,$value['path_to_file']));
	                if ($fileNews == $value['path_to_file']) {
	                    $lastChange = $value['data'];
	                    break;
	                }

	            }
            }
            $files_to_edit[$index]['data'] = translateFN('last change').': '.$lastChange;
            if (!isset($thead_data)) $thead_data=null;
            $data = BaseHtmlLib::tableElement('', $thead_data, $files_to_edit);
        }
        break;
}
$label = translateFN('Modifica dei contenuti');
$help = translateFN('Da qui puoi modificare i contenuti di tipo '.$reqType.' che appaiono in home page');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'module' => isset($module) ? $module : '',
);
/**
 * giorgio 12/ago/2013
 *
 *  if it's the swithcer force the template in the swithcer dir
 */
if ($userObj->getType()==AMA_TYPE_SWITCHER) $layout_dataAr['module_dir'] = 'switcher';
//print_r($options);
//ARE::render($layout_dataAr, $content_dataAr, $options);
ARE::render($layout_dataAr, $content_dataAr, NULL, $options);
//print_r($files);
//print_r($languages);

