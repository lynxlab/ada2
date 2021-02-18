<?php
/**
 * Course, Course_instance and Student_class classes
 *
 * @package		model
 * @author		Stefano Penge <steve@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		courses_classes
 * @version		0.1
 */

class Course_Old {
    var $id;

    var $nome;
    var $titolo;
    var $id_autore;
    var $id_layout;
    var $descr;
    var $d_create;
    var $d_publish;
    var $id_nodo_iniziale;
    var $id_nodo_toc;
    var $media_path;

    var $full;
    var $error_msg;
    var $template_family;
    var $static_mode;
    var $crediti;
    var $duration_hours;
    var $service_level;

    public function __construct($id_course) {
        $dh = $GLOBALS['dh'];

        $dataHa = $dh->get_course($id_course);
        if (AMA_DataHandler::isError($dataHa) || (!is_array($dataHa))) {
            $this->full = 0;
        }
        else {
            if (!empty($dataHa['nome'])) {
                $this->full = 1;
                /* fare attenzione ad eventuali modifiche ai nomi delle colonne
         * nella tabella modello_corso.
         * devono coincidere con i nomi degli attributi di questa classe.
                */
                foreach ($dataHa as $key=>$value) {
                    $this->$key = $value;
                }

                $this->id  = $id_course;
                $id_layout = $this->id_layout;
                // Table Layout is not available.
                // $layoutHa  = $dh->_get_layout($id_layout);
                // $this->template_family = $layoutHa['family'];
            }
        }
    }

    public function getId() {
        return $this->id;
    }
    /* RIASSUNTO:
   main_indexFN: mostra nodi e gruppi, per studente (no autore, tutor e admin)
   explode_nodesFN : ricorsiva, chiamata per default e se $order=struct
   explode_nodes_iterativeFN : iterativa, chiamata se $order=alfa

   se hide_visits=1 mostrano anche le visite dello studente

   class_indexFN: mostra nodi e gruppi,per tutor e autore  (no studente e admin)
   class_explode_nodesFN : ricorsiva, chiamata per default e se $order=struct
   class_explode_nodes_iterativeFN : iterativa, chiamata se $order=alfa

   se hide_visits=1 mostrano anche le visite della classe (tutor) o di tutti (autore)

   forum_main_indexFN: mostra  solo note, per studente, tutor  (no admin e autore)
   forum_explode_nodesFN : ricorsiva, chiamata se $order=struct
   forum_explode_nodes_iterativeFN : iterativa, chiamata per default e se $order=chrono

   *se hide_visits=1 mostrano anche le visite della classe (tutor)
    */



    function main_indexFN($id_toc='',$depth=1,$user_level=1,$user_history='',$user_type=AMA_TYPE_STUDENT,$order='struct',$expand=0,$mode='standard') {
        $dh = $GLOBALS['dh'];
        $debug = $GLOBALS['debug'];
        $sess_id_course = $_SESSION['sess_id_course'];

        if (empty($id_toc))
            $id_toc = $sess_id_course."_".ADA_DEFAULT_NODE;
        $base = new Node($id_toc,0);  // da dove parte
        $alt = translateFN("Gruppo principale");
        $icon = "_gruppo.png";
        $index = "<p>";
        if ($order=='struct') {
            $index .= "<img name=\"nodo\" alt=$alt src=\"img/$icon\"> <a href=view.php?id_node=".$id_toc.">".translateFN("Principale")."</a>";
        }
        $index .= $this->tabled_explode_nodesFN(1,$user_level,$id_toc,$user_type,$order,$expand,$mode);
        $index .= "</p>";
        return $index;
    }


    function tabled_explode_nodesFN($depth,$user_level,$id_parent,$id_profile,$order,$expand,$mode) {
        $lObj = new Ilist();
        if ($order=='alfa') {
            $data =  $this->explode_nodes_iterativeFN($depth,$user_level,$id_parent,$id_profile,$order,$expand,$mode);
            $lObj->initList('1','1',1);
        }   else {    // = 'r'

            $data =  $this->explode_nodesFN($depth,$user_level,$id_parent,$id_profile,$order,$expand,$mode);
            $lObj->initList(0,'',1);
        }
        $lObj->setList($data);
        $tabled_index = $lObj->getList();

        return $tabled_index;
    }



    function explode_nodes_iterativeFN($depth,$user_level,$id_parent,$id_profile,$order,$expand,$mode) {
        // returns an Array
        // only students
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_user = $_SESSION['sess_id_user'];
        $sess_id_course = $_SESSION['sess_id_course'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];

        $dh = $GLOBALS['dh'];
        $debug = $GLOBALS['debug'];
        $id_course  = $GLOBALS['id_course'];
        $hide_visits = $GLOBALS['hide_visits'];
        $with_icons = $GLOBALS['with_icons'];
        $with_dates = $GLOBALS['with_dates'];
        $with_authors = $GLOBALS['with_authors'];


        $tot_notes = 0;
        $childnumber = 0;
        $out_fields_ar = array('nome','tipo');
        $clause = "";
        $childrenAr = $dh->find_course_nodes_list($out_fields_ar,$clause,$sess_id_course);
        $childrenAr = masort($childrenAr, 1); // il campo 1 �il nome del nodo
        $k = 0;
        $indexAr = array();
        foreach ($childrenAr as $nodeHa) {
            $k++;
            $index_item = "";
            $id_child = $nodeHa[0];
            if (!empty($id_child)) {
                $childnumber++;
                $child_dataHa = $dh->get_node_info($id_child);
                $node_instance = $child_dataHa['instance'];
                $id_node_parent = $child_dataHa['parent_id'];
                $creation_date = $child_dataHa['creation_date'];
                $version = $child_dataHa['version'];
                $node_authorHa =   $child_dataHa['author'];
                $node_author_name = $node_authorHa['nome'];
                $node_author_surname = $node_authorHa['cognome'];
                $parent_dataHa = $dh->get_node_info($id_node_parent);
                if (($id_node_parent==NULL) OR (!is_array($parent_dataHa))) // map
                    continue;
                $parent_type = $parent_dataHa['type'];
                if ($parent_type >= ADA_STANDARD_EXERCISE_TYPE)
                    $node_type = 'answer';
                else
                    $node_type = $child_dataHa['type'];

                switch ($node_type) {
                    case 'answer':
                        break;
                    case ADA_LEAF_TYPE:    //node
                        if ($child_dataHa['level']<=$user_level) {
                            $alt = translateFN("Nodo inferiore");
                            $icon = "_nodo.png";
                            if (!isset($hide_visits) OR $hide_visits==0) {
                                $visit_count  = ADAUser::is_visited_by_userFN($id_child,$sess_id_course_instance,$sess_id_user);
                            }
                            if  (empty($visit_count)) {
                                if ($with_icons)
                                    $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\"> ";
                                $index_item .="<b><a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a></b>";
                            } else {
                                if ($with_icons)
                                    $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\"> ";
                                $index_item .="<a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a>
                                   ($visit_count)";
                            }

                            // is user visiting this node?
                            if ($id_child==$sess_id_node) {
                                if ($with_icons)
                                    $index_item.= "<img  name=\"attuale\" alt=\"attuale\" src=\"img/_anchor.png\">";
                            }
                            // has user bookmarked this node?
                            $id_bk = Bookmark::is_node_bookmarkedFN($sess_id_user,$id_child);
                            if ($id_bk)
                                if ($with_icons)
                                    $index_item .= "<a href=\"bookmarks.php?op=zoom&id_bk=$id_bk\"><img name=\"bookmark\" alt=\"bookmark\" src=\"img/check.png\"  border=\"0\"></a>";

                        } else {
                            $alt = translateFN("Nodo non visitabile");
                            $icon = "_nododis.png"; // _nododis.png
                            if ($with_icons)
                                $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\"> ";
                            $index_item.= $child_dataHa['name'];
                        }

                        break;
                    case ADA_GROUP_TYPE:    //group
                        if ($child_dataHa['level']<=$user_level) {
                            $alt = translateFN("Approfondimento");
                            $icon = "_gruppo.png";
                            if (!isset($hide_visits) OR $hide_visits==0) {
                                $visit_count  = ADAUser::is_visited_by_userFN($id_child,$sess_id_course_instance,$sess_id_user);
                            }
                            if  (empty($visit_count)) {
                                if ($with_icons)
                                    $index_item = "<img name=\"nodo\" alt=$alt src=\"img/$icon\"> ";
                                $index_item .= "<b><a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a></b>";
                            } else {
                                if ($with_icons)
                                    $index_item = "<img name=\"nodo\" alt=$alt src=\"img/$icon\"> ";
                                $index_item .="<a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a>
                                   ($visit_count)";
                            }


                            // is user visiting this node?
                            if ($id_child==$sess_id_node)
                                $index_item .= "<img  name=\"attuale\" alt=\"attuale\" src=\"img/_anchor.png\">";

                            // has user bookmarked this node?
                            $id_bk = Bookmark::is_node_bookmarkedFN($sess_id_user,$id_child);
                            if ($id_bk)
                                $index_item .= "<a href=\"bookmarks.php?op=zoom&id_bk=$id_bk\"><img name=\"bookmark\" alt=\"bookmark\" src=\"img/check.png\" border=\"0\"></a>";
                        } else {
                            $alt = translateFN("Approfondimento non visitabile");
                            $icon = "_gruppodis.png";
                            if ($with_icons)
                                $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\" >";
                            $index_item .=$child_dataHa['name'];
                        }
                        break;
                    case ADA_NOTE_TYPE:    // note added by users
                    case ADA_PRIVATE_NOTE_TYPE:    // note added by users
                        $index_item="";
                        break;
                    default: // exercise, etc
                        $index_item="";
                        break;
                } // end case
            }  // end if

            if (!empty($index_item)) {
                if ($with_dates)
                    $index_item .= " $creation_date";
                if ($with_authors)
                    $index_item .= " $node_author_name $node_author_surname";
                $indexAr[] = $index_item;
            }
        }   // end foreach
        return $indexAr;
    }


    function   explode_nodesFN($depth,$user_level,$id_parent,$id_profile,$order,$expand) {
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_user = $_SESSION['sess_id_user'];
        $sess_id_course = $_SESSION['sess_id_course'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];

        $id_course  = $GLOBALS['id_course'];
        $dh = $GLOBALS['dh'];
        $debug = $GLOBALS['debug'];
        $sess_id_course = $GLOBALS['sess_id_course'];
        $hide_visits = $GLOBALS['hide_visits'];

        // recursive
        $indexAr = array();
        if (!empty($expand)  && ($expand > $depth)) {
            $childrenAr = $dh->get_node_children($id_parent);
            if (is_array($childrenAr)) {
                $depth++;
                $childnumber = 0;

                $index_item = array();
                foreach ($childrenAr as $id_child) {

                    if (!empty($id_child)) {
                        $sub_indexAr = "";
                        $childnumber++;
                        $visit_count = 0;
                        $child_dataHa = $dh->get_node_info($id_child);
                        if (is_array($child_dataHa)) {
                            $node_type = $child_dataHa['type'];
                            switch ($node_type) {
                                case ADA_LEAF_TYPE:    //node
                                    if ($child_dataHa['level']<=$user_level) {
                                        $alt = translateFN("Nodo inferiore");
                                        $icon = "_nodo.png";

                                        switch ($id_profile) {
                                            case AMA_TYPE_STUDENT:
                                            default:
                                                if (!isset($hide_visits) OR $hide_visits==0) {
                                                    $visit_count  = ADAUser::is_visited_by_userFN($id_child,$sess_id_course_instance,$sess_id_user);
                                                }
                                                break;
                                            case AMA_TYPE_TUTOR:
                                            /*
                         if (!isset($hide_visits) OR $hide_visits==0) {
                         $visit_count  = User::is_visited_by_classFN($id_child,$sess_id_course_instance,$sess_id_course);
                         }
                                            */
                                                break;
                                            case AMA_TYPE_AUTHOR:
                                            /*
                         * if (!isset($hide_visits) OR $hide_visits==0) {
                         $visit_count  = User::is_visitedFN($id_child);
                         }
                                            */
                                        }
                                        if  ($visit_count==0) {
                                            $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\">
                                   <b><a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a></b>";
                                        } else {
                                            $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\">
                                   <a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a>
                                   ($visit_count)";
                                        }
                                    } else {
                                        $alt = translateFN("Nodo non visitabile");
                                        $icon = "_nododis.png"; // _nododis.png
                                        $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\">".$child_dataHa['name'];
                                    }
                                    // is user visiting this node?
                                    if ($id_child==$sess_id_node)
                                        $index_item .= "<img  name=\"attuale\" alt=\"attuale\" src=\"img/_anchor.png\">";

                                    // has user bookmarked this node?
                                    $id_bk = Bookmark::is_node_bookmarkedFN($sess_id_user,$id_child);
                                    if ($id_bk)
                                        $index_item .= "<a href=\"bookmarks.php?op=zoom&id_bk=$id_bk\"><img name=\"bookmark\" alt=\"bookmark\" src=\"img/check.png\"  border=\"0\"></a>";


                                    break;
                                case ADA_GROUP_TYPE:    //group
                                    if  ($child_dataHa['level']<=$user_level) {
                                        $alt = translateFN("Approfondimento");
                                        $icon = "_gruppo.png";

                                        switch ($id_profile) {
                                            case AMA_TYPE_STUDENT:
                                            default:
                                                if (!isset($hide_visits) OR $hide_visits==0) {
                                                    $visit_count  = ADAUser::is_visited_by_userFN($id_child,$sess_id_course_instance,$sess_id_user);
                                                }
                                                break;
                                            case AMA_TYPE_TUTOR:
                                            /*
                               if (!isset($hide_visits) OR $hide_visits==0) {
                               $visit_count  = User::is_visited_by_classFN($id_child,$sess_id_course_instance,$sess_id_course);
                               }
                                            */
                                                break;
                                            case AMA_TYPE_AUTHOR:
                                            /*
                               if (!isset($hide_visits) OR $hide_visits==0) {
                               $visit_count  = User::is_visitedFN($id_child);
                               }
                                            */
                                                break;
                                            case AMA_TYPE_ADMIN:
                                                break;
                                        }
                                        if  ($visit_count==0) {
                                            $index_item = "<img name=\"nodo\" alt=$alt src=\"img/$icon\">
                                   <b><a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a></b>$expand_link";
                                        } else {
                                            $index_item = "<img name=\"nodo\" alt=$alt src=\"img/$icon\">
                                   <a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a>
                                   ($visit_count)";
                                        }


                                        // is user visiting this node?
                                        if ($id_child==$sess_id_node)
                                            $index_item .= "<img  name=\"attuale\" alt=\"attuale\" src=\"img/_anchor.png\">";

                                        // has user bookmarked this node?
                                        $id_bk = Bookmark::is_node_bookmarkedFN($sess_id_user,$id_child);
                                        if ($id_bk)
                                            $index_item .= "<a href=\"bookmarks.php?op=zoom&id_bk=$id_bk\"><img name=\"bookmark\" alt=\"bookmark\" src=\"img/check.png\" border=\"0\"></a>";

                                        // recurses...
                                        //$sub_indexAr = array();
                                        $sub_indexAr = $this->explode_nodesFN($depth,$user_level,$id_child,$id_profile,$order,$expand);

                                    } else {
                                        $alt = translateFN("Approfondimento non visitabile");
                                        $icon = "_gruppodis.png";
                                        $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\">".$child_dataHa['name'];
                                    }
                                    break;
                                case ADA_NOTE_TYPE:    // node added by users
                                // we don't want to show notes here
                                    $index_item="";
                                    break;
                                case ADA_PRIVATE_NOTE_TYPE:    // node added by users
                                // we don't want to show private notes here
                                    $index_item="";
                                    break;
                                case 3: // exercise
                                case 4: // exercise
                                case 5: // exercise
                                case 6: // exercise
                                case 7: // exercise

                                    $out_fields_ar = array('data_visita','punteggio','ripetibile');
                                    $history_exerc = $dh->find_ex_history_list($out_fields_ar,$sess_id_user, $sess_id_course_instance, $id_child);
                                    if (is_array($history_exerc)) {
                                        $h_exerc = array_shift($history_exerc);
                                        if (is_array($h_exerc))
                                            $already_executed = !$h_exerc[3];
                                        else
                                            $already_executed = "";
                                    } else {
                                        $already_executed = "";
                                    }

                                    //$debug=1;mydebug(__LINE__,__FILE__,$already_executed[1]); $debug=0;
                                    if (!$already_executed) {
                                        $alt = translateFN("Esercizio");
                                        $icon = "_exer.png";
                                        $index_item = "<img name=\"esercizio\" alt=\"$alt\" src=\"img/$icon\"> <a href=exercise.php?id_node=".$id_child.">".$child_dataHa['name']."</a>";
                                    } else {
                                        $date = ts2dFN($history_exerc[0][1]);
                                        $alt = translateFN("Esercizio eseguito il ").$date;
                                        $icon = "_exerdis.png"; // _gruppodis.png
                                        $index_item = "<img name=\"esercizio\" alt=\"$alt\" src=\"img/$icon\">".$child_dataHa['name'];
                                    }

                                    // is user visiting this node?
                                    if ($id_child==$sess_id_node)
                                        $index_item .= "<img  name=\"attuale\" alt=\"attuale\" src=\"img/_anchor.png\">";

                                    // has user bookmarked this node?
                                    $id_bk = Bookmark::is_node_bookmarkedFN($sess_id_user,$id_child);
                                    if ($id_bk)
                                        $index_item .= "<a href=\"bookmarks.php?op=zoom&id_bk=$id_bk\"><img name=\"bookmark\" alt=\"bookmark\" src=\"img/check.png\"></a>";

                                    break;
                                default: //?
                                    $index_item="";
                                /*
                               $icon = "_nodo.png";
                               $alt = translateFN("Nodo");
                               $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\"> <a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a>";
                                */

                            } // end case
                        }
                    }  // end if
                    if (!empty($index_item)) {
                        $indexAr[] = $index_item;
                        if (is_array($sub_indexAr))
                            array_push($indexAr,$sub_indexAr);
                    }

                }   // end foreach
                // mydebug(__LINE__,__FILE__,$index);
                return $indexAr;
            } else {
                if (is_object($childrenAr)) { // is it an error?
                    return "";
                } else {
                    return "";
                }

            }
        } else {
            return "";
        }
    }

	public function getMaxLevel() {
		$dh = $GLOBALS['dh'];

		return $dh->get_course_max_level($this->id);
	}

        /* sara - Execute advaced search: first in AND, then progressively in OR
         *
         * es:
         *
         * field_1 AND field_2 AND field_3 if empty:
         * field_1 AND field_2 OR field_3  if empty:
         * field_1 OR field_2 AND field_3  if empty:
         * field_1 OR field_2 OR field_3
         *
         */
        function executeSearch($name,$title,$text,$dh,$count,$id_user)
        {
            $out_fields_ar=array('nome','titolo','testo','tipo','id_utente','livello');
            $operator=array(0=>' AND ',1=>' OR ',2=>' AND ',3=>' OR ');
            $operator2=array(0=>' AND ',1=>' AND ',2=>' OR ',3=>' OR ');
            if($count==2)
            {
                $operator2=array(0=>' AND ',1=>' OR ');
            }
            if($count==3)
            {
                $count=$count+1;
            }

            for($i=0;$i<$count;$i++)
            {
              if (!empty($name)) {
                    $clause = "nome LIKE '%$name%'";
               }
              if (!empty($title)){ //keywors
                  if (isset($clause)) {
                      if($operator[$i]==' OR ' && $operator2[$i]==' AND ')
                      {
                          $clause = '('.$clause . $operator[$i]. "titolo LIKE '%$title%')";
                      }
                      else
                      {
                          if($operator[$i]==' AND ' && $operator2[$i]==' OR ')
                          {
                              $clause = $clause . $operator[$i]. "( titolo LIKE '%$title%'";
                          }
                          else
                          {
                              $clause = $clause . $operator[$i]. " titolo LIKE '%$title%'";
                          }
                      }
                 }
                 else
                 {
                     $clause ="titolo LIKE '%$title%'";
                 }
              }
              if (!empty($text)){
                  if (isset($clause)) {
                      if($operator[$i]==' AND ' && $operator2[$i]==' OR ')
                      {
                          $clause = $clause . $operator2[$i]. "testo LIKE '%$title%')";
                      }
                      else
                      {
                          $clause = $clause . $operator2[$i]. "testo LIKE '%$text%'";
                      }
                 }
                  else
                 {
                     $clause ="testo LIKE '%$text%'";
                 }
             }
              $clause = '('.$clause.') and ((tipo <> '.ADA_PRIVATE_NOTE_TYPE.') OR (tipo ='.ADA_PRIVATE_NOTE_TYPE.' AND id_utente = '.$id_user.'))';
              $resHa = $dh->find_course_nodes_list($out_fields_ar, $clause,$_SESSION['sess_id_course']);
              if(!AMA_DataHandler::isError($resHa) and is_array($resHa) and !empty($resHa))
              {
                  break;
              }
              $clause="";
            }
            return $resHa;

        }


}   //end class Course

class Course_instance_Old {

    var $id;
    var $id_corso;
    var $data_inizio;
    var $durata;
    var $data_inizio_previsto;
    var $id_layout;
    var $data_fine;
    var $template_family;
    var $self_instruction;
    var $self_registration;
    var $title;
    var $duration_subscription;
    var $price;
    var $start_level_student;
    var $open_subscription;
    var $duration_hours;
    var $service_level;



    public function __construct($id_course_instance) {
        $dh = $GLOBALS['dh'];

        // constructor
        $dataHa = $dh->course_instance_get($id_course_instance, true);
        if (AMA_DataHandler::isError($dataHa) || (!is_array($dataHa))) {
            $this->full = 0;
        }
        else {
            if (!empty($dataHa['id_corso'])) {
                $this->full = 1;
                foreach ($dataHa as $key=>$value) {
                    $this->$key = $value;
                }
                $this->id = $id_course_instance;
                $id_layout = $this->id_layout;
                // Table Layout is not available.
                //$layoutHa = $dh->_get_layout($id_layout);
                //$this->template_family = $layoutHa['family'];
            }
        }
    }

    function class_main_indexFN($id_toc='',$depth=1,$id_profile,$order='struct',$expand=1) {
        // indice di classe
        //  this version is intended for  tutor  or author use only
        $dh = $GLOBALS['dh'];
        $debug = $GLOBALS['debug'];
        $with_icons = $GLOBALS['with_icons'];
        $sess_id_course = $_SESSION['sess_id_course'];
        if (empty($id_toc))
            $id_toc = $sess_id_course."_".ADA_DEFAULT_NODE;
        $base = new Node($id_toc,0);  // da dove parte
        $alt = translateFN("Gruppo principale");
        $icon = "_gruppo.png";
        $index = "<p>";
        if ($order=='struct') {
            if ($with_icons)
                $index .= "<img name=\"nodo\" alt=$alt src=\"img/$icon\"> <a href=view.php?id_node=".$id_toc.">".translateFN("Principale")."</a>";
            else
                $index .= "<a href=view.php?id_node=".$id_toc.">".translateFN("Principale")."</a>";

        }
        $index .= $this->tabled_class_explode_nodesFN(1,$id_toc,$id_profile,$order,$expand);
        $index .= "</p>";
        return $index;

    }

    function tabled_class_explode_nodesFN($depth,$id_parent,$id_profile,$order,$expand=1) {
        $lObj = new Ilist();
        if ($order=='alfa') {
            $data =  $this->class_explode_nodes_iterativeFN($depth,$id_parent,$id_profile,$order,$expand);
            $lObj->initList('1','1',1);
        }   else {    // = 'r'

            $data =  $this->class_explode_nodesFN($depth,$id_parent,$id_profile,$order,$expand);
            $lObj->initList(0,'',1);
        }
        $lObj->setList($data);
        $tabled_index = $lObj->getList();

        return $tabled_index;
    }

    function  class_explode_nodes_iterativeFN($depth,$id_parent,$id_profile,$order,$expand=1) {
        //  this version is intended for  tutor  or author use only
        // returns an array


        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_user = $_SESSION['sess_id_user'];
        $sess_id_course = $_SESSION['sess_id_course'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];
        $id_course  = $GLOBALS['id_course'];
        $dh = $GLOBALS['dh'];
        $debug = $GLOBALS['debug'];
        $hide_visits = $GLOBALS['hide_visits'];

        $tot_notes = 0;
        $childnumber = 0;
        $indexAr = array();
        $out_fields_ar = array('nome','tipo');
        $clause = "";
        $childrenAr = $dh->find_course_nodes_list($out_fields_ar,$clause,$sess_id_course);
        $childrenAr = masort($childrenAr, 1); // il campo 1 �il nome del nodo
        foreach ($childrenAr as $nodeHa) {
            $index_item = "";
            $id_child = $nodeHa[0];
            if (!empty($id_child)) {
                $childnumber++;
                $child_dataHa = $dh->get_node_info($id_child);
                $node_instance = $child_dataHa['instance'];
                $id_node_parent = $child_dataHa['parent_id'];
                $node_keywords = $child_dataHa['title'];
                $parent_dataHa = $dh->get_node_info($id_node_parent);
                if ((!AMA_datahandler::isError($parent_dataHa)) && ($parent_dataHa['type']>= ADA_STANDARD_EXERCISE_TYPE))
                    $node_type = 'answer';
                else
                    $node_type = $child_dataHa['type'];

                switch ($node_type) { // exercises?
                    case 'answer':
                        break;
                    case ADA_LEAF_TYPE:    //node
                        $alt = translateFN("Nodo inferiore");
                        $icon = "_nodo.png";
                        if (!isset($hide_visits) OR $hide_visits==0) {
                            $visit_count  = ADAUser::is_visited_by_userFN($id_child,$sess_id_course_instance,$sess_id_user);
                        }
                        if  (empty($visit_count)) {
                            $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\">&nbsp;<b><a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a></b>\n";
                        } else {
                            $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\">&nbsp;<a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a> ($visit_count)\n";
                        }
                        // has user bookmarked this node?
                        $id_bk = Bookmark::is_node_bookmarkedFN($sess_id_user,$id_child);
                        if ($id_bk)
                            $index_item .= "&nbsp;<a href=\"bookmarks.php?op=zoom&id_bk=$id_bk\"><img name=\"bookmark\" alt=\"bookmark\" src=\"img/check.png\"  border=\"0\"></a>";

                        break;
                    case ADA_GROUP_TYPE:    //group
                        $alt = translateFN("Approfondimento");
                        $icon = "_gruppo.png";
                        if (!isset($hide_visits) OR $hide_visits==0) {
                            $visit_count  = ADAUser::is_visited_by_userFN($id_child,$sess_id_course_instance,$sess_id_user);
                        }
                        if  (empty($visit_count)) {
                            $index_item .= "<img name=\"nodo\" alt=$alt src=\"img/$icon\">&nbsp;<b><a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a></b>\n";
                        } else {
                            $index_item .= "<img name=\"nodo\" alt=$alt src=\"img/$icon\">&nbsp;<a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a>($visit_count)";
                        }


                        // is user visiting this node?
                        if ($id_child==$sess_id_node)
                            $index_item .= "&nbsp;<img  name=\"attuale\" alt=\"attuale\" src=\"img/_anchor.png\">&nbsp;";

                        // has user bookmarked this node?
                        $id_bk = Bookmark::is_node_bookmarkedFN($sess_id_user,$id_child);
                        if ($id_bk)
                            $index_item .= "<a href=\"bookmarks.php?op=zoom&id_bk=$id_bk\"><img name=\"bookmark\" alt=\"bookmark\" src=\"img/check.png\" border=\"0\"></a>&nbsp;";
                        break;
                    case ADA_NOTE_TYPE:    // note added by users
                    case ADA_PRIVATE_NOTE_TYPE:    // private note added by users
                        $index_item = "";
                        break;
                    default: // ?
                        $index_item = "";
                        break;
                } // end case
            }  // end if
            if (!empty($index_item))
                $indexAr[] = $index_item;
        }   // end foreach
        return $indexAr;
    }

    function   class_explode_nodesFN($depth,$id_parent,$id_profile,$order,$expand) {
        //  this version is intended for  tutor  or author use only
        // returns an array

        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_user = $_SESSION['sess_id_user'];
        $sess_id_course = $_SESSION['sess_id_course'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];
        $id_course  = $GLOBALS['id_course'];
        $dh = $GLOBALS['dh'];
        $debug = $GLOBALS['debug'];
        $hide_visits = $GLOBALS['hide_visits'];

        // recursive

        if (!empty($expand)  && ($expand > $depth)) {
            $childrenAr = $dh->get_node_children($id_parent);
            $indexAr = array();
            if (is_array($childrenAr)) {
                $index_item = "";
                $sub_indexAr = array();
                $depth++;
                $childnumber = 0;
                foreach ($childrenAr as $id_child) {
                    if (!empty($id_child)) {
                        $childnumber++;
                        $visit_count = 0;
                        $child_dataHa = $dh->get_node_info($id_child);
                        $node_type = $child_dataHa['type'];
                        switch ($node_type) {
                            case ADA_LEAF_TYPE:    //node
                                $alt = translateFN("Nodo");
                                $icon = "_nodo.png";

                                switch ($id_profile) {
                                    case AMA_TYPE_STUDENT:
                                        break;
                                    case AMA_TYPE_TUTOR:
                                        if (!isset($hide_visits) OR $hide_visits==0) {
                                            $visit_count  = ADAUser::is_visited_by_classFN($id_child,$sess_id_course_instance,$sess_id_course);
                                        }
                                        break;

                                    case AMA_TYPE_AUTHOR:
                                        if (!isset($hide_visits) OR $hide_visits==0) {
                                            $visit_count  = ADAUser::is_visitedFN($id_child);
                                        }
                                    case AMA_TYPE_ADMIN:
                                        break;
                                } //end switch $id_profile
                                if  ($visit_count==0) {
                                    $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\">
                                   <b><a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a></b>\n";
                                } else {
                                    $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\">
                                   <a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a>
                                    (".translateFN("visite").": $visit_count)\n";
                                }


                                // is user visiting this node?
                                if ($id_child==$sess_id_node)
                                    $index_item .= "<img  name=\"attuale\" alt=\"attuale\" src=\"img/_anchor.png\">";

                                // is someone else there?
                                $is_someone = ADAUser::is_someone_thereFN($sess_id_course_instance,$id_child);
                                if ($is_someone>=1)
                                    $index_item .= "<img  name=\"altri\" alt=\"altri\" src=\"img/_student.png\">";

                                break;
                            case ADA_GROUP_TYPE:    //group
                                $alt = translateFN("Approfondimento");
                                $icon = "_gruppo.png";

                                switch ($id_profile) {
                                    case AMA_TYPE_TUTOR:
                                        if (!isset($hide_visits) OR $hide_visits==0) {
                                            $visit_count  = ADAUser::is_visited_by_classFN($id_child,$sess_id_course_instance,$sess_id_course);
                                        }
                                        break;
                                    case AMA_TYPE_AUTHOR:
                                        if (!isset($hide_visits) OR $hide_visits==0) {
                                            $visit_count  = ADAUser::is_visitedFN($id_child);
                                        }
                                } // end switch $id_profile

                                if  ($visit_count==0) {
                                    $index_item = "<img name=\"nodo\" alt=$alt src=\"img/$icon\">
                                   <b><a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a></b>\n";
                                } else {
                                    $index_item = "<img name=\"nodo\" alt=$alt src=\"img/$icon\">
                                   <a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a>
                                    (".translateFN("visite").": $visit_count)\n";
                                }
                                // is user visiting this node?
                                if ($id_child==$sess_id_node)
                                    $index_item .= "<img  name=\"attuale\" alt=\"attuale\" src=\"img/_anchor.png\">";

                                // is someone else there?
                                $is_someone = ADAUser::is_someone_thereFN($sess_id_course_instance,$id_child);
                                if ($is_someone>=1)
                                    $index_item .= "<img  name=\"altri\" alt=\"altri\" src=\"img/_student.png\">";

                                $sub_indexAr = $this->class_explode_nodesFN($depth,$id_child,$id_profile,$order,$expand);

                                break;
                            case ADA_NOTE_TYPE:    // note added by users
                            case ADA_PRIVATE_NOTE_TYPE:    // private note added by users
                                $index_item = "";
                                // we don't want to show notes here
                                break;
                            case 3: // exercise
                            case 4: // exercise
                            case 5: // exercise
                            case 6: // exercise
                                $alt = translateFN("Esercizio");
                                $icon = "_exer.png";
                                if (($id_profile == AMA_TYPE_AUTHOR) or ($id_profile == AMA_TYPE_TUTOR))
                                    $index_item = "<img name=\"esercizio\" alt=\"$alt\" src=\"img/$icon\"> <a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a>\n";
                                else
                                    $index_item = "<img name=\"esercizio\" alt=\"$alt\" src=\"img/$icon\"> <a href=exercise.php?id_node=".$id_child.">".$child_dataHa['name']."</a>\n";

                                break;
                            default:
                                $icon = "_nodo.png";
                                $alt = translateFN("Nodo");
                                $index_item = "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\"> <a href=view.php?id_node=".$id_child.">".$child_dataHa['name']."</a>\n";
                        } // end switch $node_type
                    }  // end if
                    if (!empty($index_item)) {
                        $indexAr[] = $index_item;
                        if (is_array($sub_indexAr))
                            array_push($indexAr,$sub_indexAr);
                    }
                }   // end foreach
                // mydebug(__LINE__,__FILE__,$index);
                return $indexAr;
            } else {
                if (is_object($childrenAr)) { // is it an error?
                    return "";
                } else {
                    return "";
                }
            }
        } else {
            return "";
        }

    }

    function forum_main_indexFN($id_toc='',$depth=1,$id_profile,$order='chrono',$id_student,$mode='standard') {
        // class function
        // only notes are showed
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_user = $_SESSION['sess_id_user'];
        $sess_id_course = $_SESSION['sess_id_course'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];
        $id_course  = $GLOBALS['id_course'];
        $dh = $GLOBALS['dh'];
        $debug = $GLOBALS['debug'];
        $hide_visits = $GLOBALS['hide_visits'];



        if (empty($id_toc))
            $id_toc = $sess_id_course."_".ADA_DEFAULT_NODE;
        $base = new Node($id_toc,0);  // da dove parte
        $alt = translateFN("Gruppo principale");
        $icon = "_gruppo.png";

        $out_fields_ar = array('data_creazione','tipo');
        $clause = "id_istanza =  $sess_id_course_instance AND tipo = ".ADA_NOTE_TYPE;
        $childrenAr = $dh->find_course_nodes_list($out_fields_ar,$clause,$sess_id_course);
        $note_count = count($childrenAr);
        $index = $note_count . translateFN(" note attualmente presenti nel Forum di classe.");
        $index .= "<p>";
        if ($order=='struct') {
            // $index .= "<img name=\"nodo\" alt=$alt src=\"img/$icon\"> <a href=view.php?id_node=".$id_toc.">".translateFN("Principale")."</a>";
            $index .= $this->tabled_forum_explode_nodesFN(1,$id_toc,$id_profile,$order,$id_student,$mode);
        } else { //order=chrono
            $index .= $this->tabled_forum_explode_nodesFN(1,$id_toc,$id_profile,$order,$id_student,$mode);
        }
        $index .= "</p>";
        return $index;
    }


    function tabled_forum_explode_nodesFN($depth,$id_parent,$id_profile,$order,$id_student,$mode='standard') {
        // returns an html list
        $lObj = new Ilist();

        if ($order=='chrono') {
            $data =  $this->forum_explode_nodes_iterativeFN($depth,$id_parent,$id_profile,$order,$id_student,$mode);
            $lObj->initList('1','1',1);
        }   else {    // = 'struct'
            $data =  $this->forum_explode_nodesFN($depth,$id_parent,$id_profile,$order,$id_student,$mode);
            $lObj->initList(0,'',1);
        }
        $lObj->setList($data);
        $tabled_index = $lObj->getList();
        return $tabled_index;
    }

    function   forum_explode_nodes_iterativeFN($depth,$id_parent,$id_profile,$order,$id_student,$mode='standard') {
        // only notes are showed !
        // returns an array

        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_user = $_SESSION['sess_id_user'];
        $sess_id_course = $_SESSION['sess_id_course'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];
        $id_course  = $GLOBALS['id_course'];
        $id_node_exp =  $GLOBALS['id_node_exp'];
        $dh = $GLOBALS['dh'];
        $debug = $GLOBALS['debug'];
        $hide_visits = $GLOBALS['hide_visits'];
        $with_icons = $GLOBALS['with_icons'];

        $tot_notes = 0;
        $childnumber = 0;
        $indexAr = array();
        $out_fields_ar = array('data_creazione','tipo');
        $clause = "id_istanza =  $sess_id_course_instance";
        $childrenAr = $dh->find_course_nodes_list($out_fields_ar,$clause,$sess_id_course);
        // $debug=1; mydebug(__LINE__,__FILE__,$childrenAr);
        $childrenAr = masort($childrenAr, 1,-1);
        foreach ($childrenAr as $nodeHa) {
            $index_item = "";
            $id_child = $nodeHa[0];
            if (!empty($id_child)) {
                $childnumber++;
                $child_dataHa = $dh->get_node_info($id_child);
                $node_type = $child_dataHa['type'];
                $node_instance = $child_dataHa['instance'];
                switch ($node_type) {
                    case ADA_LEAF_TYPE:    //node
                        break;
                    case ADA_GROUP_TYPE:    //group
                        break;
                    case ADA_NOTE_TYPE:    // note added by users
                    case ADA_PRIVATE_NOTE_TYPE:
                        $tot_notes++;
                        // we want to show ONLY notes here
                        // notes doesn't have levels !
                        $node_date = $child_dataHa['creation_date'];
                        $autoreHa = $child_dataHa['author'];
                        $autore =  $autoreHa['id'];
                        $is_note_visibile = 0;
                        $class_tutor_id = $dh->course_instance_tutor_get($sess_id_course_instance);
                        $expand_link = "<a href=\"main_index.php?op=forum&id_course=$sess_id_course&id_course_instance=$sess_id_course_instance&id_node_exp=$id_child\"><img src=\"img/_expand.png\" border=0></a>&nbsp;";
                        $contract_link = "<a href=\"main_index.php?op=forum&id_course=$sess_id_course&id_course_instance=$sess_id_course_instance\"><img src=\"img/_contract.png\" border=0></a>&nbsp;";

                        if ($class_tutor_id == $autore) { //Nota del tutor
                            $is_note_visibile = 1;
                            $alt = translateFN("Nota del tutor");
                            $icon = "_nota_tutor.png";
                            if ($sess_id_user == $autore)
                                $author_name = "<strong>".$autoreHa['username']."</strong>";
                            else
                                $author_name = $autoreHa['username'];

                        } else {
                            if (($node_type ==ADA_PRIVATE_NOTE_TYPE) && ($id_student == $autore)) { // nota dello studente
                                $is_note_visibile = 1;
                                $alt = translateFN("Nota privata");
                                $icon = "_nota_pers.png";
                                $author_name = "<strong>".$autoreHa['username']."</strong>";

                            } else {
                                //   $author_dataHa =  $dh->get_subscription($autore, $sess_id_course_instance);
                                //   if (!AMA_DB::isError($author_dataHa) AND (!VIEW_PRIVATE_NOTES_ONLY)){
                                $is_note_visibile = 1;
                                $alt = translateFN("Nota di un altro studente");
                                $icon = "_nota.png";
                                $author_name = $autoreHa['username'];
                            }
                        }
                        if ($is_note_visibile) {
                            if ((($id_profile == AMA_TYPE_TUTOR)  OR ($id_profile == AMA_TYPE_STUDENT)) AND (!isset($hide_visits) OR $hide_visits==0))
                                $visit_count  = "(".ADAUser::is_visited_by_classFN($id_child,$sess_id_course_instance,$sess_id_course).")";
                            else
                                $visit_count = "";
                            switch ($mode) {
                                case 'export_all':
                                    $index_item ="\n $node_date -  $author_name\n".
                                            $child_dataHa['name'] . $visit_count ."\n".
                                            $child_dataHa['text']."\n";
                                    break;
                                case 'export_single':
                                    if ($autore==$id_student) {
                                        $index_item ="\n $node_date \n".
                                                $child_dataHa['name'] . $visit_count ."\n".
                                                $child_dataHa['text']."\n";
                                    }
                                    break;
                                case 'standard':
                                default:
                                    if ($with_icons) {
                                        $index_item ="$node_date <img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\">&nbsp;<a href=view.php?id_node=".$id_child.">".$child_dataHa['name'] ."</a> (".$author_name.") $visit_count \n";
                                    }   else {
                                        $index_item ="$node_date <a href=view.php?id_node=".$id_child.">".$child_dataHa['name'] ."</a> (".$author_name.") $visit_count \n";
                                    }
                                    if ((!empty($id_node_exp)) AND ($id_node_exp==$id_child)) { // node to expand INLINE
                                        $index_item =   "<hr><dl><dd class=\"nota\">".$contract_link.$index_item;
                                        $index_item .=   "<a name=$id_node_exp>".$child_dataHa['text']."</dd></dl>\n";
                                    } else {
                                        $index_item =   "<hr>".$expand_link.$index_item;
                                    }

                                    // is someone else there?
                                    $is_someone = ADAUser::is_someone_thereFN($sess_id_course_instance,$id_child);
                                    if ($is_someone>=1)
                                        if ($with_icons)
                                            $index_item .= "&nbsp;<img  name=\"altri\" alt=\"altri\" src=\"img/_student.png\">";
                                        else
                                            $index_item .= " +";

                            }
                        }

                        break;
                    case ADA_STANDARD_EXERCISE: // exercise
                    default:
                        break;
                } // end case
            }  // end if
            if (!empty($index_item))
                $indexAr[]=$index_item;
        }   // end foreach
        return $indexAr;
    }

    function   forum_explode_nodesFN($depth,$id_parent,$id_profile,$order,$id_student,$mode='standard') {
        // recursive (slow!)
        // only notes are showed
        // returns an array

        $sess_id_user = $_SESSION['sess_id_user'];
        $sess_id_course = $_SESSION['sess_id_course'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];
        $id_course  = $GLOBALS['id_course'];
        $dh = $GLOBALS['dh'];
        $debug = $GLOBALS['debug'];
        $hide_visits = $GLOBALS['hide_visits'];
        $with_icons = $GLOBALS['with_icons'];

        static $tot_notes;


        // recursive
        if (!isset($indexAr))
            $indexAr= array();

        if (!isset($tot_notes))
            $tot_notes= 0;

        $childrenAr = $dh->get_node_children($id_parent,$sess_id_course_instance);

        if (is_array($childrenAr)) {
            $depth++;
            $childnumber = 0;
            $class_tutor_id = $dh->course_instance_tutor_get($sess_id_course_instance);
            foreach ($childrenAr as $id_child) {
                if (!empty($id_child)) {
                    $sub_indexAr = "";
                    $index_item = "";
                    $childnumber++;
                    $child_dataHa = $dh->get_node_info($id_child);
                    $node_type = $child_dataHa['type'];
                    $node_instance = $child_dataHa['instance'];
                    switch ($node_type) {
                        case ADA_LEAF_TYPE:    //node
                        case ADA_GROUP_TYPE:    //group
                            $sub_indexAr = $this->forum_explode_nodesFN($depth,$id_child,$id_profile,$order,$id_student);
                            break;
                        case ADA_NOTE_TYPE:    // node added by users
                        case ADA_PRIVATE_NOTE_TYPE:


                            $tot_notes++;
                            // we want to show ONLY notes here
                            $node_date = $child_dataHa['creation_date'];
                            $autoreHa = $child_dataHa['author'];
                            $autore =  $autoreHa['id'];
                            $is_note_visibile = 0;

                            // echo "TUTOR $class_tutor_id == AUTORE $autore == USER $sess_id_user";
                            if ($class_tutor_id == $autore) {
                                if ($node_instance == $sess_id_course_instance) {
                                    $is_note_visibile = 1;
                                    $alt = translateFN("Nota del tutor");
                                    $icon = "_nota_tutor.png";
                                    if ($sess_id_user == $autore) // per ora c'e' un solo tutor per classe...
                                        $author_name = "<strong>".$autoreHa['username']."</strong>";
                                    else
                                        $author_name = $autoreHa['username'];
                                }
                            }
                            else {
                                /*
                   * vito, 8 ottobre 2008 corretto il nome della costante in ADA_PRIVATE_NOTE_TYPE
                                */
                                /*
                   if (($node_type == ADA_PRIVATE_NOTE_TYPE) &&($id_student == $autore)){
                   if ($node_instance == $sess_id_course_instance) {
                   $is_note_visibile = 1;
                   $alt = translateFN("Nota privata");
                   $icon = "_nota_pers.png";
                   $author_name = "<strong>".$autoreHa['username']."</strong>";
                   }
                   } else {
                   // $author_dataHa =  $dh->get_subscription($autore, $sess_id_course_instance);
                   // if ((!AMA_DB::isError($author_dataHa))  AND (!VIEW_PRIVATE_NOTES_ONLY)){
                   if ($node_instance == $sess_id_course_instance) {
                   $is_note_visibile = 1;
                   $alt = translateFN("Nota di un altro studente");
                   $icon = "_nota.png";
                   $author_name = $autoreHa['username'];
                   }
                   }
                   }
                                */
                                if ($node_type == ADA_PRIVATE_NOTE_TYPE
                                        && $id_student == $autore
                                        && $node_instance == $sess_id_course_instance
                                ) {
                                    $is_note_visibile = 1;
                                    $alt = translateFN("Nota privata");
                                    $icon = "_nota_pers.png";
                                    $author_name = "<strong>".$autoreHa['username']."</strong>";
                                }
                                else if ( $node_type == ADA_NOTE_TYPE
                                        && $node_instance == $sess_id_course_instance
                                ) {
                                    // $author_dataHa =  $dh->get_subscription($autore, $sess_id_course_instance);
                                    // if ((!AMA_DB::isError($author_dataHa))  AND (!VIEW_PRIVATE_NOTES_ONLY)){
                                    $is_note_visibile = 1;
                                    $icon = "_nota.png";

                                    if( $id_student == $autore) {
                                        $alt = translateFN("Tua nota pubblica");
                                        $author_name = "<strong>".$autoreHa['username']."</strong>";
                                    }
                                    else {
                                        $alt = translateFN("Nota di un altro studente");
                                        $author_name = $autoreHa['username'];
                                    }

                                }

                            }// end else   riga 1079

                            if ($is_note_visibile) {

                                if ((($id_profile == AMA_TYPE_TUTOR) OR ($id_profile == AMA_TYPE_STUDENT)) AND (!isset($hide_visits) OR $hide_visits==0))
                                    $visit_count  = "(".ADAUser::is_visited_by_classFN($id_child,$sess_id_course_instance,$sess_id_course).")";
                                else
                                    $visit_count = "";

                                if ($with_icons)
                                    $index_item .= "<img name=\"nodo\" alt=\"$alt\" src=\"img/$icon\">&nbsp;<a href=view.php?id_node=".$id_child.">".$child_dataHa['name'] ."</a> ($author_name) - $node_date $visit_count \n";
                                else
                                    $index_item .= "<a href=view.php?id_node=".$id_child.">".$child_dataHa['name'] ."</a> ($author_name) - $node_date $visit_count \n";

                                // is someone else there?
                                //  TOO SLOW !  $is_someone = User::is_someone_thereFN($sess_id_course_instance,$id_child);
                                if ($is_someone>=1)
                                    $index_item .= "<img  name=\"altri\" alt=\"altri\" src=\"img/_student.png\">";
                                else
                                    $index_item .= " ";
                            }
                            // echo "<br> $tot_notes $id_child $node_type $is_note_visibile";
                            $children2Ar = $dh->get_node_children($id_child,$sess_id_course_instance);
                            if (is_array($children2Ar)) { // there are sub-notes
                                $sub_indexAr = $this->forum_explode_nodesFN($depth,$id_child,$id_profile,$order,$id_student);
                            }
                            break;
                        case ADA_TYPE_STANDARD_EXERCISE: // exercise
                        default;

                            break;

                    } // end case $type
                }  // end if
                if (!empty($index_item))
                    $indexAr[] = $index_item;
                if (is_array($sub_indexAr))
                    array_push($indexAr,$sub_indexAr);
                //		  print_r($index_ar);
                // unset($sub_indexAr);
                unset($children2Ar);
            }   // end foreach

            return $indexAr;
        } else {
            if (is_object($childrenAr)) { // is it an error?
                return "";
            } else {
                return "";
            }

        }
    }


}




class Student_class {
    var $id;
    var $id_course_instance;
    var $student_list;


    function __construct($id_course_instance, $status=null) {
        $dh = $GLOBALS['dh'];
        // constructor
        if (is_null($status)) $status = ADA_STATUS_SUBSCRIBED; // we want only subscribed students
        $dataHa = $dh->course_instance_students_presubscribe_get_list($id_course_instance,$status); // Get student list of selected course

        if (AMA_DataHandler::isError($dataHa)) { // || (!is_array($dataHa))){ ** Se non e' un array non deve chiamare getMessage 12/05/2004

            $msg = $dataHa->getMessage();
            // header("Location: $error?err_msg=$msg");
        } else {
            if (!empty($dataHa[0]['id_utente_studente'])) {
                $this->full = 1;
                $this->student_list = $dataHa;
                $this->id = $id_course_instance;
            }
        }

    }

    function get_student_coursesFN($id_course,$order="",$speed_mode) {
        return $this->get_class_reportFN($id_course,$order,$speed_mode);
    }


    function get_class_report_from_dbFN($id_course,$id_course_instance) {
        require_once ROOT_DIR . '/switcher/include/Subscription.inc.php';
        // last data from db tble
        $dh = $GLOBALS['dh'];
        $info_course = $dh->get_course($id_course); // Get title course
        if  (AMA_DataHandler::isError($info_course)) {
            $msg = $info_course->getMessage();
            return $msg;
        }
        $course_title = $info_course['titolo'];
        $instance_course_ha = $dh->course_instance_get($id_course_instance); // Get the instance courses data
        if  (AMA_DataHandler::isError($instance_course_ha)) {
            $msg = $instance_course_ha->getMessage();
            return $msg;
        }
        $start_date =  AMA_DataHandler::ts_to_date($instance_course_ha['data_inizio'], ADA_DATE_FORMAT);

        /**
         * @author giorgio 27/ott/2014
         * from now on, passing a null date to read_class_data
         * will get the most updated class report, so it's
         * safe to get rid of the following 2 lines of code
         *
         */
//         $ymdhms = today_dateFN();
//         $utime = dt2tsFN($ymdhms);

		$utime = null;
        $report_dataHa = $this->read_class_data($id_course,$id_course_instance,$utime);
        // vito, 16 luglio 2008, gestione dell'errore relativo alla chiamata a read_class_data
        if (AMA_DataHandler::isError($report_dataHa)) {
            $msg = $report_dataHa->getMessage();
            return $msg;
        }

        $num_student = count($report_dataHa);
        if ($num_student >0) {
            /*
           * vito, 27 mar 2009. Add links to table data.
            */

            $totalHistory = 0;
            $totalExercies = 0;
            $totalExerciesMax = 0;
            $totalTest = 0;
            $totalTestMax = 0;
            $totalSurvey = 0;
            $totalSurveyMax = 0;
            $totalAddedNodes = 0;
            $totalReadNotes = 0;
            $totalMessageCountIn = 0;
            $totalMessageCountOut = 0;
            $totalChat = 0;
            $totalBookmarks = 0;
            $totalIndex = 0;
            $totalLevel = 0;
            $row = -1;

            $returnArray = array();
            if (isset($report_dataHa['report_generation_date'])) {
            	$report_generation_TS = $report_dataHa['report_generation_date'];
            	unset ($report_dataHa['report_generation_date']);
            } else $report_generation_TS = null;

            if (MODULES_BADGES) {
                Lynxlab\ADA\Module\Badges\RewardedBadge::loadInstanceRewards($id_course, $id_course_instance);
            }

            foreach ($report_dataHa as $currentReportRow) {
            	// returnArray elements order (keys) MUST be
            	// the same as returned by get_class_report_from_db
            	$returnArray[++$row]['id'] = $currentReportRow['id_stud'];
                $returnArray[$row]['student'] = '<a href="tutor.php?op=zoom_student&id_student='.$currentReportRow['id_stud'].'&id_course='.$id_course.'&id_instance='.$id_course_instance.'">'. $currentReportRow['student'] .'</a>';
                $returnArray[$row]['history']  = '<a href="tutor_history.php?id_student='.$currentReportRow['id_stud'].'&id_course='.$id_course.'&id_course_instance='.$id_course_instance.'">'. $currentReportRow['visits'] .'</a>';
                $returnArray[$row]['last_access'] = '<a href="tutor_history_details.php?period=1&id_student='.$currentReportRow['id_stud'].'&id_course='.$id_course.'&id_course_instance='.$id_course_instance.'">'. substr(ts2dFN($currentReportRow['date']),0,-5) .'</a>';
                $returnArray[$row]['exercises'] = '<a href="tutor_exercise.php?id_student='.$currentReportRow['id_stud'].'&id_course_instance='.$id_course_instance.'" class="dontwrap">'. $currentReportRow['score'] .
                	' '.translateFN('su').' '.$currentReportRow['exercises']*ADA_MAX_SCORE.'</a>';

                if (MODULES_TEST) {
                	$st_score_test = $currentReportRow['score_test'];
                	$st_score_norm_test = str_pad($st_score_test,5, "0", STR_PAD_LEFT);
                	$st_exer_number_test = $currentReportRow['exercises_test'];
                	$returnArray[$row]['exercises_test'] = '<!-- '.$st_score_norm_test.' --><a href="'.MODULES_TEST_HTTP.'/tutor.php?op=test&id_course_instance='.$id_course_instance.'&id_course='.$id_course.'&id_student='.$currentReportRow['id_stud'].'" class="dontwrap">'.$st_score_test.' '.translateFN('su').' '.$st_exer_number_test.'</a>';

                	$st_score_survey = $currentReportRow['score_survey'];
                	$st_score_norm_survey = str_pad($st_score_survey,5, "0", STR_PAD_LEFT);
                	$st_exer_number_survey = $currentReportRow['exercises_survey'];
                	$returnArray[$row]['exercises_survey'] = '<!-- '.$st_score_norm_survey.' --><a href="'.MODULES_TEST_HTTP.'/tutor.php?op=survey&id_course_instance='.$id_course_instance.'&id_course='.$id_course.'&id_student='.$currentReportRow['id_stud'].'" class="dontwrap">'.$st_score_survey.' '.translateFN('su').' '.$st_exer_number_survey.'</a>';
                }
                $returnArray[$row]['added_notes'] = '<a href="tutor.php?op=student_notes&id_student='.$currentReportRow['id_stud'].'&id_instance='.$id_course_instance.'">'. $currentReportRow['notes_out'] .'</a>';
                $returnArray[$row]['read_notes'] = ($currentReportRow['notes_in']>0) ? $currentReportRow['notes_in'] : '-';
                $returnArray[$row]['message_count_in'] = $currentReportRow['msg_in'];
                $returnArray[$row]['message_count_out'] = $currentReportRow['msg_out'];
                $returnArray[$row]['chat'] = $currentReportRow['chat'];
                $returnArray[$row]['bookmarks'] = $currentReportRow['bookmarks'];
                $returnArray[$row]['index'] = $currentReportRow['indice_att'];
                $returnArray[$row]['status'] = sprintf("<!-- %d -->%s", $currentReportRow['status'], Subscription::subscriptionStatusArray()[$currentReportRow['status']]);
                if (MODULES_BADGES) {
                    $returnArray[$row]['badges'] = Lynxlab\ADA\Module\Badges\RewardedBadge::buildStudentRewardHTML($id_course, $id_course_instance,$currentReportRow['id_stud'])->getHtml();
                }
                $returnArray[$row]['level'] = '<span id="studentLevel_'.$currentReportRow['id_stud'].'">'.$currentReportRow['level'].'</span>';
                $forceUpdate = false;
                $linksHtml = $this->generateLevelButtons($currentReportRow['id_stud'], $forceUpdate);
                $returnArray[$row]['level_plus'] = (!is_null($linksHtml)) ? $linksHtml : '-';


                // UPDATE TOTALS

                $totalHistory         += $currentReportRow['visits'];
                $totalExercies        += $currentReportRow['exercises'];
                $totalExerciesMax     += $currentReportRow['exercises']*ADA_MAX_SCORE;
                $totalTest            += $currentReportRow['score_test'];
                $totalTestMax         += $currentReportRow['exercises_test'];
                $totalSurvey          += $currentReportRow['score_survey'];
                $totalSurveyMax       += $currentReportRow['exercises_survey'];
                $totalAddedNodes      += $currentReportRow['notes_out'];
                $totalReadNotes       += $currentReportRow['notes_in'];
                $totalMessageCountIn  += $currentReportRow['msg_in'];
                $totalMessageCountOut += $currentReportRow['msg_out'];
                $totalChat            += $currentReportRow['chat'];
                $totalBookmarks       += $currentReportRow['bookmarks'];
                $totalIndex           += $currentReportRow['indice_att'];
                $totalLevel           += $currentReportRow['level'];

            }

            // generate and add footer (average) row
            $total = ++$row;
            $returnArray[++$row] = array(
            		'id' => '-',
            		'student' => translateFN("Media"),
            		'history' => round($totalHistory/$total,2),
            		'last_access' => '-',
            		'exercises' => round($totalExercies/$total,2).' '.translateFN('su').' '.floor($totalExerciesMax/$total)
            );

            if (MODULES_TEST) {
            	$returnArray[$row]['exercises_test'] = round($totalTest/$total,2).' '.translateFN('su').' '.
            		floor($totalTestMax/$total);
            	$returnArray[$row]['exercises_survey'] = round($totalSurvey/$total,2).' '.translateFN('su').' '.
            		floor($totalSurveyMax/$total);
            }

            $returnArray[$row]['added_notes'] = round ($totalAddedNodes/$total,2);
            $returnArray[$row]['read_notes'] = round ($totalReadNotes/$total,2);
            $returnArray[$row]['message_count_in'] = round($totalMessageCountIn/$total,2);
            $returnArray[$row]['message_count_out'] = round($totalMessageCountOut/$total,2);
            $returnArray[$row]['chat'] = round($totalChat/$total,2);
            $returnArray[$row]['bookmarks'] = round($totalBookmarks/$total,2);
            $returnArray[$row]['index'] = round($totalIndex/$total,2);
            $returnArray[$row]['status'] = '-';
            if (MODULES_BADGES) {
                $rew = Lynxlab\ADA\Module\Badges\RewardedBadge::getInstanceRewards();
                $returnArray[$row]['badges'] = round(array_sum($rew['studentsRewards']) / $total,2).' '.translateFN('su').' '.$rew['total'];
            }
            $returnArray[$row]['level'] = '<span id="averageLevel">'.round($totalLevel/$total,2).'</span>';
            $returnArray[$row]['level_plus'] = '-';

            // TABLE LABELS

            $table_labels[0] = $this->generate_class_report_header();

            /**
             * @author giorgio 27/ott/2014
             *
             * unset the unwanted columns data and labels. unwanted cols are defined in config/config_class_report.inc.php
             */

            $arrayToUse = 'reportHTMLColArray';
            $this->clean_class_reportFN($arrayToUse, $table_labels, $returnArray);

           	return array('report_generation_date'=>$report_generation_TS) + array_merge($table_labels,$returnArray);

        }
        return null;
    }

    // @author giorgio 14/mag/2013
    // added type parameter that defaults to 'xls'
    function get_class_reportFN($id_course,$order="",$index_att="",$type='HTML',$speed_mode=true) {
        $dh = $GLOBALS['dh'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        $debug  = isset($GLOBALS['debug']) ? $GLOBALS['debug'] : null;
        $npar = isset($GLOBALS['npar']) ? $GLOBALS['npar'] : null;
        $hpar = isset($GLOBALS['hpar']) ? $GLOBALS['hpar'] : null;
        $mpar = isset($GLOBALS['mpar']) ? $GLOBALS['mpar'] : null;
        $epar = isset($GLOBALS['epar']) ? $GLOBALS['epar'] : null;
        $bpar = isset($GLOBALS['bpar']) ? $GLOBALS['bpar'] : null;
        $cpar = isset($GLOBALS['cpar']) ? $GLOBALS['cpar'] : null;
        $spar = isset($GLOBALS['spar']) ? $GLOBALS['spar'] : null;

        // default parameters for activity index are in configuration file
        if (empty($npar))
            $npar = NOTE_PAR; // notes
        if (!isset($hpar))
            $hpar = HIST_PAR; // history
        if (!isset($mpar))
            $mpar = MSG_PAR; //messages
        if (!isset($epar))
            $epar = EXE_PAR; // exercises
        if (!isset($bpar))
            $bpar = (defined('BKM_PAR')) ? BKM_PAR : null; //bookmarks
        if (!isset($cpar))
            $cpar = (defined('CHA_PAR')) ? CHA_PAR : null; // chat
        if (!isset($spar))
            $spar = (defined('SURV_PAR')) ? SURV_PAR : null; // surveys

        $student_list_ar = $this->student_list;
        $id_instance = $this->id;
        if ($student_list_ar!=0) {
            $info_course = $dh->get_course($id_course); // Get title course
            if  (AMA_DataHandler::isError($info_course)) {
                $msg = $info_course->getMessage();
                return $msg;
            }
            $course_title = $info_course['titolo'];

            $instance_course_ha = $dh->course_instance_get($id_instance); // Get the instance courses data
            if  (AMA_DataHandler::isError($instance_course_ha)) {
                $msg = $instance_course_ha->getMessage();
                return $msg;
            }

            $start_date =  AMA_DataHandler::ts_to_date($instance_course_ha['data_inizio'], ADA_DATE_FORMAT);
            $tot_history_count = 0;
            $tot_exercises_score = 0;
            $tot_exercises_number = 0;
            $tot_added_notes = 0;
            $tot_read_notes  = 0;
            $tot_message_count = 0;
            $tot_message_count_in = 0;
            $tot_message_count_out = 0;
            $tot_bookmarks_count = 0;
            $tot_chatlines_count_out = 0;
            $tot_index = 0;
            $tot_level = 0;
            $tot_exercises_score_test = 0;
            $tot_exercises_number_test = 0;
            $tot_exercises_score_survey = 0;
            $tot_exercises_number_survey = 0;
            /**
             * @author giorgio 27/ott/2014
             *
             * change to:
             * $report_generation_TS = time();
             *
             * to have full date & time generation of report
             * but be warned that table log_classi may grow A LOT!
             */
            $report_generation_TS = dt2tsFN(today_dateFN());
            if ($speed_mode===true) {
                // in  $data_to_get we choose what fields to get back and the order of fields
                $columns = [
                    REPORT_COLUMN_HISTORY => 'history',
                    REPORT_COLUMN_LAST_ACCESS => 'last_access',
                    REPORT_COLUMN_EXERCISES_TEST => 'exercises_test',
                    REPORT_COLUMN_EXERCISES_SURVEY => 'exercises_survey',
                    REPORT_COLUMN_ADDED_NOTES => 'added_notes',
                    REPORT_COLUMN_READ_NOTES   => 'read_notes',
                    REPORT_COLUMN_MESSAGE_COUNT_IN  => 'message_count_in',
                    REPORT_COLUMN_MESSAGE_COUNT_OUT  => 'message_count_out',
                    REPORT_COLUMN_CHAT  => 'chat',
                    REPORT_COLUMN_BOOKMARKS  => 'bookmarks',
                    REPORT_COLUMN_INDEX  => 'index',
                    REPORT_COLUMN_STATUS => 'status',
                    REPORT_COLUMN_BADGES => 'badges',
                    REPORT_COLUMN_LEVEL  => 'level',
                    REPORT_COLUMN_LEVEL_PLUS  => 'level_plus',
                    REPORT_COLUMN_LEVEL_LESS  => 'level_less'
                ];
                $weights = [
                    REPORT_COLUMN_HISTORY => $hpar,
                    REPORT_COLUMN_EXERCISES_TEST => $epar,
                    REPORT_COLUMN_EXERCISES_SURVEY => $spar,
                    REPORT_COLUMN_ADDED_NOTES => $npar,
                    REPORT_COLUMN_READ_NOTES => $npar,
                    REPORT_COLUMN_MESSAGE_COUNT_IN => $mpar,
                    REPORT_COLUMN_MESSAGE_COUNT_OUT => $mpar,
                    REPORT_COLUMN_CHAT => $cpar,
                    REPORT_COLUMN_BOOKMARKS => $bpar
                ];
                $to_not_get = $GLOBALS['report' . $type . 'ColArray'];

                foreach ($to_not_get as $to_delete) {
                    unset($columns[constant($to_delete)]);
                    if (isset($weights[constant($to_delete)])) unset($weights[constant($to_delete)]);
                }

                if (array_key_exists(REPORT_COLUMN_BADGES, $columns)) {
                    if (MODULES_BADGES)
                        \Lynxlab\ADA\Module\Badges\RewardedBadge::loadInstanceRewards($id_course, $id_instance);
                    else unset($columns[REPORT_COLUMN_BADGES]);
                }

                if (!MODULES_TEST) {
                    if (isset($columns[REPORT_COLUMN_EXERCISES_TEST])) unset($columns[REPORT_COLUMN_EXERCISES_TEST]);
                    if (isset($columns[REPORT_COLUMN_EXERCISES_SURVEY])) unset($columns[REPORT_COLUMN_EXERCISES_SURVEY]);
                }

                if (array_key_exists(REPORT_COLUMN_STATUS, $columns)) {
                    require_once ROOT_DIR . '/switcher/include/Subscription.inc.php';
                }

                $stausIsButton = false;
                if (defined('MODULES_SERVICECOMPLETE') && MODULES_SERVICECOMPLETE) {
                    // need the service-complete module data handler
                    require_once MODULES_SERVICECOMPLETE_PATH . '/include/init.inc.php';
                    $mydh = AMACompleteDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
                    // load the conditionset for this course
                    $conditionSet = $mydh->get_linked_conditionset_for_course($id_course);
                    $stausIsButton = $conditionSet instanceof \CompleteConditionSet;
                    $mydh->disconnect();
                }


                $dati_stude = $dh->get_students_report($id_instance, $id_course, $columns, $weights);

                foreach ($dati_stude as $key => $user) {
                    // counters for statistics
                    $id_student = $dati_stude[$key]["id"];
                    $tot_history_count += $dati_stude[$key]["history"];
                    $tot_added_notes += $dati_stude[$key]["added_notes"];
                    $tot_read_notes += $dati_stude[$key]["read_notes"];
                    $tot_message_count_in += $dati_stude[$key]['message_count_in'];
                    $tot_message_count_out += $dati_stude[$key]['message_count_out'];
                    $tot_chatlines_count_out += $dati_stude[$key]['chat'];
                    $tot_bookmarks_count += isset($dati_stude[$key]['bookmarks']) ? $dati_stude[$key]['bookmarks'] :0;
                    $tot_index += isset($dati_stude[$key]['index']) ? $dati_stude[$key]['index'] : 0;
                    $tot_level += $dati_stude[$key]['level'];

                    //if in this installation module test is active
                    if (MODULES_TEST) {
                        if (array_key_exists(REPORT_COLUMN_EXERCISES_TEST, $columns)) {
                            $st_score_test = explode(" ".translateFN("su")." ", $dati_stude[$key]["exercises_test"])[0];
                            $st_exer_number_test = explode(" ".translateFN("su")." ", $dati_stude[$key]["exercises_test"])[1];
                            $dati_stude[$key]["exercises_test"] = '<a href="' . MODULES_TEST_HTTP . '/tutor.php?op=test&id_course_instance=' . $id_instance . '&id_course=' . $id_course . '&id_student=' . $id_student . '" class="dontwrap">' . $st_score_test . ' ' . translateFN('su') . ' ' . $st_exer_number_test . '</a>';
                            $tot_exercises_score_test += $st_score_test;
                            $tot_exercises_number_test += $st_exer_number_test;
                        }

                        if (array_key_exists(REPORT_COLUMN_EXERCISES_SURVEY, $columns)) {
                            $st_score_survey = explode(" ".translateFN("su")." ", $dati_stude[$key]["exercises_survey"])[0];
                            $st_exer_number_survey = explode(" ".translateFN("su")." ", $dati_stude[$key]["exercises_survey"])[1];
                            $dati_stude[$key]["exercises_survey"] = '<a href="' . MODULES_TEST_HTTP . '/tutor.php?op=survey&id_course_instance=' . $id_instance . '&id_course=' . $id_course . '&id_student=' . $id_student . '" class="dontwrap">' . $st_score_survey . ' ' . translateFN('su') . ' ' . $st_exer_number_survey . '</a>';
                            $tot_exercises_score_survey += $st_score_survey;
                            $tot_exercises_number_survey += $st_exer_number_survey;
                        }
                        //others counter for statistics
                    }

                    if (array_key_exists(REPORT_COLUMN_BADGES, $columns) && MODULES_BADGES) {
                        $dati_stude[$key]['badges'] = Lynxlab\ADA\Module\Badges\RewardedBadge::buildStudentRewardHTML($id_course, $id_instance, $id_student)->getHtml();
                    }

                    list ($firstanme, $lastname) = explode('::', $dati_stude[$key]["student"]);
                    // build HTML for name and surname
                    $st_name = "<a href=" .  $http_root_dir . "/tutor/tutor.php?op=zoom_student&id_student=" . $id_student;
                    $st_name .= "&id_course=" . $id_course . "&id_instance=" . $id_instance . ">";
                    $st_name .= $firstanme . "</a>";
                    $dati_stude[$key]["student"] = $st_name;

                    $st_lastname = "<a href=" .  $http_root_dir . "/tutor/tutor.php?op=zoom_student&id_student=" . $id_student;
                    $st_lastname .= "&id_course=" . $id_course . "&id_instance=" . $id_instance . ">";
                    $st_lastname .= $lastname . "</a>";

                    // insert lastname after student (aka firstname)
                    $nameIndex = 1+array_search("student", array_keys($dati_stude[$key]));
                    $dati_stude[$key] = array_slice($dati_stude[$key], 0, $nameIndex, true) +
                    array("lastname" => $st_lastname) +
                    array_slice($dati_stude[$key], $nameIndex, count($dati_stude[$key])-$nameIndex, true);

                    if (array_key_exists(REPORT_COLUMN_ADDED_NOTES, $columns)) {
                        // build HTML for added_notes
                        $dati_stude[$key]["added_notes"] = "<a href=$http_root_dir/tutor/tutor.php?op=student_notes&id_instance=$id_instance&id_student=$id_student>" . $dati_stude[$key]["added_notes"] . "</a>";
                    }

                    if (array_key_exists(REPORT_COLUMN_HISTORY, $columns)) {
                        // build HTML for history
                        $st_history = "<a href=" .  $http_root_dir . "/tutor/tutor_history.php?id_student=" . $id_student;
                        $st_history .= "&id_course=" . $id_course . "&id_course_instance=" . $id_instance . ">" . $dati_stude[$key]["history"] . "</a>";
                        $dati_stude[$key]["history"] = $st_history;
                    }

                    if (array_key_exists(REPORT_COLUMN_LAST_ACCESS, $columns)) {
                        // if has at least 1 access then build last_access HTML
                        if ($dati_stude[$key]["last_access"] != "-") {
                            $dati_stude[$key]["last_access"] = "<a href=\"$http_root_dir/tutor/tutor_history_details.php?period=1&id_student=$id_student&id_course_instance=$id_instance&id_course=$id_course\">" . $dati_stude[$key]["last_access"] . "</a>";
                        }
                    }

                    if (array_key_exists(REPORT_COLUMN_LEVEL, $columns)) {
                        //build level HTML
                        $dati_stude[$key]['level'] = '<span id="studentLevel_' . $id_student . '">' . $dati_stude[$key]['level'] . '</span>';
                    }

                    if (array_key_exists(REPORT_COLUMN_STATUS, $columns)) {
                        //build level HTML
                        if (defined('MODULES_SERVICECOMPLETE') && MODULES_SERVICECOMPLETE && $stausIsButton) {
                            $stBtn = \CDOMElement::create('button','class:ui tiny button servicecomplete-summary-modal');
                            $stBtn->setAttribute('data-student-id', $id_student);
                            $stBtn->setAttribute('data-instance-id', $id_instance);
                            $stBtn->setAttribute('data-course-id',$id_course);
                            $stBtn->addChild(new \CText(Subscription::subscriptionStatusArray()[$dati_stude[$key]['status']]));
                            $dati_stude[$key]['status'] = $stBtn->getHtml();
                        } else {
                            $dati_stude[$key]['status'] = Subscription::subscriptionStatusArray()[$dati_stude[$key]['status']];
                        }
                    }

                    if (array_key_exists(REPORT_COLUMN_LEVEL_PLUS, $columns) or in_array(REPORT_COLUMN_LEVEL_LESS, $columns)) {
                        //build level's buttons HTML
                        $forceUpdate = false;
                        $linksHtml = $this->generateLevelButtons($id_student, $forceUpdate);
                        $dati_stude[$key]['level_plus'] = (!is_null($linksHtml)) ? $linksHtml : '-';
                    }
                }

                $tot_students = count($dati_stude);
                // prevent division by zero
                $tot_students = $tot_students==0 ? 1 : $tot_students;
                // set av_student to the last dati_stude array key plus 1
                $av_student = 1+intval(key(array_slice($dati_stude, -1, 1, true)));
                $dati_stude[$av_student]['id'] = "-";
                $dati_stude[$av_student]["student"] = translateFN("Media");
                $dati_stude[$av_student]['lastname'] = "&nbsp;";

                $tableHeader['id'] = translateFN("Id");
                $tableHeader["student"] = translateFN("Nome");
                $tableHeader["lastname"] = translateFN("Cognome");

                if (array_key_exists(REPORT_COLUMN_HISTORY, $columns)) {
                    $tableHeader['history'] = translateFN("Visite");
                    $av_history = ($tot_history_count / $tot_students);
                    $dati_stude[$av_student]['history'] = round($av_history, 2);
                }

                if (array_key_exists(REPORT_COLUMN_LAST_ACCESS, $columns)) {
                    $tableHeader['last_access'] = translateFN("Recente");
                    $dati_stude[$av_student]['last_access'] = "-";
                }

                if (MODULES_TEST) {
                    if (array_key_exists(REPORT_COLUMN_EXERCISES_TEST, $columns)) {
                        $tableHeader['exercises_test'] = translateFN("Punti Test");
                        $av_exercises_test = round($tot_exercises_score_test / $tot_students, 2); // . ' ' . translateFN('su') . ' ' . floor($tot_exercises_number_test / $tot_students);
                        $dati_stude[$av_student]['exercises_test'] = '<span class="dontwrap">' . $av_exercises_test . '</span>';
                    }
                    if (array_key_exists(REPORT_COLUMN_EXERCISES_SURVEY, $columns)) {
                        $tableHeader['exercises_survey'] = translateFN("Punti Sondaggio");
                        $av_exercises_survey = round($tot_exercises_score_survey / $tot_students, 2); // . ' ' . translateFN('su') . ' ' . floor($tot_exercises_number_survey / $tot_students);
                        $dati_stude[$av_student]['exercises_survey'] = '<span class="dontwrap">' . $av_exercises_survey . '</span>';
                    }
                }

                if (array_key_exists(REPORT_COLUMN_ADDED_NOTES, $columns)) {
                    $tableHeader['added_notes'] = translateFN("Note Scri");
                    $av_added_notes = ($tot_added_notes / $tot_students);
                    $dati_stude[$av_student]['added_notes'] = round($av_added_notes, 2);
                }
                if (array_key_exists(REPORT_COLUMN_READ_NOTES, $columns)) {
                    $tableHeader['read_notes'] = translateFN("Note Let");
                    $av_read_notes = ($tot_read_notes / $tot_students);
                    $dati_stude[$av_student]['read_notes'] = round($av_read_notes, 2);
                }
                if (array_key_exists(REPORT_COLUMN_MESSAGE_COUNT_IN, $columns)) {
                    $tableHeader['message_count_in'] = translateFN("Msg Ric");
                    $av_message_count_in = ($tot_message_count_in / $tot_students);
                    $dati_stude[$av_student]['message_count_in'] = round($av_message_count_in, 2);
                }
                if (array_key_exists(REPORT_COLUMN_MESSAGE_COUNT_OUT, $columns)) {
                    $tableHeader['message_count_out'] = translateFN("Msg Inv");
                    $av_message_count_out = ($tot_message_count_out / $tot_students);
                    $dati_stude[$av_student]['message_count_out'] = round($av_message_count_out, 2);
                }
                if (array_key_exists(REPORT_COLUMN_CHAT, $columns)) {
                    $tableHeader['chat'] = translateFN("Chat ");
                    $av_chat_count_out = ($tot_chatlines_count_out / $tot_students);
                    $dati_stude[$av_student]['chat'] = round($av_chat_count_out, 2);
                }
                if (array_key_exists(REPORT_COLUMN_BOOKMARKS, $columns)) {
                    $tableHeader['bookmarks'] = translateFN("Bkms ");
                    $av_bookmarks_count = ($tot_bookmarks_count / $tot_students);
                    $dati_stude[$av_student]['bookmarks'] = round($av_bookmarks_count, 2);
                }
                if (array_key_exists(REPORT_COLUMN_INDEX, $columns)) {
                    $tableHeader['index'] = translateFN("Attivita'");
                    $av_index = ($tot_index / $tot_students);
                    $dati_stude[$av_student]['index'] = round($av_index, 2);
                }
                if (array_key_exists(REPORT_COLUMN_STATUS, $columns)) {
                    $tableHeader['status'] = translateFN("Stato");
                    $dati_stude[$av_student]['status'] = '-';
                }
                if (array_key_exists(REPORT_COLUMN_LEVEL, $columns)) {
                    $tableHeader['level'] = translateFN("Livello");
                    $av_level = ($tot_level / $tot_students);
                    $dati_stude[$av_student]['level'] = '<span id="averageLevel">' . round($av_level, 2) . '</span>';
                }
                if (array_key_exists(REPORT_COLUMN_BADGES, $columns) && MODULES_BADGES) {
                    $tableHeader['badges'] = translateFN("Badges");
                    $rew = Lynxlab\ADA\Module\Badges\RewardedBadge::getInstanceRewards();
                    $dati_stude[$av_student]['badges'] = round(array_sum($rew['studentsRewards']) / $tot_students, 2) . ' ' . translateFN('su') . ' ' . $rew['total'];
                }
                if (array_key_exists(REPORT_COLUMN_LEVEL_PLUS, $columns)) {
                    $tableHeader['level_plus'] = translateFN("Modifica livello");
                    $dati_stude[$av_student]['level_plus'] = "-";
                }

                if (!empty($order)) {
                    $dati_stude = masort($dati_stude, $order, 1, SORT_NUMERIC);
                }
                // TABLE LABELS
                $table_labels[0] = $tableHeader;

            } else {
                $num_student = -1;
                if (MODULES_TEST) {
                    $test_db = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
                    $test_score = $test_db->getStudentsScores($id_course, $id_instance);
                }
                if (MODULES_BADGES) {
                    \Lynxlab\ADA\Module\Badges\RewardedBadge::loadInstanceRewards($id_course, $id_instance);
                }
                foreach ($student_list_ar as $one_student) {
                    $num_student++; //starts with 0
                    $id_student = $one_student['id_utente_studente'];
                    $student_level = $one_student['livello'];
                    $status_student = $one_student['status'];
                    $dati['id'] = $id_student;
                    $dati['level'] = $student_level;
                    $ymdhms = today_dateFN();
                    $utime = dt2tsFN($ymdhms);
                    $dati['date'] = $report_generation_TS;

                    $goodStatuses = array(ADA_STATUS_SUBSCRIBED, ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED, ADA_STATUS_TERMINATED);
                    if (!empty($id_student) && in_array($status_student, $goodStatuses)) {

                        $studentObj = MultiPort::findUser($id_student); //new Student($id_student,$id_instance);

                        if ($studentObj->full != 0) { //==0) {
                            $err_msg = $studentObj->error_msg;
                        } else {

                            if ($studentObj instanceof ADAPractitioner) {
                                /**
                                 * @author giorgio 14/apr/2015
                                 *
                                 * If student is actually a tutor, build a new student
                                 * object for history and evaluation purposes
                                 */
                                $studentObj = $studentObj->toStudent();
                            }
                            $student_name = $studentObj->getFirstName(); //$studentObj->nome." ".$studentObj->cognome;
                            $student_lastname = $studentObj->getLastName();

                            // vito
                            $studentObj->set_course_instance_for_history($id_instance);
                            //$studentObj->history->setCourseInstance($id_instance);
                            $studentObj->history->setCourse($id_course);

                            $studentObj->get_exercise_dataFN($id_instance, $id_student);
                            $st_exercise_dataAr = $studentObj->user_ex_historyAr;
                            $st_score = 0;
                            $st_exer_number = 0;
                            if (is_array($st_exercise_dataAr)) {
                                foreach ($st_exercise_dataAr as $exercise) {
                                    $st_score += $exercise[7];
                                    $st_exer_number++;
                                }
                            }
                            $dati['exercises'] = $st_exer_number;
                            $dati['score'] = $st_score;

                            if (MODULES_TEST) {
                                $st_score_test = isset($test_score[$id_student]['score_test']) ? $test_score[$id_student]['score_test'] : 0;
                                $st_exer_number_test = isset($test_score[$id_student]['max_score_test']) ? $test_score[$id_student]['max_score_test'] : 0;
                                $dati['exercises_test'] = $st_exer_number_test;
                                $dati['score_test'] = $st_score_test;

                                $st_score_survey = isset($test_score[$id_student]['score_survey']) ? $test_score[$id_student]['score_survey'] : 0;
                                $st_exer_number_survey = isset($test_score[$id_student]['max_score_survey']) ? $test_score[$id_student]['max_score_survey'] : 0;
                                $dati['exercises_survey'] = $st_exer_number_survey;
                                $dati['score_survey'] = $st_score_survey;
                            }

                            $sub_courses = $dh->get_subscription($id_student, $id_instance);

                            if ($sub_courses['tipo'] == ADA_STATUS_SUBSCRIBED) {
                                $out_fields_ar = array('nome', 'titolo', 'id_istanza', 'data_creazione');
                                $clause = "tipo = '" . ADA_NOTE_TYPE . "' AND id_utente = '$id_student'";
                                $nodes = $dh->find_course_nodes_list($out_fields_ar, $clause, $id_course);
                                $added_nodes_count = count($nodes);
                                $added_nodes_count_norm = str_pad($added_nodes_count, 5, "0", STR_PAD_LEFT);

                                $added_notes = "<!-- $added_nodes_count_norm --><a href=$http_root_dir/tutor/tutor.php?op=student_notes&id_instance=$id_instance&id_student=$id_student>" . $added_nodes_count . "</a>";
                                //$added_notes = $added_nodes_count;
                            } else {
                                $added_notes = "<!-- 0 -->-";
                            }
                            $read_notes_count = $studentObj->total_visited_notesFN($id_student, $id_course);
                            if ($read_notes_count > 0) {
                                $read_nodes_count_norm = str_pad($read_notes_count, 5, "0", STR_PAD_LEFT);
                                $read_notes = "<!-- $read_nodes_count_norm -->$read_notes_count";
                            } else {
                                $read_notes = "<!-- 0 -->-";
                            }

                            $st_history_count = "0";
                            $debug = 0;
                            $st_history_count = $studentObj->total_visited_nodesFN($id_student, ADA_LEAF_TYPE);
                            // vito, 11 mar 2009. Ottiene solo il numero di visite a nodi di tipo foglia.
                            // vogliamo anche il numero di visite a nodi di tipo gruppo.
                            $st_history_count += $studentObj->total_visited_nodesFN($id_student, ADA_GROUP_TYPE);

                            $dati['visits'] = $st_history_count;

                            $st_name = "<!-- $student_name --><a href=" .  $http_root_dir . "/tutor/tutor.php?op=zoom_student&id_student=" . $id_student;

                            $st_name .= "&id_course=" . $id_course . "&id_instance=" . $id_instance . ">";
                            $st_name .= $student_name . "</a>";

                            $st_lastname = "<!-- $student_lastname --><a href=" .  $http_root_dir . "/tutor/tutor.php?op=zoom_student&id_student=" . $id_student;

                            $st_lastname .= "&id_course=" . $id_course . "&id_instance=" . $id_instance . ">";
                            $st_lastname .= $student_lastname . "</a>";

                            $st_history_count_norm = str_pad($st_history_count, 5, "0", STR_PAD_LEFT);
                            $st_history = "<!-- $st_history_count_norm --><a href=" .  $http_root_dir . "/tutor/tutor_history.php?id_student=" . $id_student;
                            $st_history .= "&id_course=" . $id_course . "&id_course_instance=" . $id_instance . ">";
                            $st_history .=  $st_history_count . "</a>";

                            $st_history_last_access = $studentObj->get_last_accessFN($id_instance, "T");
                            //$dati['date'] = $st_history_last_access;

                            $st_score_norm = str_pad($st_score, 5, "0", STR_PAD_LEFT);
                            $st_exercises = "<!-- $st_score_norm --><a href=" .  $http_root_dir . "/tutor/tutor_exercise.php?id_student=" . $id_student;
                            $st_exercises .= "&id_course_instance=" . $id_instance . " class='dontwrap'>";
                            $st_exercises .=  $st_score . " " . translateFN("su") . " " . ($st_exer_number * ADA_MAX_SCORE) . "</a>";

                            if (MODULES_TEST) {
                                $st_score_norm_test = str_pad($st_score_test, 5, "0", STR_PAD_LEFT);
                                $st_exercises_test = '<!-- ' . $st_score_norm_test . ' --><a href="' . MODULES_TEST_HTTP . '/tutor.php?op=test&id_course_instance=' . $id_instance . '&id_course=' . $id_course . '&id_student=' . $id_student . '" class="dontwrap">' . $st_score_test . ' ' . translateFN('su') . ' ' . $st_exer_number_test . '</a>';

                                $st_score_norm_survey = str_pad($st_score_survey, 5, "0", STR_PAD_LEFT);
                                $st_exercises_survey = '<!-- ' . $st_score_norm_survey . ' --><a href="' . MODULES_TEST_HTTP . '/tutor.php?op=survey&id_course_instance=' . $id_instance . '&id_course=' . $id_course . '&id_student=' . $id_student . '" class="dontwrap">' . $st_score_survey . ' ' . translateFN('su') . ' ' . $st_exer_number_survey . '</a>';
                            }

                            // user data
                            $dati_stude[$num_student]['id'] = $id_student;
                            $dati_stude[$num_student]['student'] = $st_name;
                            $dati_stude[$num_student]['lastname'] = $st_lastname;

                            // history
                            $dati_stude[$num_student]['history'] = $st_history;
                            $tot_history_count += $st_history_count;
                            if ($st_history_last_access != "-") {

                                $dati_stude[$num_student]['last_access'] = "<a href=\"$http_root_dir/tutor/tutor_history_details.php?period=1&id_student=$id_student&id_course_instance=$id_instance&id_course=$id_course\">" . $st_history_last_access . "</a>";
                                $dati['last_access'] = $studentObj->get_last_accessFN($id_instance, 'UT');
                            } else {
                                $dati_stude[$num_student]['last_access'] = $st_history_last_access;
                                $dati['last_access'] = null;
                            }

                            // exercises
                            $tot_exercises_score += $st_score;
                            $tot_exercises_number += $st_exer_number;
                            $dati_stude[$num_student]['exercises'] = $st_exercises;
                            $dati['exercises'] = $st_exer_number;

                            if (MODULES_TEST) {
                                $tot_exercises_score_test += $st_score_test;
                                $tot_exercises_number_test += $st_exer_number_test;
                                $dati_stude[$num_student]['exercises_test'] = $st_exercises_test;
                                $dati['exercises_test'] = $st_exer_number_test;

                                $tot_exercises_score_survey += $st_score_survey;
                                $tot_exercises_number_survey += $st_exer_number_survey;
                                $dati_stude[$num_student]['exercises_survey'] = $st_exercises_survey;
                                $dati['exercises_survey'] = $st_exer_number_survey;
                            }

                            // forum notes written
                            $dati_stude[$num_student]['added_notes'] = $added_notes;
                            $tot_added_notes += $added_nodes_count;
                            $dati['added_notes'] = $added_nodes_count;
                            // forum notes read
                            $dati_stude[$num_student]['read_notes'] = $read_notes;
                            $tot_read_notes += $read_notes_count;
                            $dati['read_notes'] = $read_notes_count;
                            // messages
                            //$mh = new MessageHandler("%d/%m/%Y - %H:%M:%S");

                            $mh = MessageHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
                            $sort_field = "data_ora desc";

                            // messages received

                            $msgs_ha = $mh->get_messages(
                                $id_student,
                                ADA_MSG_SIMPLE,
                                array("id_mittente", "data_ora"),
                                $sort_field
                            );
                            if (AMA_DataHandler::isError($msgs_ha)) {
                                $err_code = $msgs_ha->code;
                                $dati_stude[$num_student]['message_count_in'] = "-";
                            } else {
                                $user_message_count =  count($msgs_ha);
                                $dati_stude[$num_student]['message_count_in'] = $user_message_count;

                                $tot_message_count += $user_message_count;
                            }
                            $tot_message_count_in += $user_message_count;
                            $dati['msg_in'] = $user_message_count;



                            // messages sent

                            $msgs_ha = $mh->get_sent_messages(
                                $id_student,
                                ADA_MSG_SIMPLE,
                                array("id_mittente", "data_ora"),
                                $sort_field
                            );
                            if (AMA_DataHandler::isError($msgs_ha)) {
                                $err_code = $msgs_ha->code;
                                $dati_stude[$num_student]['message_count_out'] = "-";
                            } else {
                                $user_message_count =  count($msgs_ha);
                                $dati_stude[$num_student]['message_count_out'] = $user_message_count;
                                $tot_message_count += $user_message_count;
                            }
                            $tot_message_count_out += $user_message_count;
                            $dati['msg_out'] = $user_message_count;

                            //chat..
                            $msgs_ha = $mh->get_sent_messages(
                                $id_student,
                                ADA_MSG_CHAT,
                                array("id_mittente", "data_ora"),
                                $sort_field
                            );
                            if (AMA_DataHandler::isError($msgs_ha)) {
                                $err_code = $msgs_ha->code;
                                $dati_stude[$num_student]['chat'] = "-";
                            } else {
                                $chatlines_count_out =  count($msgs_ha);
                                $dati_stude[$num_student]['chat'] = $chatlines_count_out;
                                $tot_chatlines_count_out += $chatlines_count_out;
                            }
                            $tot_chatlines_count_out += $chatlines_count_out;
                            $dati['chat'] = $chatlines_count_out;

                            //bookmarks..
                            include_once 'bookmark_class.inc.php';
                            $bookmarks_count = count(Bookmark::get_bookmarks($id_student));
                            $dati_stude[$num_student]['bookmarks'] = $bookmarks_count;
                            $tot_bookmarks_count += $bookmarks_count;
                            $dati['bookmarks']  = $bookmarks_count;

                            // activity index
                            if (empty($index_att)) // parametro passato alla funzione
                                if (empty($GLOBALS['index_activity_expression'])) {
                                    //
                                    if (!isset($bcount)) $bcount = 1;
                                    $index = ($added_nodes_count * $npar) + ($st_history_count * $hpar)  + ($user_message_count * $mpar) + ($st_exer_number * $epar) + ($bookmarks_count * $bcount) + ($chatlines_count_out * $cpar);
                                } else
                                    $index = eval($GLOBALS['index_activity_expression']);
                            else
                                $index = eval($index_att);

                            $dati_stude[$num_student]['index'] = $index;
                            //echo $index;
                            $tot_index += $index;
                            $dati['index'] = $index;

                            // status
                            require_once ROOT_DIR . '/switcher/include/Subscription.inc.php';
                            $dati['status'] = $status_student;
                            $dati_stude[$num_student]['status'] = sprintf("<!-- %d -->%s", $status_student, Subscription::subscriptionStatusArray()[$status_student]);

                            if (MODULES_BADGES) {
                                $dati_stude[$num_student]['badges'] = Lynxlab\ADA\Module\Badges\RewardedBadge::buildStudentRewardHTML($id_course, $id_instance, $id_student)->getHtml();
                            }

                            // level
                            $tot_level += $student_level;
                            $dati_stude[$num_student]['level'] = '<span id="studentLevel_' . $id_student . '">' . $student_level . '</span>';
                            $forceUpdate = false;
                            $linksHtml = $this->generateLevelButtons($id_student, $forceUpdate);

                            $dati_stude[$num_student]['level_plus'] = (!is_null($linksHtml)) ? $linksHtml : '-';

                            // inserting a row in table log_classi

                            $this->log_class_data($id_course, $id_instance, $dati);
                        }
                    }
                }

                // average data
                $tot_students = ($num_student==0) ? 1 : $num_student;
                $av_history = ($tot_history_count / $tot_students);
                $av_exercises = ($tot_exercises_score / $tot_students) . " " . translateFN("su") . " " . floor($tot_exercises_number * ADA_MAX_SCORE / $tot_students);

                if (MODULES_TEST) {
                    $av_exercises_test = round($tot_exercises_score_test / $tot_students, 2) . ' ' . translateFN('su') . ' ' . floor($tot_exercises_number_test / $tot_students);
                    $av_exercises_survey = round($tot_exercises_score_survey / $tot_students, 2) . ' ' . translateFN('su') . ' ' . floor($tot_exercises_number_survey / $tot_students);
                }
                $av_added_notes = ($tot_added_notes / $tot_students);
                $av_read_notes = ($tot_read_notes / $tot_students);
                $av_message_count_in = ($tot_message_count_in / $tot_students);
                $av_message_count_out = ($tot_message_count_out / $tot_students);
                $av_chat_count_out = ($tot_chatlines_count_out / $tot_students);
                $av_bookmarks_count = ($tot_bookmarks_count / $tot_students);
                $av_index = ($tot_index / $tot_students);
                $av_level = ($tot_level / $tot_students);

                // set av_student to the last dati_stude array key plus 1
                $av_student = 1+intval(key(array_slice($dati_stude, -1, 1, true)));
                $dati_stude[$av_student]['id'] = "-";
                $dati_stude[$av_student]['student'] = translateFN("Media");
                $dati_stude[$av_student]['lastname'] = "&nbsp;";
                $dati_stude[$av_student]['history'] = round($av_history, 2);
                $dati_stude[$av_student]['last_access'] = "-";
                $dati_stude[$av_student]['exercises'] = '<span class="dontwrap">' . $av_exercises . '</span>';

                if (MODULES_TEST) {
                    $dati_stude[$av_student]['exercises_test'] = '<span class="dontwrap">' . $av_exercises_test . '</span>';
                    $dati_stude[$av_student]['exercises_survey'] = '<span class="dontwrap">' . $av_exercises_survey . '</span>';
                }

                $dati_stude[$av_student]['added_notes'] = round($av_added_notes, 2);
                $dati_stude[$av_student]['read_notes'] = round($av_read_notes, 2);
                $dati_stude[$av_student]['message_count_in'] = round($av_message_count_in, 2);
                $dati_stude[$av_student]['message_count_out'] = round($av_message_count_out, 2);
                $dati_stude[$av_student]['chat'] = round($av_chat_count_out, 2);
                $dati_stude[$av_student]['bookmarks'] = round($av_bookmarks_count, 2);

                $dati_stude[$av_student]['index'] = round($av_index, 2);
                $dati_stude[$av_student]['status'] = "-";
                if (MODULES_BADGES) {
                    $rew = Lynxlab\ADA\Module\Badges\RewardedBadge::getInstanceRewards();
                    $dati_stude[$av_student]['badges'] = round(array_sum($rew['studentsRewards']) / $tot_students, 2) . ' ' . translateFN('su') . ' ' . $rew['total'];
                }
                $dati_stude[$av_student]['level'] = '<span id="averageLevel">' . round($av_level, 2) . '</span>';
                $dati_stude[$av_student]['level_plus'] = "-";
                // @author giorgio 16/mag/2013
                // was $dati_stude[$av_student]['level_minus'] = "-";
                // $dati_stude[$av_student]['level_less'] = "-";

                if (!empty($order)) {
                    //var_dump($dati_stude);
                    $dati_stude = masort($dati_stude, $order, 1, SORT_NUMERIC);
                }

                // TABLE LABELS
                $table_labels[0] = $this->generate_class_report_header();

                /**
                 * @author giorgio 16/mag/2013
                 *
                 * unset the unwanted columns data and labels. unwanted cols are defined in config/config_class_report.inc.php
                 */

                $arrayToUse = 'report' . $type . 'ColArray';
                $this->clean_class_reportFN($arrayToUse, $table_labels, $dati_stude);
            }

           	return array('report_generation_date'=>$report_generation_TS) + array_merge($table_labels,$dati_stude);
        } else {
        	return null;
        }
    }    // end function get_class_reportFN{}

    /**
     * generates buttons for increasing and decreasing user level
     *
     * @param number  $id_student
     * @param boolean $forceUpdate true if the javascript must reload the page in update mode
     *
     * @return string|NULL
     */
    private function generateLevelButtons($id_student,$forceUpdate) {
    	// convert $forceUpdate to string to be properly passed to the JS
    	$forceUpdate = ($forceUpdate) ? 'true' : 'false';

    	$ButtonPlus =  CDOMElement::create('button','class: button_Increase');
    	$ButtonPlus->setAttribute('onclick','javascript:updateLevel('.$id_student.',1,'.$forceUpdate.');');

    	$ButtonMinus = CDOMElement::create('button','class: button_Decrease');
    	$ButtonMinus->setAttribute('onclick','javascript:updateLevel('.$id_student.',-1,'.$forceUpdate.');');

    	$links = array();
    	$links[0] = CDOMElement::create('li','class:liactions');
    	$links[0]->addChild ($ButtonPlus);

    	$links[1] = CDOMElement::create('li','class:liactions');
    	$links[1]->addChild ($ButtonMinus);

    	if(!empty($links)){
    		$linksul = CDOMElement::create('ul','class:ulactions');
    		foreach ($links as $link) $linksul->addChild ($link);
    		return $linksul->getHtml();
    	}
    	return null;
    }

    /**
     * @author giorgio 24/ott/2014
     *
     * generate class report table header
     *
     * @return array the generated table header array
     *
     * @access private
     */
    private function generate_class_report_header() {

    	$tableHeader['id'] = translateFN("Id");
        $tableHeader['student'] = translateFN("Nome");
        $tableHeader['lastname'] = translateFN("Cognome");
    	$tableHeader['history'] = translateFN("Visite");
    	$tableHeader['last_access'] = translateFN("Recente");
    	$tableHeader['exercises'] = translateFN("Punti A");

    	if (MODULES_TEST) {
    		$tableHeader['exercises_test'] = translateFN("Punti Test");
    		$tableHeader['exercises_survey'] = translateFN("Punti Sondaggio");
    	}

    	$tableHeader['added_notes'] = translateFN("Note Scri");
    	$tableHeader['read_notes'] = translateFN("Note Let");
    	$tableHeader['message_count_in'] = translateFN("Msg Ric");
    	$tableHeader['message_count_out'] = translateFN("Msg Inv");
    	$tableHeader['chat'] = translateFN("Chat ");
    	$tableHeader['bookmarks'] = translateFN("Bkms ");

        $tableHeader['index'] = translateFN("Attivita'");
        $tableHeader['status'] = translateFN("Stato");
        if (MODULES_BADGES) {
            $tableHeader['badges'] = translateFN("Badges");
        }
    	$tableHeader['level'] = translateFN("Livello");
    	$tableHeader['level_plus'] = translateFN("Modifica livello");

    	return $tableHeader;
    }

    /**
     * @author giorgio 24/ott/2014
     *
     * remove unwanted columns from the class report
     * unwanted cols are defined in config/config_class_report.inc.php
     *
     * @param array $arrayToUse
     * @param array $table_labels NOTE: passed by ref, this method will modify the array!
     * @param array $dati_stude   NOTE: passed by ref, this method will modify the array!
     *
     * @access private
     */
    private function clean_class_reportFN($arrayToUse, &$table_labels, &$dati_stude) {

    	if ( CONFIG_CLASS_REPORT && is_array($GLOBALS[$arrayToUse]) && count($GLOBALS[$arrayToUse]) )
    	{
    		foreach  ( $GLOBALS[$arrayToUse] as $reportCol )
    		{
    			if (constant($reportCol) >0)
    			{
    				preg_match("/^REPORT_COLUMN_([A-Z_]*)$/", $reportCol, $output_array);
    				$arrayKey = strtolower($output_array[1]);
    				unset ($table_labels[0][$arrayKey]);

    				foreach ($dati_stude as $key=>$oneRow)
    				{
    					unset ($dati_stude[$key][$arrayKey]);
    				}
    			}
    		}
    	}
    }

    function log_class_data($id_course,$id_course_instance,$dati_stude) {
        $dh = $GLOBALS['dh'];
        $debug  = isset($GLOBALS['debug']) ? $GLOBALS['debug'] : null;
        $dataHa = $dh->add_class_report($id_course,$id_course_instance,$dati_stude);
        if (AMA_DataHandler::isError($dataHa)) {
            $msg = $dataHa->getMessage();
            // header("Location: $error?err_msg=$msg");
        } else $msg = null;
        return $msg;

    }

    function read_class_data($id_course,$id_course_instance,$date) {
        $dh     = $GLOBALS['dh'];
        $debug  = isset($GLOBALS['debug']) ? $GLOBALS['debug'] : '';
        $dataHa = $dh->get_class_report($id_course,$id_course_instance,$date);
        // vito, 16 luglio 2008. Lasciamo la gestione dell'errore al chiamante.
        return $dataHa;

    }

    function read_student_data($id_course,$id_course_instance,$id_student) {
        $dh = $GLOBALS['dh'];
        $debug  = $GLOBALS['debug'];
        $dataHa = $dh->get_class_report($id_course,$id_course_instance,$id_student);
        if (AMA_DataHandler::isError($dataHa) || (!is_array($dataHa))) {
            $msg = $dataHa->getMessage();
            // header("Location: $error?err_msg=$msg");
        } else {

            return $dataHa;
        }
    }
    function find_student_index_att($id_course,$id_course_instance,$id_student) {
        // returns an array
        // last element is index
        $dh = $GLOBALS['dh'];
        $debug  = $GLOBALS['debug'];
        $clause = "";
        $out_fields_ar = array('indice_att');
        $dataHa = $dh->find_student_report ($id_student,$id_course_instance,$clause,$out_fields_ar);
        if (AMA_DataHandler::isError($dataHa)) {
            $msg = $dataHa->getMessage();
            // header("Location: $error?err_msg=$msg");
        } else {

            $last_index = count($dataHa);
            $student_dataAr = $dataHa[$last_index-1];
            $student_dataHa['id_log'] = $student_dataAr [0];
            $student_dataHa['id_course_instance'] = $student_dataAr [1];
            $student_dataHa['id_user'] = $student_dataAr [2];
            $student_dataHa['index_att'] = $student_dataAr [3];

            return $student_dataHa;
        }

    }
} // end class students class
