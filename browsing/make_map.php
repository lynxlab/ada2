<?php
$debug=0;
$ada_config_path = realpath(dirname(__FILE__).'/..');
include_once("$ada_config_path/config_path.inc.php");
include_once("$ada_config_path/include/utilities.inc.php");
include_once("include/class_image.inc.php");
include_once("include/graph_map.inc.php");
//vito 2 feb 2009
//$encode_children = $_GET['ec'];

$self = whoami();
$ID = new ImageDevice();
$type = $ID->imageDevice();

if (!is_array($type)){
  print $type;
} else {
 //print_r( $type);
 // vito, 27 apr 2009, commentare riga seguente in debug
 header ("Content-type: image/".$type[0]); // type element 1 contain gif or png.
}

session_start();
//session_register('sess_node_map');
//session_register('sess_user_level');
//session_register('sess_template');
$sess_user_level = $_SESSION['sess_user_level'];
$sess_template = $_SESSION['sess_template'];
$sess_node_map = $_SESSION['sess_node_map'];
//print_r($_SESSION);


$children_ha = $sess_node_map;

// vito 2 feb 2009
//$children= urldecode($encode_children);
//$children_ha = unserialize($children);

$layout_template= $sess_template;

// Color definition. they will be read from DB
$background_R = 255;
$background_G = 255;
$background_B = 255;
$foreground_R = 0;
$foreground_G = 0;
$foreground_B = 0;

// Calcola la grandezza dell'immagine di sfondo.
$max_coordinate_ar = compute_maxFN($children_ha);
$max_X = $max_coordinate_ar[0];
$max_Y = $max_coordinate_ar[1];
//echo "$max_X e $max_Y";

//--------------------------------------------------------------------
// genera l'immagine che fa da sfondo all'immagine mappa
$im_dest = ImageCreate ($max_X, $max_Y)  or die ("Cannot Initialize new GD image stream");
$background_control = ImageColorAllocate ($im_dest, $background_R,$background_G,$background_B);


//--------------------------------------------------------------------
// Incolla le icone che rappresentano i nodi nell'immagine di sfondo.
// restituisce un array contenente le coordinate dei nodi.
// la chiave e' costituita dal id del nodo.


$position_node = copy_imageFN($children_ha, $im_dest, $max_X, $max_Y,$sess_user_level);

// vito, 27 apr 2009
//print_r($children_ha);
//echo "$max_X<br />$max_Y";
//print_r($im_dest);
//echo '<br />';
//print_r($background_control);
//echo '<br />';
//print_r($position_node);
//exit();


//--------------------------------------------------------------------
// Genera le frecce che indicano i link
$result = make_linkFN($children_ha, $im_dest, $position_node);

if ($result)
	show_image_FN($im_dest);
else
	print "GD Error!";

//session_unregister('node_map');
//session_unregister('sess_layout_template');
?>