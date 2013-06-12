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

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_SWITCHER => array('layout', 'course')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();  // = admin!

include_once 'include/admin_functions.inc.php';

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/CourseModelForm.inc.php';
$options = '';
$languages = Translator::getSupportedLanguages();
$files_news = read_dir(ROOT_DIR.'/docs/news','txt');
//print_r($files_news);

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
   $newsfile =  $_POST['file_edit'];
   $n = fopen($newsfile,'w');
   if (get_magic_quotes_gpc()) {
           $res = fwrite($n,stripslashes($_POST['news']));
    }else{
           $res = fwrite($n,$_POST['news']);
    }
   $res = fclose($n);
}

$codeLang = $_GET['codeLang'];
switch ($op) {
    case 'edit':
        $newsmsg = array();
        $fileToOpen = ROOT_DIR . '/docs/news/news_'.$codeLang.'.txt';
        $newsfile = $fileToOpen; 
        if ($fid = @fopen($newsfile,'r')){
            while (!feof($fid))
                $newsmsg['news'] .= fread($fid,4096);
            fclose($fid);
         } else {
            $newsmsg['news'] = translateFN("Non ci sono news");
         }
         $data = AdminModuleHtmlLib::getEditNewsForm($newsmsg, $fileToOpen);
         $body_onload = "includeFCKeditor('news');";
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
            $href = HTTP_ROOT_DIR .'/admin/edit_news.php?op=edit&codeLang='.$codeLang;
            $text = translateFN('edit news in') .' '. $languageName;
            $files_to_edit[$index]['link'] = BaseHtmlLib::link($href, $text);
            $fileNews = ROOT_DIR . '/docs/news/news_'.$codeLang.'.txt';
            $lastChange = 'no file';
            foreach ($files_news as $key => $value) {
//                print_r(array($fileNews,$value['path_to_file']));
                if ($fileNews == $value['path_to_file']) {
                    $lastChange = $value['data'];
                    break;
                }
                
            }
            $files_to_edit[$index]['data'] = translateFN('last change').': '.$lastChange;
            $data = BaseHtmlLib::tableElement('', $thead_data, $files_to_edit);
        }
        break;
}
$label = translateFN('Modifica delle news');
$help = translateFN('Da qui l\'admin può modificare le news che appaiono in home page');

$menu_dataAr = array(
  array('href' => 'add_tester.php', 'text' => translateFN('Aggiungi tester')),
  array('href' => 'add_service.php', 'text' => translateFN('Aggiungi servizio')),
  array('href' => 'add_user.php', 'text' => translateFN('Aggiungi utente')),
  array('href' => 'import_language.php', 'text' => translateFN('Import Language'))
  );
$actions_menu = AdminModuleHtmlLib::createActionsMenu($menu_dataAr);

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'actions_menu' => $actions_menu->getHtml(),
    'data' => $data->getHtml(),
    'module' => $module,
    'messages' => $user_messages->getHtml()
);
//print_r($options);
//ARE::render($layout_dataAr, $content_dataAr, $options);
ARE::render($layout_dataAr, $content_dataAr, NULL, $options);
//print_r($files);
//print_r($languages);

