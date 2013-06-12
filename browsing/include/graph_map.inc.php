<?php
/* FUNCTIONS */

function make_image_mapFN($children_ha,$user_level,$id_profile) {
    $sess_id_node = $_SESSION['sess_id_node'];
    $media_path = $GLOBALS['media_path'];
    $root_dir = $GLOBALS['root_dir'];
    $layout_template = $GLOBALS['layout_template'];
    $duplicate_dir_structure = $GLOBALS['duplicate_dir_structure'];
    $family = $GLOBALS['family'];
    $debug = $GLOBALS['debug'];



    /*
                $templates_dir = "browsing/templates";
                $temp = split('[/\]',$layout_template);
                $template_family = $temp[1];
                $template_img_dir = "$templates_dir/$template_family/img";
    */
    $function_group = "browsing"; //
    // $duplicate_dir_structure =  $GLOBALS['duplicate_dir_structure'];
    if (!isset($duplicate_dir_structure))
        $duplicate_dir_structure=0; //default
    if (!$family)
        $family = ADA_TEMPLATE_FAMILY; //default

    if (!$duplicate_dir_structure) { //0
        $templates_dir = "$function_group/templates/".$family;
    }        else {
        $templates_dir = "layout/$family";
    }
    $template_img_dir = "$templates_dir/img";

    $src_x = 0;
    $src_y = 0;
    $image_map = "<map name=\"Map\">\n";
    foreach ($children_ha as $child) {
        //mydebug(__LINE__,__FILE__,$child);
        $node_type = $child['type_child'];
        $node_level = $child['level_child'];
        $position_child = $child['position_child'];
        $id_child = $child['id_child'];
        $name = $child['name_child'];

        $tmp_icon_child = $child['icon_child'];
        if (stristr($tmp_icon_child,'/')) {
            if (file_exists($tmp_icon_child)) {
                $icon_child = $tmp_icon_child; // icon has an absolute path attached
            } elseif (file_exists($root_dir.$tmp_icon_child)) {
                $icon_child = $root_dir.$tmp_icon_child; // icon has a relative path attached
            } else {
                $icon_child = "$root_dir/$template_img_dir/$tmp_icon_child";
            }
        } else {
            $icon_child = "$root_dir/$template_img_dir/$tmp_icon_child";
        }
        //mydebug(__LINE__,__FILE__,$icon_child);
        // $icon_child = $template_img_dir . $child['icon_child'];
        // Get image dimensions
        $size_src = GetImageSize ($icon_child);
        $height_src=$size_src[1];
        $width_src=$size_src[0];
        $x1 = $position_child[0];
        $y1 = $position_child[1];
        // $x2 = $position_child[2];
        // $y2 = $position_child[3];
        $x2 = $x1 + $width_src;
        $y2 = $y1 + $height_src;

        if ($node_type == ADA_GROUP_TYPE) { // group
            $program = "map.php";
        } elseif ($node_type == ADA_LEAF_TYPE) {
            $program = "view.php";
        }
        if (isset($program)) {
            if ($user_level<$node_level) {
                $image_map .= "<area shape=\"rect\" coords=\"$x1,$y1,$x2,$y2\" alt=\"$name\">\n";
            } else {
                $image_map .= "<area shape=\"rect\" coords=\"$x1,$y1,$x2,$y2\" href=\"" . $program . "?id_node=$id_child\" alt=\"$name\">\n";
            }
        }
    }
    $image_map .= "</map>\n";
    return $image_map;
}

/*-------------------------------------------------------------------------*/
/* calcola le coordinate massime per la generazione dell'immagine di fondo */
function compute_maxFN($children_ha) {
    $max_X = 0;
    $max_Y = 0;
    foreach ($children_ha as $val) {
        $coordinate_ar = $val['position_child'];
//                mydebug(__LINE__,__FILE__,$coordinate_ar);

        $icon_child = $val['icon_child'];
        // Get image dimensions
        if (!empty($icon_child)) {
            // $size_src = GetImageSize($icon_child);
            // $height_src=$size_src[0];
            // $width_src=$size_src[1];
            // if (($coordinate_ar[0] + $height_src) > $max_X) {
            if ($coordinate_ar[2] > $max_X) {
                $max_X = ($coordinate_ar[2] + 50);
            }
            // if (($coordinate_ar[1] + $width_src) > $max_Y) {
            if ($coordinate_ar[3]  > $max_Y) {
                $max_Y = ($coordinate_ar[3] + 50);
            }
        }
    }
    $max_coordinate_ar = array($max_X,$max_Y);
    return $max_coordinate_ar;
}

/*-------------------------------------------------------------------------*/
/* Incolla le icone dei nodi all'interno dell'immagine generale            */
/*-------------------------------------------------------------------------*/
function copy_imageFN($children_ha, $im_dest, $max_X, $max_Y,$user_level) {
    $background_R = $GLOBALS['background_R'];
    $background_G = $GLOBALS['background_G'];
    $background_B = $GLOBALS['background_B'];
    $foreground_R = $GLOBALS['foreground_R '];
    $foreground_G = $GLOBALS['foreground_R'];
    $foreground_B = $GLOBALS['foreground_R '];
    $root_dir = $GLOBALS['root_dir'];
    $layout_template = $GLOBALS['layout_template'];
    $duplicate_dir_structure = $GLOBALS['duplicate_dir_structure'];
    $family = $GLOBALS['family'];
    $debug = $GLOBALS['debug'];

    $src_x = 0;
    $src_y = 0;

    $position_node = array();
    $function_group = "browsing"; //
// $duplicate_dir_structure =  $GLOBALS['duplicate_dir_structure'];
    if (!isset($duplicate_dir_structure))
        $duplicate_dir_structure=0; //default
    if (!$family)
        $family = ADA_TEMPLATE_FAMILY; //default

    if (!$duplicate_dir_structure) { //0
        $templates_dir = "$function_group/templates/".$family;
    }	else {
        $templates_dir = "templates/$function_group/".$family;
    }
// $templates_dir = "browsing/templates";
//echo $templates_dir;
//$temp = split('[/\]',$layout_template);
//$template_family = $temp[1];
//$template_img_dir = "$templates_dir/$template_family/img";
    $template_img_dir = "$templates_dir/img";
$template_img_dir = 'layout/standard/img';
# $background_color = ImageColorAllocate ($im, 0, 0, 0);
# $text_color = ImageColorAllocate ($im_dest, 233, 14, 91);
// $text_color = ImageColorAllocate ($im_dest, 255, 255, 255);
    $text_color = ImageColorAllocate ($im_dest, $foreground_R, $foreground_G, $foreground_B);

//mydebug(__LINE__,__FILE__,$im_dest);

    foreach ($children_ha as $child) {
        //mydebug(__LINE__,__FILE__,$child);
        $node_type = $child['type_child'];
        $position_child_ar = $child['position_child'];
        $name_child = $child['name_child'];
        $node_level = $child['level_child'];
        $id_child = $child['id_child'];
        $tmp_icon_child = $child['icon_child'];
//	mydebug(__LINE__,__FILE__,$tmp_icon_child);


        if ($user_level<$node_level) {
            $icon_child = "$root_dir/$template_img_dir/_nododis.png";
        } elseif ($node_type == ADA_NOTE_TYPE) { // notes aren't shown in maps !
            $icon_child = "$root_dir/$template_img_dir/_nota.png";
        } else {
            if (stristr($tmp_icon_child,'/')) {
                if (file_exists($tmp_icon_child)) {
                    $icon_child = $tmp_icon_child; // icon has an absolute path attached
                } elseif (file_exists($root_dir.$tmp_icon_child)) {
                    $icon_child = $root_dir.$tmp_icon_child; // icon has a relative path attached
                } else {
                    $icon_child = "$root_dir/$template_img_dir/$tmp_icon_child";
                }
            } else {
                $icon_child = "$root_dir/$template_img_dir/$tmp_icon_child";
            }
        }

        //mydebug(__LINE__,__FILE__,$icon_child);
        //echo "<img src=$icon_child>";

        if (!empty($icon_child)) {
            $id = new ImageDevice();
            if (empty($id->error) AND (file_exists($icon_child))) {
                // Get image dimensions
                $size_src = $id->GetImageSizeX($icon_child);
                // mydebug(__LINE__,__FILE__,$size_src);

                $height_src=$size_src[1];
                $width_src=$size_src[0];
                $dest_x = $position_child_ar[0];
                $dest_y = $position_child_ar[1];

                // Necessari per ridimensionamento immagine
                $height_dest=$position_child_ar[3] - $position_child_ar[1];
                $width_dest=$position_child_ar[2] - $position_child_ar[0];
                // mydebug(__LINE__,__FILE__,$position_child_ar);
                //

                // $extension = $id->type;

                $im_src = $id->imagecreateFromX($icon_child);
                mydebug(__LINE__,__FILE__,$id->error);
// Versione immagini ridimensionate.
//                      $im_result =  ImageCopyResized ($im_dest, $im_src, $dest_x, $dest_y, $src_x, $src_y, $width_dest, $height_dest, $width_src, $height_src);
// Versione che non ridimensiona
                $im_result =  ImageCopy ($im_dest, $im_src, $dest_x, $dest_y, $src_x, $src_y, $width_src, $height_src);
                ImageDestroy($im_src);

            }
            $bounds = $dest_x . "," . $dest_y . "," . ($dest_x + $width_src) . "," . ($dest_y + $height_src + 10);
            $bounds_ar = explode(",",$bounds);
            $control = array_push($position_node, array ('id_node'=>$id_child, 'bounds'=>$bounds));

            ImageString ($im_dest, 1, $dest_x, ($dest_y + $height_src + 10),  $name_child, $text_color);
        }
    }
    // mydebug(__LINE__,__FILE__,$position_node);
    return $position_node; # restituisce un array contenente le posizioni dei nodi figli
}

/*----------------------------------------------------*/
/* Genera le linee indicanti i collegamenti tra i nodi*/
function make_linkFN($children_ha, $im_dest, $position_node) {
    $background_R = $GLOBALS['background_R'];
    $background_G = $GLOBALS['background_G'];
    $background_B = $GLOBALS['background_B'];
    $foreground_R = $GLOBALS['foreground_R'];
    $foreground_G = $GLOBALS['foreground_R'];
    $foreground_B = $GLOBALS['foreground_R '];
    $root_dir = $GLOBALS['root_dir'];
    $layout_template = $GLOBALS['layout_template'];
    $duplicate_dir_structure = $GLOBALS['duplicate_dir_structure'];
    $family = $GLOBALS['family'];
    $debug = $GLOBALS['debug'];

    $src_x = 0;
    $src_y = 0;
    $text_color = ImageColorAllocate ($im_dest, $foreground_R, $foreground_G, $foreground_B);
    $node_from_found = 0;
    $node_to_found = 0;

    foreach ($children_ha as $child) {
        $id_node_from = $child['id_child'];
        $linked = $child['linked'];
        if (!empty($linked)) {
            foreach ($linked as $link) {

                $id_node_to = $link['id_node_to'];
                mydebug(__LINE__,__FILE__,array('id_node_to'=>$id_node_to,'id_node_from'=>$id_node_from));
                foreach ($position_node as $pos) {
                    $id_node = $pos['id_node'];
                    mydebug(__LINE__,__FILE__,array('id_node_from'=>$id_node_from,'id_node_to'=>$id_node_to,'id_node'=>$id_node,'trovato'=>$node_from_found));
                    if (($id_node == $id_node_from) and (!$node_from_found)) { // get position of node from
                        mydebug(__LINE__,__FILE__,$id_node);
                        $bounds = $pos['bounds'];
                        $bounds = explode(",", $bounds);
                        $xa1 = $bounds[0];
                        $ya1 = $bounds[1]; // left top corner

                        $xa4 = $bounds[2];
                        $ya4 = $bounds[3]; // right bottom corner

                        $xa2 = $xa4;
                        $ya2 = $ya1; // right top corner

                        $xa3 = $xa1;
                        $ya3 = $ya4; // left bottom corner

                        $node_from_found = 1;
                    } elseif (($id_node == $id_node_to) and (!$node_to_found)) { // get position of node to
                        $node_to_found = 1;
                        $bounds = $pos['bounds'];
                        $bounds = explode(",", $bounds);
                        $xb1 = $bounds[0];
                        $yb1 = $bounds[1]; // left top corner

                        $xb4 = $bounds[2];
                        $yb4 = $bounds[3]; // right bottom corner

                        $xb2 = $xb4;
                        $yb2 = $yb1; // right top corner

                        $xb3 = $xb1;
                        $yb3 = $yb4; // left bottom corner
                    }
                }

                if (($node_from_found) and ($node_to_found)) {
                    $node_from_found = 0;
                    $node_to_found = 0;

                    // Calcola la distanza tra i punti
                    $min_dist = 10000;
                    $min_array = 0;
                    $distanza_ar = array ();
                    $coordinate_ar = array ();


                    for ($i = 1; $i <= 4; $i++) {
                        $XA = "xa".$i;
                        $YA = "ya".$i;
                        for ($o = 1; $o <= 4; $o++) {
                            $XB = "xb".$o;
                            $YB = "yb".$o;
                            $distanza_ar[] = round(sqrt(abs(pow(($$XA - $$XB),2) + pow(($$YA - $$YB),2))));
                            $coordinate_ar[] = $$XA . "," . $$YA . "," . $$XB . "," . $$YB;
                        }
                    }

                    for ($i = 0; $i <= 15; $i++) {
                        if ($distanza_ar[$i] < $min_dist) {
                            $min_array = $i;
                            $min_dist = $distanza_ar[$i];
                            # echo "Minore = $min_array, distanza Minore = $min_dist <BR>";
                        }
                    }
                    $coordinate = $coordinate_ar[$min_array];
                    $coord_final = explode("," , $coordinate);
                    # echo "$coordinate<BR><BR>";
                    $x_from = $coord_final[0];
                    $y_from = $coord_final[1];

                    $x_to = $coord_final[2];
                    $y_to = $coord_final[3];
                    imagedashedline ($im_dest, $x_from, $y_from, $x_to, $y_to, $text_color);
                }
            }
        }
    }
    return true;
}

/*----------------------------------------------*/
/* Mostra l'immagine della mappa */
function show_image_FN($im_dest) {
    $id_img = new ImageDevice();
    if (empty($id_img->error)) {
        $id_img->ImageX($im_dest);
    }
}
?>