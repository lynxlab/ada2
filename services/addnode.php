<?php
/**
 * ADD NODE.
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

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_AUTHOR, AMA_TYPE_STUDENT, AMA_TYPE_TUTOR);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_AUTHOR => array('layout','course'),
  AMA_TYPE_STUDENT => array('layout','course','course_instance'),
  AMA_TYPE_TUTOR => array('layout','course','course_instance')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
require_once 'include/author_functions.inc.php';

/*
 * YOUR CODE HERE
 */
require_once CORE_LIBRARY_PATH.'/includes.inc.php';
require_once 'include/editnode_funcs.inc.php';
require_once 'include/NodeEditing.inc.php';
require_once '../browsing/include/CourseViewer.inc.php';

if (isset($err_msg)) {
  $status = $err_msg;
}
else {
  $status = '';
}

if (!isset($op)) {
  $op = 'add_node'; //default
}

switch($op){
  case "add_note":
    //default:
    $self="student"; // per il template
    $action = "addnode";
    break;

  case "add_node":
  case "add_node_author":
  case "save":
    $self = whoami();//$self="author";
    $action = "addnode";
    break;

  case "preview":
    // case preview is handled at line ..., because we need to know
    // the type of the edited node
    break;

  default:
    $self = whoami();
    $action = 'addnode';
    $op = 'add_node';
}

if (($id_profile!=AMA_TYPE_AUTHOR) && ($id_profile!=AMA_TYPE_STUDENT) && ($id_profile!=AMA_TYPE_TUTOR)) {
  $errObj = new ADA_Error(NULL, translateFN('Utente non autorizzato, impossibile proseguire.'));
} else if ($id_profile==AMA_TYPE_STUDENT && isset($id_course_instance) && intval($id_course_instance)>0 &&
		$userObj->get_student_status($userObj->getId(),$id_course_instance)==ADA_STATUS_TERMINATED) {
	/**
	 * @author giorgio 03/apr/2015
	 *
	 * if user has the terminated status for the course instance, redirect to view
	 */
	redirect(HTTP_ROOT_DIR . '/browsing/view.php?id_node='.$parent_id.'&id_course='.$id_course.
			'&id_course_instance='.$id_course_instance);
}

$help = translateFN("Da qui l'autore può aggiungere un nuovo nodo al corso");
$menu = "";

$form_action = 'addnode';
$body_onload = "";

$navigation_history = $_SESSION['sess_navigation_history'];

if ( $op == 'add_node' ) {
  /*
   *	Provengo da view.php
   */
  /*
   * Save the page from which the user select the add node operation
   * so that, if he cancels the editing operation, we can redirect
   * him there.
   */
  if(strcmp($navigation_history->previousItem(), __FILE__) !== 0) {
    $_SESSION['page_to_load_on_cancel_editing'] = $navigation_history->previousPage();
  }
  $body_onload = "switchToFCKeditor('$template_family');";

  if (isset($id_course) && isset($type))
  {
  	if (!(isset($id_parent) && strlen($id_parent) > 0)) $id_parent = $id_course.'_'.$courseObj->getRootNodeId();
    $node_type = Utilities::getAdaNodeTypeFromString($type);

    $nodeObj = read_node_from_DB($id_parent);
    // gestione errore !!!
    // vito, 20 feb 2009
    if($node_type == ADA_NOTE_TYPE || $node_type == ADA_PRIVATE_NOTE_TYPE) {
      $node_name = 'Re: '. $nodeObj->name;
    }
    else {
      $node_name = '';
    }
    $nodePath =  $nodeObj->findPathFN();


    /*
     * determina l'id del nodo da inserire
     */
//    $last_node = get_max_idFN($id_course);
//    $tempAr = explode ("_", $last_node);
//    $new_id =$tempAr[1] + 1;
    $new_node = $id_course . "_" . '999999999';// $new_id;

    $node_to_edit = array(
                'id'             => $new_node,
                'parent_id'      => $id_parent,
                'id_node_author' => $sess_id_user,
                'level'          => $nodeObj->level,
                'order'          => $nodeObj->order,
                'version'        => 0,
            	'creation_date'  => $ymdhms,
                'icon'           => Utilities::getIconForNodeType($node_type),
                'type'           => $node_type,               // usare una select?
                'position'       => '100,100,200,200',
    // vito, 20 feb 2009
    //'name'	     => '',
                'name'	         => $node_name,              // titolo
                'title'          => $nodeObj->title, // keywords
                'bg_color'       => '#FFFFFF',
                'color'			 => '',
                'correctness'    => '',
                'copyright'      => ''
                );
                $head_form = NodeEditingViewer::getHeadForm($sess_id_user, $user_level, $user_type,$nodeObj,$new_node, $node_type);
  }
  /*
   * Provengo da author.php
   */
  else if ( isset($id_course) )
  {
    $node_type = ADA_LEAF_TYPE;

    $default_parent_node = $id_course . "_" . ADA_DEFAULT_NODE;

    $nodeObj =& read_node_from_DB($default_parent_node);
    // gestione errore !!!

    /*
     * determina l'id del nodo da inserire
     */
//    $last_node = get_max_idFN($id_course);
//    $tempAr = explode ("_", $last_node);
//    $new_id =$tempAr[1] + 1;
    $new_node = $id_course . "_" . '999999999';// $new_id;

    $node_to_edit = array(
                'id'             => $new_node,
                'parent_id'      => $default_parent_node,
                'id_node_author' => $sess_id_user,
                'level'          => $nodeObj->level,
                'order'          => $nodeObj->order,
                'version'        => 0,
            	'creation_date'  => $ymdhms,
                'icon'           => Utilities::getIconForNodeType($node_type),
                'type'           => $node_type,               // usare una select?
                'position'       => '100,100,200,200',
                'name'	         => '',              // titolo
                'title'          => $nodeObj->title, // keywords
                'bg_color'       => '#FFFFFF',
                'color'			 => '',
                'correctness'    => '',
                'copyright'      => ''
                );
                $head_form = NodeEditingViewer::getHeadForm($sess_id_user, $user_level, $user_type, $nodeObj,$new_node, $node_type);
  }
  /*
   *
   */
  else if ( !isset($id_course) && !isset($id_parent) && !isset($type) )
  {
    // qui il codice necessario a generare la pagina di aggiunta per il nodo
    // quando non vengono passati parametri
  }

  /*
   * Determina quali media l'utente può inserire in base al tipo di utente, al tipo di nodo ed all'operazione.
   */
  if (!isset($node_type)) $node_type = null;
  $flags = PreferenceSelector::getPreferences($id_profile, $node_type, ADD_OPERATION, $ADA_ELEMENT_VIEWING_PREFERENCES);

  /*
   * Genera il form contenente l'editor
   */
  /*
   * Verifica la pagina da cui l'utente proviene.
   * Se l'utente proviene da una pagina diversa da addnode.php e i dati relativi all'editing
   * del nodo sono presenti in sessione, si tratta di dati non salvati, quindi non dovrebbero
   * essere mostrati. Al momento faccio l'unset della sessione.
   * I dati relativi alla navigazione in ADA sono gestiti da un oggetto di navigazione mantenuto
   * nella variabile di sessione $sess_navigation_history.
   */
  //$navigation_history = $_SESSION['sess_navigation_history'];
  $need_to_unset_session = (strcmp($navigation_history->previousItem(), __FILE__) !== 0);

  if ( !isset($_SESSION['sess_node_editing']['node_data']) || $need_to_unset_session)
  {
    if ( $need_to_unset_session !== 0 )
    {
      unset($_SESSION['sess_node_editing']);
    }

    $media_found = array();
    $_SESSION['sess_node_editing']['media_in_db'] = serialize($media_found);
  }
  else
  {
    $node_to_edit = unserialize($_SESSION['sess_node_editing']['node_data']);

    unset($_SESSION['sess_node_editing']['node_data']);
  }

  //    $data = NodeEditingViewer::getEditingForm($form_action, $id_course, $id_course_instance, $sess_id_user, $node_to_edit, $flags);
  //    $form = $data['form'];

  $form = NodeEditingViewer::getEditingForm($form_action,
  		isset($id_course) ? $id_course : null,
  		isset($id_course_instance) ? $id_course_instance : null,
  		isset($sess_id_user) ? $sess_id_user : null, $node_to_edit, $flags);
  /* vito, 20 feb 2009
   * usa i dati presenti nella sessione per mostrare alcune informazioni relative al nodo
   * che si sta editando
   */
  $icon  = CourseViewer::getClassNameForNodeType($node_type);
  $title = Utilities::getEditingFormTitleForNodeType($node_type);
  if ($status == '') {
    $status = $title;
  }
  // vito, 20 apr 2009
  $preview_additional_data = array(
      'title'      => $title,
      'version'    => $node_to_edit['version'],
      'author'     => $user_name,
      'node_level' => $node_to_edit['level'],
      'keywords'   => $node_to_edit['title'],
      'date'       => $node_to_edit['creation_date']
  );
  /*
   $version = $node_to_edit['version'];
   $author = $user_name;
   $node_level = $node_to_edit['level'];
   $keywords = $node_to_edit['title'];
   $creation_date = $node_to_edit['creation_date'];
   $edit_link = '';
   $save_link = '';
   $node_data_and_buttons_CSS_class = 'hide_node_data';
   */
}
/*
 * Anteprima dei contenuti del nodo
 */
else if ( $op == 'preview' )
{
  //$data = NodeEditingViewer::getPreviewForm('addnode.php?op=add_node', 'addnode.php?op=save');
  //$form = $data['form'];
  $form = NodeEditingViewer::getPreviewForm('addnode.php?op=add_node', 'addnode.php?op=save');
  /* vito, 20 feb 2009
   * usa i dati presenti nella sessione per mostrare l'anteprima del nodo
   * che si sta editando. E' il metodo NodeEditingViewer::getPreviewForm
   * che si occupa di passare i dati in $_POST nella sessione, pertanto è
   * necessario che questo sia invocato prima.
   */
  $node_data = unserialize($_SESSION['sess_node_editing']['node_data']);
  $icon  = CourseViewer::getClassNameForNodeType($node_data['type']);
  if($status == '') {
    $status = translateFN('Visualizzazione anteprima del nodo');
  }

  // vito, 20 apr 2009
  /*
  * Choose the right template for the preview
  */
  switch($node_data['type']) {
    case ADA_NOTE_TYPE:
      $self = 'previewnote';
      break;
    case ADA_PRIVATE_NOTE_TYPE:
      $self = 'previewprivatenote';
      break;
    case ADA_GROUP_TYPE:
    case ADA_LEAF_WORD_TYPE:
    case ADA_GROUP_WORD_TYPE:
    case ADA_LEAF_TYPE:
    default:
      $self = 'preview';
      break;
  }
  $nodePath =  translateFN('Anteprima del nodo'). ' '.$node_data['name'];

  $preview_additional_data = array(
      'title'      => $node_data['name'],
      'version'    => $node_data['version'],
      'author'     => $user_name,
      'node_level' => $node_data['level'],
      'keywords'   => $node_data['title'],
      'date'       => $node_data['creation_date'],
      'edit_link'  => 'addnode.php?op=add_node',
      'save_link'=>'addnode.php?op=save'
  );

  if (!isset($layout_dataAr['CSS_filename'])) $layout_dataAr['CSS_filename'] = array();
  if (!isset($layout_dataAr['JS_filename'])) $layout_dataAr['JS_filename'] = array();

  $layout_dataAr['CSS_filename'][] = JQUERY_UI_CSS;
  $layout_dataAr['CSS_filename'][] = ROOT_DIR.'/external/mediaplayer/flowplayer-5.4.3/skin/minimalist.css';
  $layout_dataAr['CSS_filename'][] = JQUERY_NIVOSLIDER_CSS;
  $layout_dataAr['CSS_filename'][] = ROOT_DIR.'/js/include/jquery/nivo-slider/themes/default/default.css';
  $layout_dataAr['CSS_filename'][] = JQUERY_JPLAYER_CSS;

  $layout_dataAr['JS_filename'] = array_merge($layout_dataAr['JS_filename'], array(
  		JQUERY,
  		JQUERY_UI,
  		JQUERY_NIVOSLIDER,
  		JQUERY_JPLAYER,
  		JQUERY_NO_CONFLICT,
  		ROOT_DIR. '/external/mediaplayer/flowplayer-5.4.3/flowplayer.js',
  		ROOT_DIR.'/js/browsing/view.js'
  ));

  $body_onload = "initDoc();";

}

/*
 * Salvataggio dei contenuti del nodo
 */
else if ( $op == 'save' )
{
  $form = 'Salvataggio del nodo';

  /*
   * media inseriti nel nodo
   */
  $current_media = array();
  $node_data = unserialize($_SESSION['sess_node_editing']['node_data']);
  $current_media = NodeEditing::getMediaFromNodeText($node_data['text']);

  /*
   * crea il nuovo nodo
   */
  unset($node_data['DataFCKeditor']);

  $nodePath = '';
  $result = NodeEditing::createNode($node_data);
  if ( AMA_DataHandler::isError($result) ) {
    $errObj = new ADA_Error($result,translateFN('Errore nella creazione del nodo'));
  } else {
      $node_data['id'] = $result;
  }
  /*
   * se non si sono verificati errori, il nodo e' stato creato.
   * possono essere aggiunti eventuali media
   */
  $result = NodeEditing::updateMediaAssociationsWithNode($node_data['id'], $node_data['id_node_author'],null,$current_media);
  if (AMA_DataHandler::isError($result)) {
    $errObj = new ADA_Error($result, translateFN("Errore nell'associazione dei media al nodo"));
  }

  unset($_SESSION['sess_node_editing']);
  header("Location: $http_root_dir/browsing/view.php?id_node={$node_data['id']}");
  exit();
}

/*
 * vito, 24 apr 2009
 * build the link for the Cancel operation, that when confirmed, redirects the user
 * to the page where he clicked Add Node.
 */
$link   = $_SESSION['page_to_load_on_cancel_editing'];
$text   = addslashes(translateFN('Vuoi annullare l\'inserimento del nodo?'));
$cancel = "confirmCriticalOperationBeforeRedirect('$text','$link')";

$banner = include ROOT_DIR.'/include/banner.inc.php';

$content_dataAr = array(
  'head'         => isset($head_form) ? $head_form : '',
  'banner'       => $banner,
  'menu'         => $menu,
  'help'         => $help,
  'form'         => $form->getHtml(),
  'status'       => $status,
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'messages'     => $user_messages->getHtml(),
  'agenda'       => $user_agenda->getHtml(),
  'course_title' => '<a href="../browsing/main_index.php">'.$course_title.'</a>',
  'path'         => isset($nodePath) ? $nodePath : '',
  'back'         => isset($back) ? $back : '',
  'icon'         => $icon,
  'cancel'       => $cancel
);

$content_dataAr = array_merge($content_dataAr, $preview_additional_data);

/*
 * vito, 1 ottobre 2008: passiamo il parametro onload_func=switchToFCKeditor() per
 * mostrare l'editor. Questo risolve i problemi che si avevano con IE e evet.observe di prototype
 */
//$htmlObj = new HTML($layout_template,$layout_CSS,$user_name,"","","","","","","$body_onload");

$options = array('onload_func' => $body_onload);
ARE::render($layout_dataAr, $content_dataAr, NULL, $options);