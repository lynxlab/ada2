<?php
/**
 * EDITNODE FUNCTIONS
 * 
 * @package		
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link					
 * @version		0.1
 */

function delete_nodeFN($id_node,$id_course,$action) {

  $http_root_dir = $GLOBALS['http_root_dir'];
  $self          = $GLOBALS['self'];
  $dh = $GLOBALS['dh'];
  $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
  $sess_id_node            = $_SESSION['sess_id_node'];
 
 
  /*
   * get object node
   */ 
  $nodeObj = read_node_from_DB($id_node);
  if (is_object($nodeObj) && (!AMA_DataHandler::isError($nodeObj))) {
    //$sess_id_node = $nodeObj->id;
    $name = $nodeObj->name;
    $title = $nodeObj->title;
    $text = $nodeObj->text;
    $type = $nodeObj->type;
    $parent_id = $nodeObj->parent_id;
    // $path = $nodeObj->findPathFN($target);
    $order = $nodeObj->ordine;
    $version = $nodeObj->version;
    $color = $nodeObj->color;
    $bgcolor=$nodeObj->bgcolor;
    $correctness = $nodeObj->correctness;
    $copyright=$nodeObj->copyright;
    $n_contacts=$nodeObj->contacts;
    $icon=$nodeObj->icon;
    $id_node_author=$nodeObj->author;
    $creation_date = $nodeObj->creation_date;

    $node_childrenAr = $dh->get_node_children($id_node,$sess_id_course_instance);
    
    $has_children = is_array($node_childrenAr);//(!is_object($node_childrenAr));
    $is_root_node = (strpos($nodeObj->id, '_0') !== false);
    if ( !$is_root_node && ((!$has_children) || ($type==ADA_LEAF_TYPE))) {
      /* si possono eliminare:
       *           i nodi semplici
       *     i gruppi , ma solo se vuoti
       */
      $head_form= "<b>". translateFN('Eliminare questo nodo?') ."</b><hr><br>\n";
      // building form
      $invia = translateFN('Elimina nodo');

      $fields["add"][]="course";
      $names["add"][]="";
      $edittypes["add"][]="hidden";
      $necessary["add"][]="";
      $values["add"][]=$id_course;
      $options["add"][]="";
      $maxsize["add"][]="";
      // nodo
      $fields["add"][]="id_node";
      $names["add"][]="";
      $edittypes["add"][]="hidden";
      $necessary["add"][]="";
      $values["add"][]=$id_node;
      $options["add"][]="";
      $maxsize["add"][]="";

      // nodo genitore
      $fields["add"][]="parent_id";
      $names["add"][]="";
      $edittypes["add"][]="hidden";
      $necessary["add"][]="";
      $values["add"][]=$parent_id;
      $options["add"][]="";
      $maxsize["add"][]="";
      // creazione del form
      $form = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$action.".php?op=delete","add",false,true,$invia);

      //
    } else {
      $msg = urlencode(translateFN("Attenzione: non &egrave; possibile eliminare nodi superiori"));
      header("Location: " . $http_root_dir . "/browsing/view.php?msg=$msg&id_node=$sess_id_node");
    }

    $menu= "<a href=$http_root_dir/browsing/view.php?id_node=$sess_id_node>".translateFN('Torna')."</a>";
    $head_form.= "<table>\n";
    $head_form.=  "<tr><td><b>". translateFN('ID del nodo') ."</b></td><td> $id_node</td></tr>\n";
    $head_form.=  "<tr><td><b>". translateFN('Titolo') ."</b></td><td> $name</td></tr>\n";
    $head_form.= "<tr><td><b>". translateFN('Tipo:')." </b></td><td> $type</td></tr>\n";
    //    $head_form.= translateFN('Ordine:')." <b>$order</b><br>\n";
    //    $head_form.= translateFN('Versione:')."<b>$version</b><br>\n";
    //    $head_form.= translateFN('Colore:')."<b>$color</b><br>\n";
    //    $head_form.= translateFN('Colore fondo:')."<b>$bgcolor</b><br>\n";
    //    $head_form.= translateFN('Correttezza:')."<b>$correctness</b><br>\n";
    //    $head_form.= translateFN('Copyright:')."<b$copyright></b><br>\n";
    //   $head_form.= "<img src=$http_root_dir/browsing/templates/default/img/$icon><br>\n";
    $head_form.= "<tr><td><b>".translateFN('Data di creazione:')."</b></td><td>$creation_date</td></tr>\n";
    $head_form.="</table><br>\n";
    $head_form.=  "<b>Testo:</b><p><cit>".strip_tags($text,"<br>")."</cit></p><hr>";

    $data['head_form'] = $head_form;
    $data['menu'] = $menu;
    $data['form'] = $form;
    return $data;
  } 
  else {
    $errObj = new ADA_Error($nodeObj, translateFN('Nodo non trovato, impossibile proseguire.'));
    // FIXME: eliminare il return?
    return  $errObj;
  }
}

function copy_nodeFN($id_node,$id_course,$action){
  //global $http_root_dir,$sess_id_node,$self;
  $self = $GLOBALS['self'];
  $sess_id_node = $_SESSION['sess_id_node'];
  $http_root_dir = $GLOBALS['http_root_dir'];

  // get object node
  $nodeObj = read_node_from_DB($id_node);
  // per il momento se non e' un oggetto vuol dire che c'e' un errore
  // invece deve COMUQNQUE ritornare un oggetto, ma di tipo diverso: Errore
  if (is_object($nodeObj) && (!AMA_DataHandler::isError($nodeObj))) {
    $sess_id_node = $nodeObj->id;
    $name = $nodeObj->name;
    $title = $nodeObj->title;
    $text = $nodeObj->text;
    $type = $nodeObj->type;
    $parent_id = $nodeObj->parent_id;
    // $path = $nodeObj->findPathFN($target);
    $order = $nodeObj->ordine;
    $version = $nodeObj->version;
    $color = $nodeObj->color;
    $bgcolor=$nodeObj->bgcolor;
    $correctness = $nodeObj->correctness;
    $copyright=$nodeObj->copyright;
    $n_contacts=$nodeObj->contacts;
    $icon=$nodeObj->icon;
    $id_node_author=$nodeObj->author;
    $creation_date = $nodeObj->creation_date;

    if ($type==ADA_LEAF_TYPE){

      $head_form= "<b>". translateFN('Duplicare questo nodo?') ."</b><hr><br>\n";
      // building form
      $invia = translateFN('Duplica nodo');
      $fields["add"][]="new_id_node";
      $names["add"][]="Id del nuovo nodo";
      $edittypes["add"][]="text";
      $necessary["add"][]="";
      $values["add"][]=$node_id;
      $options["add"][]="";
      $maxsize["add"][]="";
      // creazione del form
      $form = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$self.".php","add",false,true,$invia);

      //
    } else {
      $head_form = "<b>". translateFN('Attenzione: non &egrave; possibile duplicare nodi di questo tipo') ."</b><hr><br>\n";
    }

    $menu = "<a href=$http_root_dir/browsing/view.php?id_node=$sess_id_node>".translateFN('Torna')."</a>";             $head_form.=  "<b>". translateFN('ID del nodo') ."</b> $id_node<br>\n";
    $head_form.=  "<b>". translateFN('Titolo') ."</b> $name<br>\n";
    $head_form.= translateFN('Tipo:')."<b>$type</b><br>\n";
    $head_form.= translateFN('Ordine:')."<b>$order</b><br>\n";
    $head_form.= translateFN('Versione:')."<b>$version</b><br>\n";
    $head_form.= translateFN('Colore:')."<b>$color</b><br>\n";
    $head_form.= translateFN('Colore fondo:')."<b>$bgcolor</b><br>\n";
    $head_form.= translateFN('Correttezza:')."<b>$correctness</b><br>\n";
    $head_form.= translateFN('Copyright:')."<b$copyright></b><br>\n";
    // $head_form.= "<img src=$http_root_dir/browsing/templates/default/img/$icon><br>\n";
    $head_form.= translateFN('Data di creazione:')."<b>$creation_date</b><br>\n";
    $head_form.=  "<p>$text</p><hr>";

    $data['head_form'] = $head_form;
    $data['menu'] = $menu;
    $data['form'] = $form;
    return $data;
  } 
  else {
    $errObj = new ADA_Error($nodeObj, translateFN('Nodo non trovato, impossibile proseguire.'));
    // FIXME: eliminare il return?
    return  $errObj;
  }

}

function preview_nodeFN($id_node,$id_course,$action){
  //global $http_root_dir,$sess_id_node,$self;
  //global $level,$order,$version,$correctness,$creation_date,$icon,$course,$name,$title,$type,$text;

  $self = $GLOBALS['self'];
  $sess_id_node = $_SESSION['sess_id_node'];
  $http_root_dir = $GLOBALS['http_root_dir'];

  $level = $GLOBALS['level'];
  $order = $GLOBALS['order'];
  $version = $GLOBALS['version'];
  $correctness = $GLOBALS['correctness'];
  $creation_date = $GLOBALS['creation_date'];
  $icon = $GLOBALS['icon'];
  $course = $GLOBALS['course'];
  $name = $GLOBALS['name'];
  $title = $GLOBALS['title'];
  $type = $GLOBALS['type'];
  $text = $GLOBALS['text'];
  $parent_id = $GLOBALS['parent_id'];
  $position = $GLOBALS['position'];


  $text = nl2br($text);
  $no_slashed_text = stripslashes($text);
  $no_slashed_title = stripslashes($title);


  if ($type==ADA_NOTE_TYPE) { //NOTE
    $invia = translateFN("Modifica nota");
  } else {
    $invia = translateFN("Modifica nodo");
    // Livello del nodo
    $fields["add"][]="level";
    $names["add"][]="";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$level;
    $options["add"][]="";
    $maxsize["add"][]=10;

    // Ordine del nodo
    $fields["add"][]="order";
    $names["add"][]="";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$order;
    $options["add"][]="";
    $maxsize["add"][]=10;

    // Versione del nodo
    $fields["add"][]="version";
    $names["add"][]="";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$version;
    $options["add"][]="";
    $maxsize["add"][]=10;

    // Correttezza del nodo
    $fields["add"][]="correctness";
    $names["add"][]="";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$correctness;
    $options["add"][]="";
    $maxsize["add"][]=10;

    // Data del nodo
    $fields["add"][]="creation_date";
    $names["add"][]="";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$creation_date;
    $options["add"][]="";
    $maxsize["add"][]=10;

    // Icona del nodo
    $fields["add"][]="icon";
    $names["add"][]="";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$icon;
    $options["add"][]="";
    $maxsize["add"][]=20;

  }

  $fields["add"][]="parent_id";
  $names["add"][]="";
  $edittypes["add"][]="hidden";
  $necessary["add"][]="";
  $values["add"][]=$parent_id;
  $options["add"][]="";
  $maxsize["add"][]="";

  $reset ="no";

  $fields["add"][]="course";
  $names["add"][]="";
  $edittypes["add"][]="hidden";
  $necessary["add"][]="";
  $values["add"][]=$course;
  $options["add"][]="";
  $maxsize["add"][]="";


  // Nome del nodo
  $fields["add"][]="name";
  $names["add"][]="";
  $edittypes["add"][]="hidden";
  $necessary["add"][]="";
  $values["add"][]=$name;
  $options["add"][]="";
  $maxsize["add"][]=80;

  // Keywords del nodo
  $fields["add"][]="title";
  $names["add"][]="";
  $edittypes["add"][]="hidden";
  $necessary["add"][]="";
  $values["add"][]=urlencode($title);
  $options["add"][]="";
  $maxsize["add"][]=80;

  // Testo del nodo
  $fields["add"][]="text";
  $names["add"][]="";
  $edittypes["add"][]="hidden";
  $necessary["add"][]="";
  $values["add"][]=urlencode($text);
  $options["add"][]="";
  $maxsize["add"][]="";



  // nodo
  $fields["add"][]="id_node";
  $names["add"][]="";
  $edittypes["add"][]="hidden";
  $necessary["add"][]="";
  $values["add"][]=$id_node;
  $options["add"][]="";
  $maxsize["add"][]=20;

  // tipo del nodo
  $fields["add"][]="type";
  $names["add"][]="";
  $edittypes["add"][]="hidden";
  $necessary["add"][]="";
  $values["add"][]=$type;
  $options["add"][]="";
  $maxsize["add"][]=20;

  $menu= "<b><a href=edit_node.php?op=edit&id_node=$id_node>".translateFN('Torna')."</a></b><br>\n";
  $head_form = "<p class=menuinterno><b>".translateFN('Anteprima delle modifiche:')."</b></p>\n";
  $head_form .=  "<b>". translateFN('Titolo') ."</b>: $name<br>\n";
  $head_form.=  "<b>". translateFN('Keywords') ."</b>: $no_slashed_title<br>\n";
  $head_form.=  "<p>$no_slashed_text</p><hr>";

  // creazione del form
  $form = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$action.".php?op=save","add",false,true,$invia,$reset);
  $data['head_form'] = $head_form;
  $data['menu'] = $menu;
  $data['form'] = $form;
  return $data;

}

function edit_nodeFN($id_node,$id_course,$action){

  $self = $GLOBALS['self'];
  $sess_id_node = $_SESSION['sess_id_node'];
  $sess_id_user = $_SESSION['sess_id_user'];
  $http_root_dir = $GLOBALS['http_root_dir'];
  $dh = $GLOBALS['dh'];
  // get object node
  $nodeObj = read_node_from_DB($id_node);
  if (is_object($nodeObj) && (!AMA_DataHandler::isError($nodeObj))) {
    $sess_id_node = $nodeObj->id;
    $name = $nodeObj->name;
    $title = $nodeObj->title;
    $text = $nodeObj->text;
    $type = $nodeObj->type;
    $level = $nodeObj->level;
    $parent_id = $nodeObj->parent_id;
    $position = $nodeObj->position;
    $order = $nodeObj->ordine;
    $version = $nodeObj->version;
    $color = $nodeObj->color;
    $bgcolor=$nodeObj->bgcolor;
    $correctness = $nodeObj->correctness;
    $copyright=$nodeObj->copyright;
    $n_contacts=$nodeObj->contacts;
    $icon=$nodeObj->icon;
    $id_node_author=$nodeObj->author;
    $creation_date = $nodeObj->creation_date;
    $id_instance = $nodeObj->id_instance;
     
    //get parent obj node [useful in several tasks]
    $nodeObjParent = read_node_from_DB($sess_id_node);
    if (is_object($nodeObj) && (!AMA_DataHandler::isError($nodeObj))) {
      $sess_id_nodeParent = $nodeObjParent->id;
      $nameParent = $nodeObjParent->name;
      $titleParent = $nodeObjParent->title;
      $textParent = $nodeObjParent->text;
      $typeParent = $nodeObjParent->type;
      $levelParent = $nodeObjParent->level;
      $parent_idParent = $nodeObjParent->parent_id;
      $positionParent = $nodeObjParent->position;
      $orderParent = $nodeObjParent->ordine;
      $versionParent = $nodeObjParent->version;
      $colorParent = $nodeObjParent->color;
      $bgcolorParent=$nodeObjParent->bgcolor;
      $correctnessParent = $nodeObjParent->correctness;
      $copyrightParent=$nodeObjParent->copyright;
      $n_contactsParent=$nodeObjParent->contacts;
      $iconParent=$nodeObjParent->icon;
      $id_node_authorParent=$nodeObjParent->author;
      $creation_dateParent = $nodeObjParent->creation_date;
      $id_instanceParent = $nodeObjParent->id_instance;
    }
    // building form
    $invia = translateFN("Anteprima");
    $reset ="";

    $sess_id_node = $id_node;

    switch ($type){

      case  ADA_NOTE_TYPE: //NOTE
        $label_nome = translateFN('Titolo della nota');
        $label_testo =  translateFN('Testo');
        $label_keywords =  translateFN('Keywords');
        $label_tipo = translateFN('Tipo'); // promote note->node !
        $type_field = "hidden"; // text
        $fields["add"][]="type";
        $names["add"][]="";
        $edittypes["add"][]=$type_field;
        $necessary["add"][]=$label_tipo;
        $values["add"][]=$type;
        $options["add"][]="";
        $maxsize["add"][]=40;
        break;

      case  ADA_PRIVATE_NOTE_TYPE: //NOTE
        $label_nome = translateFN('Titolo della nota personale');
        $label_testo =  translateFN('Testo');
        $label_keywords =  translateFN('Keywords');
        $label_tipo = translateFN('Tipo'); //promote private note-> forum note                         $label_tipo ="";
        $type_field = "hidden"; // text
        $fields["add"][]="type";
        $names["add"][]="";
        $edittypes["add"][]=$type_field;
        $necessary["add"][]=$label_tipo;
        $values["add"][]=$type;
        $options["add"][]="";
        $maxsize["add"][]=40;
        break;

      default:
        $type_field = "text";
        $label_nome = translateFN('Titolo');
        $label_testo =  translateFN('Testo');
        $label_tipo = translateFN('Tipo');
        $label_keywords =  translateFN('Keywords');
        $label_livello = translateFN('Livello');
        $label_ordine = translateFN('Ordine');
        $label_versione = translateFN('Versione');
        $label_correttezza = translateFN('Correttezza');
        $label_icona = translateFN('Icona');
        $label_data = translateFN('Data');
        $label_parent =translateFN('Gruppo');
        $label_position =translateFN('Posizione');
        // genitori  del nodo
        $nodesCourse=$dh->find_course_nodes_list(array('nome','tipo'),"ID_UTENTE = '$sess_id_user' AND TIPO in (0,1) AND  ID_NODO!=" . $id_node . " order by ID_NODO asc",$id_course);

        if(is_array($nodesCourse) && sizeof($nodesCourse) > 0){
          foreach ($nodesCourse as $value) {
            // adding type of node to select label
            switch($value[2]) {
              case ADA_GROUP_TYPE:
                $tipo = translateFN('Gruppo');
                break;
                
              case ADA_LEAF_TYPE:
                $tipo = translateFN('Nodo');
                break;
            }
            $nodesCourse_label[]=$value[0]." ".$value[1]." (".$tipo.")";
            $nodesCourse_value[]=$value[0];
          }//fine foreach sui nodi
          $nodesCourse_label = ":".implode(":", $nodesCourse_label);
          $nodesCourse_value = implode(":", $nodesCourse_value);
           
          $fields["add"][]="parent_id";
          $names["add"][]=translateFN('Nodo superiore').$nodesCourse_label;
          $edittypes["add"][]="select";
          $necessary["add"][]="";
          $values["add"][]=$nodesCourse_value;
          $options["add"][]="";
          $maxsize["add"][]=10;
        }

        // Livello del nodo
        $fields["add"][]="level";
        $names["add"][]=$label_livello;
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$level;
        $options["add"][]="";
        $maxsize["add"][]=10;

        // Ordine del nodo
        $fields["add"][]="order";
        $names["add"][]=$label_ordine;
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$order;
        $options["add"][]="";
        $maxsize["add"][]=10;

        // Versione del nodo
        $fields["add"][]="version";
        $names["add"][]=$label_versione;
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$version + 1;
        $options["add"][]="";
        $maxsize["add"][]=10;

        // Correttezza del nodo ??? ONLY IF PARENT IS AN EXERCISE
        //mio dubbio [Raffaele], la soluzione e' un po' debole perche' nel caso variano o si estendono gli e esercizi in config bisogna anche individuare la variazion da apportare nel resto del codic
        if(
        in_array($typeParent,
        array(ADA_STANDARD_EXERCISE_TYPE,
        ADA_OPEN_MANUAL_EXERCISE_TYPE,
        ADA_OPEN_AUTOMATIC_EXERCISE_TYPE,
        ADA_CLOZE_EXERCISE_TYPE,
        ADA_OPEN_UPLOAD_EXERCISE_TYPE)
        )
        )
        {
          $fields["add"][]="correctness";
          $names["add"][]=$label_correttezza;
          $edittypes["add"][]="text";
          $necessary["add"][]="";
          $values["add"][]=$correctness;
          $options["add"][]="";
          $maxsize["add"][]=10;
        }
        // Data del nodo
        $fields["add"][]="creation_date";
        $names["add"][]=$label_data;
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$creation_date;
        $options["add"][]="";
        $maxsize["add"][]=10;

        // Icona del nodo
        $fields["add"][]="icon";
        $names["add"][]=$label_icona;
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$icon;
        $options["add"][]="";
        $maxsize["add"][]=20;

        // tipo del nodo
        $fields["add"][]="type";
        $names["add"][]=$label_tipo;
        $edittypes["add"][]=$type_field;
        $necessary["add"][]="";
        $values["add"][]=$type;
        $options["add"][]="";
        $maxsize["add"][]=40;
         
        // Posizione del nodo
        $desc_pos = translateFN('Posizione');
        $fields["add"][]="position";
        $names["add"][]=$desc_pos;
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=implode(",",$position); //"100,100,200,200";
        $options["add"][]="";
        $maxsize["add"][]=20;

        // inserisci link interno
        $desc_pos = translateFN('Gestione Link Interno al Nodo');
        $fields["add"][]="";
        $names["add"][]=$desc_pos;
        $edittypes["add"][]="link";
        $necessary["add"][]="";
        $values["add"][]="add_link.php?link_type=0&id_nodefrom=".$id_node."&id_course=".$id_course;
        $options["add"][]=array('target'=>'_blank');
        $maxsize["add"][]="";

        // inserisci link esterno
        $desc_pos = translateFN(' Gestione Link Esterno al Nodo');
        $fields["add"][]="";
        $names["add"][]=$desc_pos;
        $edittypes["add"][]="link";
        $necessary["add"][]="";
        $values["add"][]="add_link.php?link_type=1&id_nodefrom=".$id_node."&id_course=".$id_course;
        $options["add"][]=array('target'=>'_blank');
        $maxsize["add"][]="";

        // inserisci multimedia
        $desc_pos = translateFN('Gestione Multimedia');
        $fields["add"][]="";
        $names["add"][]=$desc_pos;
        $edittypes["add"][]="link";
        $necessary["add"][]="";
        $values["add"][]="upload.php?id_node=".$id_node;
        $options["add"][]=array('target'=>'_blank');
        $maxsize["add"][]="";
    }
    // Course
    $fields["add"][]="course";
    $names["add"][]="";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$id_course;
    $options["add"][]="";
    $maxsize["add"][]="";

    // Nome del nodo
    $fields["add"][]="name";
    $names["add"][]=$label_nome;
    $edittypes["add"][]="text";
    $necessary["add"][]="";
    $values["add"][]=$name;
    $options["add"][]="";
    $maxsize["add"][]=80;

    // Keywords del nodo
    $fields["add"][]="title";
    $names["add"][]=$label_keywords;
    $edittypes["add"][]="text";
    $necessary["add"][]="";
    $values["add"][]=$title;
    $options["add"][]="";
    $maxsize["add"][]=80;

    // Testo del nodo
    $fields["add"][]="text";
    $names["add"][]=$label_testo;
    $edittypes["add"][]="textarea";
    $necessary["add"][]="";
    $values["add"][]=$text;
    $options["add"][]="";
    $maxsize["add"][]="";
    	
    	

    // nodo
    $fields["add"][]="id_node";
    $names["add"][]="";
    $edittypes["add"][]="hidden";
    $necessary["add"][]="";
    $values["add"][]=$id_node;
    $options["add"][]="";
    $maxsize["add"][]=20;


    $head_form="";
    $menu = "<a href=$http_root_dir/browsing/view.php?id_node=$sess_id_node>".translateFN('Torna')."</a>";
    // creazione del form
    $form = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$action.".php?op=preview","add",false,true,$invia,$reset);

    $data['head_form'] = $head_form;
    $data['menu'] = $menu;
    $data['form'] = $form;
    return $data;
  } 
  else {
    $errObj = new ADA_Error($nodeObj, translateFN('Nodo non trovato, impossibile proseguire.'));
    // FIXME: eliminare il return?
    return  $errObj;
  }
}

// vito

function getNodeData( $id_node ) {
  $nodeObj = read_node_from_DB($id_node);
  if ( AMA_DataHandler::isError($nodeObj)) return $nodeObj;

  $node_data = array(
             'id' => $id_node,
             'name' => $nodeObj->name,
             'title' => $nodeObj->title,
             'text' => $nodeObj->text,
             'type' => $nodeObj->type,
             'level' => $nodeObj->level,
             'parent_id' => $nodeObj->parent_id,
             'position' => $nodeObj->position,
             'order' => $nodeObj->ordine,
             'version' => $nodeObj->version,
             'color' => $nodeObj->color,
             'bgcolor' => $nodeObj->bgcolor,
             'correctness' => $nodeObj->correctness,
             'copyright' => $nodeObj->copyright,
             'n_contacts' => $nodeObj->contacts,
             'icon' => $nodeObj->icon,
             'id_node_author' => $nodeObj->author['id'], //vito, 28 nov 2008 gets only the author id
             'creation_date' => $nodeObj->creation_date,
             'id_instance' => $nodeObj->id_instance
  );
  if ($nodeObj->type == ADA_LEAF_WORD_TYPE OR $nodeObj->type == ADA_GROUP_WORD_TYPE) {
      $node_data['hyphenation'] = $nodeObj->hyphenation;
      $node_data['grammar'] = $nodeObj->grammar;
      $node_data['semantic'] = $nodeObj->semantic;
      $node_data['notes'] = $nodeObj->notes;
      $node_data['examples'] = $nodeObj->examples;
      $node_data['ex_language'] = $nodeObj->language;
  }
  return $node_data;
   
}

function getNodeDataFromPost( $post_data = array() ) {
  $node_data = array(
   'id' => $post_data['id'],
   'name' => $post_data['name'],
   'title' => $post_data['title'],
   'text' => $post_data['ADACode'],
   'type' => $post_data['type'],
   'level' => $post_data['level'],
   'parent_id' => $post_data['parent_id'], 
   'position' => $post_data['position'],
   'order' => $post_data['order'],
   'version' => $post_data['version'],
   'color' => $post_data['color'],
   'bg_color' => $post_data['bg_color'],
   'correctness' => $post_data['correctness'],
   'copyright' => $post_data['copyright'],
   'n_contacts' => $post_data['n_contacts'], 
   'icon' => $post_data['icon'],
   'id_node_author' => $post_data['id_node_author'],
   'creation_date' => $post_data['creation_date'],
   'DataFCKeditor' => $post_data['DataFCKeditor'],
   'DataFCK_hyphen' => $post_data['DataFCK_hyphen'],
   'hyphenation' =>  $post_data['ADACodeHyphen'],
   'DataFCK_grammar' => $post_data['DataFCK_grammar'],
   'grammar' => $post_data['ADACodeGrammar'],
   'DataFCK_semantic' => $post_data['DataFCK_semantic'],
   'semantic' => $post_data['ADACodeSemantic'],
   'DataFCK_notes' => $post_data['DataFCK_notes'],
   'notes' => $post_data['ADACodeNotes'],
   'DataFCK_examples' => $post_data['DataFCK_examples'],
   'examples' => $post_data['ADACodeExamples'],
   'DataFCK_exlanguage' => $post_data['DataFCK_exlanguage'],
   'exlanguage' => $post_data['ADACodeExlanguage'],
  	// @author giorgio 26/apr/2013
   'forcecreationupdate' => $post_data['forcecreationupdate']	
  );

  return $node_data;
}
?>