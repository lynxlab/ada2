<?php
/**
 * map - this module provides edit user functionality of maps
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Massimo di Vita <mambo@lynxlab.com>
 * @copyright		Copyright (c) 2009-2010, Lynx s.r.l.
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
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT,AMA_TYPE_AUTHOR,AMA_TYPE_TUTOR, AMA_TYPE_VISITOR, AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
    AMA_TYPE_VISITOR => array('node', 'layout', 'course'),
    AMA_TYPE_STUDENT => array('node', 'layout', 'tutor', 'course', 'course_instance'),
    AMA_TYPE_TUTOR => array('node', 'layout', 'course', 'course_instance'),
    AMA_TYPE_AUTHOR => array('node', 'layout', 'course'),
	AMA_TYPE_SWITCHER => array('node', 'layout', 'course')
);

require_once ROOT_DIR . '/include/module_init.inc.php';
include_once 'include/browsing_functions.inc.php';
require_once 'include/map_functions.inc.php';
require_once 'include/class_image.inc.php';
require_once ROOT_DIR.'/services/include/NodeEditing.inc.php';

if ($userObj instanceof ADAGuest) {
    $self = 'guest_map';
} else {
    $self = whoami();
}



/*
 * YOUR CODE HERE
 */

// redirect sul nodo nel caso in cui venga cliccato un nodo anzichè un gruppo
if ($nodeObj->type == ADA_LEAF_TYPE || $nodeObj->type == ADA_LEAF_WORD_TYPE) {
    header('Location: view.php?id_node='.$nodeObj->id);
    exit();
}

//$node_path = $nodeObj->findPathFN();  // default: link to view.php
$node_path = $nodeObj->findPathFN('map');



// THE MAP
//$data = "<div><b>MAPPA DEL GRUPPO {$nodeObj->name}</b></div>\n\n";
$data = '<div><b>'.translateFN('MAPPA DEL GRUPPO') ." {$nodeObj->name}.</b></div>\n\n";
$data .= "<div id=\"map_content\" style=\"position:relative;top:0px;left:0px;\">\n";

$nodeList = $nodeObj->graph_indexFN();
$otherPos = array(0,0,0,0);
$tipo_mappa = returnMapType();

if (!AMA_DB::isError($nodeList) && is_array($nodeList) && count($nodeList)>0) {
// AND HIS CHILDS
	foreach($nodeList as $key){
	    if( $nodeObj->level <= $userObj->livello){
	//        print_r($key);
	        $nodePostId = 'input_'.$key['id_child']; // node id for javascript
	        $childNodeObj = read_node_from_DB($key['id_child']);
	        if($childNodeObj instanceof Node) {
	                    // saving new positions
	            if( isset($_POST[$nodePostId] )){
	                 $nodeArray = $childNodeObj -> object2arrayFN();
	                 $nodeArray['position'] = $_POST[$nodePostId]; // it is a string as requested by NodeEditing::saveNode()
	                 $nodeArray['icon'] = $key['icon_child']; // it does not function: NodeEditing::saveNode(), lines 210-214

	//                 $res = NodeEditing::saveNode($nodeArray);
	                 $res = NodeEditing::saveNodePosition($nodeArray);
	                 if($res == true){
	                    // read from here new Position
	                        $p = explode(",", $_POST[$nodePostId]);
	                        $width = ($p[2]-$p[0]);
	                        if($width < 0 ) $width *= -1;
	                        $nodeChildPos = array( $p[0], $p[1], 100, 100 );
	                }else{
	                        // code here
	                        $nodeChildPos = returnAdaNodePos($key['position_child'], $key['id_child']);
	                }
	            }else{
	                    $nodeChildPos = returnAdaNodePos($key['position_child'], $key['id_child']);
	                }
	        }else{
	                    // code here

	        }

	            //settings style, id etc etc etc for javascript
	        $thisNodeStyle = 'left:'.$nodeChildPos[0].'px;top:'.$nodeChildPos[1].'px;width:'.$nodeChildPos[2].'px;height:auto;';
	        $node_type = returnAdaNodeType($key['type_child']);
	        if((($node_type == "lemma" || $node_type == 'gruppo_lemmi') && $tipo_mappa == "lemma")|| (($node_type == "gruppo" || $node_type == 'nodo' || $node_type == 'test') && $tipo_mappa != "lemma") ){
	            $data .= '<div class="newNodeMap" style="position:absolute;'.$thisNodeStyle.'" id="'.$key['id_child'].'">';
	            $data .= '<img src="'.returnAdaNodeIcon($key['icon_child'], $key['type_child']).'"/>';

	            // setting icon
	             if( $key['type_child'] == ADA_GROUP_TYPE) {
	             	if (isset($key['children_count']) && $key['children_count']>0) $linkFile = '';
	             	else $linkFile = HTTP_ROOT_DIR.'/browsing/view.php';
	                 $data .= '<a href="'.$linkFile.'?id_node='.$key['id_child'].'">'.$key['name_child'].'</a>';
	             }elseif ($key['type_child'] == ADA_GROUP_WORD_TYPE ) {
	             	if (isset($key['children_count']) && $key['children_count']>0) $linkFile = '';
	             	else $linkFile = HTTP_ROOT_DIR.'/browsing/view.php';
	                 $data .= '<a href="'.$linkFile.'?id_node='.$key['id_child'].'&map_type=lemma">'.$key['name_child'].'</a>';
	             }else {
	             	if ($key['type_child']{0} == ADA_STANDARD_EXERCISE_TYPE) $linkFile = 'exercise';
	             	else $linkFile = 'view';
	             	$data .= '<a href="'.HTTP_ROOT_DIR.'/browsing/'.$linkFile.'.php?id_node='.$key['id_child'].'">'.$key['name_child'].'</a>';
	             }
	            // hidden div whit information for javascript
	            $data .= '<div style="display:none">'.returnAdaNodeLink($key['linked']).'</div>';
	            $data .= '</div>';
	         };
	    }
	}
}

$data .= '</div>';

//form button to save data (only for author)
if($userObj-> tipo == AMA_TYPE_AUTHOR && $mod_enabled){
    $id_node_parent = $nodeObj->id;
    $data .= '<form method="POST" action="map.php?map_type='.$tipo_mappa.'&id_node='.$id_node_parent.'" id="form_map"><input type="hidden" name="mod_map"/></form>';
};
//$data .= '<script type="text/javascript">document.getElementById("help").onclick=function(){alert($("map_content").map.nodeList)}</script>';


//$data .= "<div>LIVELLO STUDENTE: ".$userObj->livello."</div>";
/*
 * TO HERE
 */



$help = BaseHtmlLib::link(HTTP_ROOT_DIR.'/browsing/view.php?id_node='.$nodeObj->id, translateFN('Torna al contenuto del nodo'))->getHtml();

$label = translateFN('mappa');

//$help = translateFN('mappa');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'messages' => $user_messages->getHtml(),
    'agenda' => $user_agenda->getHtml(),
    'status' => $status,
    'title' => translateFN('mappa'),
    'path' => $node_path,
    'data' => $data,
    'edit_profile'=> $userObj->getEditProfilePage(),
    'help' => isset($help) ? $help : ''
);
$options = array('onload_func' => "var map = new Map()");
ARE::render($layout_dataAr, $content_dataAr, NULL, $options);

