<?php
//
// +----------------------------------------------------------------------+
// | ADA version 1.8                                                                                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Lynx                                                         |
// +----------------------------------------------------------------------+
// |                                                                                                          |
// |                                 BOOKMARK     C L A S S                                  |
// |                                                                                                          |
// |                                                                                                          |
// |                                                                                                          |
// |                                                                                                          |
// |                                                                                                          |
// +----------------------------------------------------------------------+
// | Author: Stefano Penge <steve@lynxlab.com>                              |
// |                                                                                                          |
// +----------------------------------------------------------------------+
//
//
/**************************
/   bookmark management
/**************************/

class Bookmark{

        var $bookmark_id;
        var $descrizione;
        var $data;
        var $ora;
        var $node_id;
        var $corso;
        var $titolo;
        var $error_msg;
        var $full;

        function Bookmark($id_bk=""){
                // finds out information about a bookmark
                $dh=$GLOBALS['dh'];
                if (!empty($id_bk)){

                    $dataHa = $dh->get_bookmark_info($id_bk);
                    if (AMA_DataHandler::isError($dataHa)){
                        $this->error_msg = $dataHa->getMessage();
                        $this->full=0;
                    } else {
                      $this->bookmark_id = $id_bk;
                      $this->node_id =  $dataHa['node_id'];
                      $course_instance_id = $dataHa['course_id'];
                      $course_instanceHa = $dh->course_instance_get($course_instance_id);
                      $course_id = $course_instanceHa['id_corso'];
                      $courseHa = $dh->get_course($course_id);
                      $this->corso=$courseHa['titolo'];
                      $node = $dh->get_node_info($this->node_id);
                      $node_title = $node['name'];
                      $this->titolo =  $node_title;
                      $this->data = $dataHa['date'];
                      $this->utente = $dataHa['student_id'];
                      //$ts = dt2tsFN($dataHa['date']);
                      //$this->ora =  ts2tmFN($ts);
                      $this->descrizione = $dataHa['description'];
                    }
                }

       }

	

       public static function get_bookmarks($id_user,$id_tutor="",$id_node=''){
                // ritorna la lista di bookmark dell'utente ed eventualmente (se passato) per il nodo

                   $dh = $GLOBALS['dh'];
                   $error = $GLOBALS['error'];
                   $sess_id_course_instance = $GLOBALS['sess_id_course_instance'];
                   $debug = isset($GLOBALS['debug']) ? $GLOBALS['debug'] : null;

                $out_fields_ar = array('id_nodo','data','descrizione','id_utente_studente');
                $data_studentHa = $dh->find_bookmarks_list($out_fields_ar,$id_user,$sess_id_course_instance,$id_node);
                if (AMA_DataHandler::isError($data_studentHa)){
                        $msg = $data_studentHa->getMessage();
                        return $msg;
                       // header("Location: $error?err_msg=$msg");
                }
                if (!empty($id_tutor)) {
                        $data_tutorHa = $dh->find_bookmarks_list($out_fields_ar,$id_tutor,$sess_id_course_instance,$id_node);
                        $dataHa = array_merge($data_studentHa,$data_tutorHa);
                } else {
                        $dataHa = $data_studentHa;
                }
               	return  $dataHa;
       }

      function get_node_bookmarks($id_node){
                // ritorna la lista di bookmarks  per questo nodo per tutti gli utenti

                   $dh = $GLOBALS['dh'];
                   $error = $GLOBALS['error'];
                   $sess_id_course_instance = $GLOBALS['sess_id_course_instance'];
                   $debug = $GLOBALS['debug'];

                $out_fields_ar = array('id_nodo','data','descrizione','id_utente_studente');
                $dataHa = $dh->find_bookmarks_list($out_fields_ar,0,$sess_id_course_instance,$id_node);
                if (AMA_DataHandler::isError($dataHa)){
                        $msg = $dataHa->getMessage();
                        return $msg;
                       // header("Location: $error?err_msg=$msg");
                }
               	return  $dataHa;
       }


       public static function is_node_bookmarkedFN($id_user,$id_node){
                // cerca un nodo nella lista di bookmark dell'utente
                $dataHa = Bookmark::get_bookmarks($id_user,$id_tutor="",$id_node);

                /* foreach ($dataHa as $bkm){
                   $id_bk = $bkm[0];
                   $id_bk_node = $bkm[1];
                   if ($id_bk_node == $id_node)
                       return $id_bk;
                }
		*/
		if (is_array($dataHa) && isset($dataHa[0][0]))
			return  $dataHa[0][0];
		else
                	return FALSE;
       }


      function get_bookmark_info($id_bk=""){
                 if ($id_bk !=""){
                         //???
                 }
                 $res_ar[0]['id'] =  $this->bookmark_id;
                 $res_ar[0]['id_nodo'] =  $this->node_id;
                 $res_ar[0]['data']  =  $this->data;
                 //$res_ar[0]['ora']  =   $this->ora;
                 $res_ar[0]['corso'] = $this->corso;
                 $res_ar[0]['titolo'] = $this->titolo;
                 $res_ar[0]['descrizione'] = $this->descrizione;
                 $res_ar[0]['utente'] = $this->utente;

                return $res_ar;
      }

      function set_bookmark($id_user, $id_node,$node_title,$node_description=""){
                   $dh = $GLOBALS['dh'];
                   $error = $GLOBALS['error'];
                   $sess_id_course_instance = $GLOBALS['sess_id_course_instance'];
                   $debug = $GLOBALS['debug'];

                $date = ""; //init date
                $dataHa = $dh->add_bookmark($id_node, $id_user, $sess_id_course_instance, $date, $node_title);
                if (AMA_DataHandler::isError($dataHa)){
                    $msg = $dataHa->getMessage();
                     // VA gestito l'errore !
                     return $msg;
                     // header("Location: $error?err_msg=$msg");
                }  else {
                                        $this->bookmark_id = $dataHa;
                    return "";
                }

       }

       function update_bookmark($id_user,$id_bk,$node_description){
                // aggiorna il bookmark
                   $dh = $GLOBALS['dh'];
                   $error = $GLOBALS['error'];
                   $http_root_dir = $GLOBALS['http_root_dir'];
                   $sess_id_course_instance = $GLOBALS['sess_id_course_instance'];
                   $sess_id_user = $GLOBALS['sess_id_user'];
                   $debug = $GLOBALS['debug'];

                if (!isset($id_user))
                     $id_user = $sess_id_user;

                if (!isset($id_bk))
                     $id_bk = $this->bookmark_id;

                // verifica
                $res_ha = $dh->get_bookmark_info($id_bk);
                if  ($res_ha['student_id'] == $id_user){
                        $dataHa = $dh->set_bookmark_description($id_bk,$node_description);
                        if (AMA_DataHandler::isError($dataHa)){
                            $msg = $dataHa->getMessage();
                            return $msg;
                            // header("Location: $error?err_msg=$msg");
                        } else {
                            return $this->get_bookmark_info($id_bk);
                        }

                }

       }

       function remove_bookmark($id_user,$id_bk){
    
               $dh = $GLOBALS['dh'];
                   $error = $GLOBALS['error'];
                   $http_root_dir = $GLOBALS['http_root_dir'];
                   $sess_id_course_instance = $GLOBALS['sess_id_course_instance'];
                   $sess_id_user = $GLOBALS['sess_id_user'];
                   $debug = $GLOBALS['debug'];

                $date = ""; //init date
                if (!isset($id_user))
                     $id_user = $sess_id_user;
                if (!isset($id_bk))
                     $id_bk = $this->bookmark_id;

                // verifica
                $res_ha = $dh->get_bookmark_info($id_bk);
                if  ($res_ha['student_id'] == $id_user){
                        $dataHa = $dh->remove_bookmark($id_bk);
                        if (AMA_DataHandler::isError($dataHa)){
                            $msg = $dataHa->getMessage();
                            return "<strong>$msg</strong><br />";
                            // header("Location: $error?err_msg=$msg");
                        }
                }

        }



        function format_bookmarks($dataAr){
                $dh = $GLOBALS['dh'];
                $debug = $GLOBALS['debug'];
                $reg_enabled = $GLOBALS['reg_enabled'];
                $id_profile = $GLOBALS['id_profile'];
                if (!is_array($dataAr) || (!count($dataAr))){
                     $res = translateFN("Nessun segnalibro");
                     // header("Location: $error?err_msg=$msg");
                } else {
                   $formatted_dataHa = array();
                   $k=-1;
                   foreach ($dataAr as $bookmark)  {
                        $id_bk = $bookmark[0];
                        $id_node = $bookmark[1];
                        $date =   $bookmark[2];
                        $user =   $bookmark[4];
                        $node = $dh->get_node_info($id_node);
                        $title = $node['name'];
                        $description =   $bookmark[3];

                        $k++;
                        $formatted_dataHa[$k]['data'] =  ts2dFN($date);
                        if (is_array($dh->get_tutor($user))) { // bookmarks del tutor
                               $formatted_dataHa[$k]['id_nodo'] = "<a href=\"view.php?id_node=$id_node\"><img src=\"img/check.png\" border=0> $title</a> (".translateFN("Tutor").")";
                               if ($id_profile == AMA_TYPE_TUTOR){
                                        $formatted_dataHa[$k]['del'] =  "<a href=\"bookmarks.php?op=delete&id_bk=$id_bk\">
                                                        <img src=\"img/delete.png\" name=\"del_icon\" border=\"0\"
                                                        alt=\"" . translateFN("Elimina") . "\"></a>";
                                        $formatted_dataHa[$k]['edit'] =  "<a href=\"bookmarks.php?op=edit&id_bk=$id_bk\">
                                                        <img src=\"img/edit.png\" name=\"edit_icon\" border=\"0\"
                                                        alt=\"" . translateFN("Edit") . "\"></a>";
                               } else {
                                               $formatted_dataHa[$k]['del'] = "-";
                                               $formatted_dataHa[$k]['edit'] = "-";
                               }

                        } else {
                                $formatted_dataHa[$k]['nodo'] = "<a href=\"view.php?id_node=$id_node\"><img src=\"img/check.png\" border=0> $title</a>";
                                if ($reg_enabled){
                                        $formatted_dataHa[$k]['del'] =  "<a href=\"bookmarks.php?op=delete&id_bk=$id_bk\">
                                                        <img src=\"img/delete.png\" name=\"del_icon\" border=\"0\"
                                                        alt=\"" . translateFN("Elimina") . "\"></a>";
                                        $formatted_dataHa[$k]['edit'] =  "<a href=\"bookmarks.php?op=edit&id_bk=$id_bk\">
                                                        <img src=\"img/edit.png\" name=\"edit_icon\" border=\"0\"
                                                        alt=\"" . translateFN("Edit") . "\"></a>";
                                } else {
                                               $formatted_dataHa[$k]['del'] = "-";
                                               $formatted_dataHa[$k]['edit'] = "-";
                               }
                         }
                         $formatted_dataHa[$k]['zoom'] =  "<a href=\"bookmarks.php?op=zoom&id_bk=$id_bk\">
                                                        <img src=\"img/zoom.png\" name=\"zoom_icon\" border=\"0\"
                                                        alt=\"" . translateFN("Zoom") . "\"></a>";
                    }

                    $t = new Table();
                    $t->initTable('','default','2','1','100%','','','','',1,0);
                    $t->setTable($formatted_dataHa,translateFN("Segnalibri"),'');
                    $res = $t->getTable();
                }
                return $res;
        }

        function export_bookmarks($dataAr,$mode='ada'){

                   $dh = $GLOBALS['dh'];
                   $error = $GLOBALS['error'];
                   $http_root_dir = $GLOBALS['http_root_dir'];
                   $debug = $GLOBALS['debug'];

                if (!is_array($dataAr) || (!count($dataAr))){
                     $res = translateFN("Nessun segnalibro");
                       // header("Location: $error?err_msg=$msg");
                } else {
                     if ($mode=='standard') {
                            $formatted_data = "<a href=\"bookmarks.php?op=export&mode=ada\" >".translateFN("Formato ADA")."</a> | ";
                            $formatted_data .= translateFN("Formato Standard")."<p>";
                }   else {
                     $formatted_data = translateFN("Formato ADA")." | ";
                     $formatted_data .= "<a href=\"bookmarks.php?op=export&mode=standard\" >".translateFN("Formato Standard")."</a><p>";
                }
                $formatted_data .= "<form><textarea rows=10 cols=80 wrap=virtual>\n";
                $ilist_data = array();
                foreach ($dataAr as $bookmark)  {
                        $id_bk = $bookmark[0];
                        $id_node = $bookmark[1];
                        $date =   $bookmark[2];
                        $node = $dh->get_node_info($id_node);
                        $title = $node['name'];
                        $description =   $bookmark[3];
                        if ($mode=='standard') {
                           //formato standard
                           //$formatted_data.="<li><a href=\"$http_root_dir/browsing/view.php?id_node=$id_node\" alt=\"$title\"> $title </a></li>\n";
                           $list_item = "<a href=\"$http_root_dir/browsing/view.php?id_node=$id_node\" alt=\"$title\"> $title </a>";
                           $ilist_data[] = $list_item;
                         } else {
                           $c_n = explode('_',$id_node);
                           $num_node = $c_n[1];
                           // formato ADA
                         //  $formatted_data.="<li>$title <LINK TYPE=internal VALUE=\"$num_node\"></li>\n";
                          $list_item ="$title <LINK TYPE=internal VALUE=\"$num_node\">";
                           $ilist_data[] = $list_item;
                         }

                  }

                  $lObj = new IList();
                  $lObj->initList('1','a',3);
                  $lObj->setList($ilist_data);
                  $formatted_data .= $lObj->getList();
                  $formatted_data .="</textarea></form>\n</p>\n";
                }
                return $formatted_data;
        }

        function edit_bookmark($dataHa){

                 $sess_id_user = $GLOBALS['sess_id_user'];
                 $id_bk = $dataHa[0]['id'];

                 $dataAr = array();
                 array_push($dataAr,array(translateFN('Corso'),$dataHa[0]['corso']));
                 array_push($dataAr,array(translateFN('Nodo'),$dataHa[0]['titolo']));
                 array_push($dataAr,array(translateFN('Data'),$dataHa[0]['data']));
                 array_push($dataAr,array(translateFN('Id'),$dataHa[0]['id_nodo']));

                 $t = new Table();
                 $t->initTable('0','center','0','0','100%','black','white','black','white','0','0');
                 $t->setTable($dataAr,$caption="",$summary=translateFN("Caratteristiche del segnalibro"));
                 $t->getTable();

                 $formatted_data =  $t->data;


                 $data = array(
                     array(
                          'label'=>'',
                          'type'=>'textarea',
                          'name'=>'description',
                          'value'=>$dataHa[0]['descrizione'],
                          'rows'=>'10',
                          'cols'=>'80',
                          'wrap'=>'virtual'
                          ),
                     array(
                          'label'=>'',
                          'type'=>'submit',
                          'name'=>'Submit',
                          'value'=>translateFN('Salva')
                          ),
                     array(
                          'label'=>'',
                          'type'=>'hidden',
                          'name'=>'id_bk',
                          'value'=>$id_bk
                          ),
                     array(
                          'label'=>'',
                          'type'=>'hidden',
                          'name'=>'id_user',
                          'value'=>$sess_id_user
                          ),
                     array(
                          'label'=>'',
                          'type'=>'hidden',
                          'name'=>'op',
                          'value'=>'update'
                          )

                 );
                 $f = new Form();
                 $f->initForm("bookmarks.php","PUT","Edit");
                 $f-> setForm($data);
                 $formatted_data.=  $f->getForm();

                 return $formatted_data;
        }

        function add_bookmark(){


                 $dh = $GLOBALS['dh'];
                 $error = $GLOBALS['error'];
                 $sess_id_course = $GLOBALS['sess_id_course'];
                 $sess_id_user = $GLOBALS['sess_id_user'];
                 $debug = $GLOBALS['debug'];




                 $data = array(
                     array(
                          'label'=>'Nodo',
                          'type'=>'text',
                          'name'=>'id_node',
                          'value'=>$sess_id_course."_"
                          ),
                     array(
                          'label'=>'Titolo',
                          'type'=>'text',
                          'name'=>'booomark_title',
                          'value'=>translateFN('Titolo del bookmark'),
			
                          ),
                     array(
                          'label'=>'',
                          'type'=>'textarea',
                          'name'=>'description',
                          'value'=>translateFN('Descrizione del nodo'),
                          'rows'=>'10',
                          'cols'=>'80',
                          'wrap'=>'virtual'
                          ),
                     array(
                          'label'=>'',
                          'type'=>'submit',
                          'name'=>'Submit',
                          'value'=>translateFN('Salva')
                          ),
                     array(
                          'label'=>'',
                          'type'=>'hidden',
                          'name'=>'id_user',
                          'value'=>$sess_id_user
                          ),
                     array(
                          'label'=>'',
                          'type'=>'hidden',
                          'name'=>'op',
                          'value'=>'update'
                          )

                 );
                 $f = new Form();
                 $f->initForm("bookmarks.php","POST","Edit");
                 $f-> setForm($data);
                 $formatted_data.=  $f->getForm()."</td></tr>";

                 return $formatted_data;
        }

        function format_bookmark($dataHa){
                 $id_bk = $dataHa[0]['id'];


                 $formatted_dataHa = array();
                 $formatted_dataHa['corso'][0] = translateFN('Corso');
                 $formatted_dataHa['data'][0] = translateFN('Data');
                 //$formatted_dataHa['ora'][0] = translateFN('Ora');
                 $formatted_dataHa['titolo'][0] = translateFN('Titolo');
                 $formatted_dataHa['descrizione'][0] =  translateFN('Descrizione');

                 $formatted_dataHa['corso'][1] = $dataHa[0]['corso'];
                 $formatted_dataHa['data'][1] = $dataHa[0]['data'];
                 //$formatted_dataHa['ora'][1] = $dataHa[0]['ora'];
                 $formatted_dataHa['titolo'][1] = $dataHa[0]['titolo'];
                 $formatted_dataHa['descrizione'][1] =  $dataHa[0]['descrizione'];

                 $t = new Table();
                 $t->initTable(0,'default','0','0','100%','','','','',0,0);
                 $t->setTable($formatted_dataHa,translateFN("Dettaglio segnalibro"),'');
                 $res = $t->getTable();
                 return $res;

        }



	



} // end class Bookmarks


class Tag extends Bookmark {
        var $bookmark_id;
        var $descrizione;
        var $data;
        var $ora;
        var $node_id;
        var $corso;
        var $titolo;
        var $error_msg;
        var $full;

        function Tag ($id_bk=""){
                // finds out information about a tag
                $dh=$GLOBALS['dh'];
                if (!empty($id_bk)){

                    $dataHa = $dh->get_bookmark_info($id_bk);
                    if (AMA_DataHandler::isError($dataHa)){
                        $this->error_msg = $dataHa->getMessage();
                        $this->full=0;
                    } else {
                      $this->bookmark_id = $id_bk;
                      $this->node_id =  $dataHa['node_id'];
                      $course_instance_id = $dataHa['course_id'];
                      $course_instanceHa = $dh->course_instance_get($course_instance_id);
                      $course_id = $course_instanceHa['id_corso'];
                      $courseHa = $dh->get_course($course_id);
                      $this->corso=$courseHa['titolo'];
                      $node = $dh->get_node_info($this->node_id);
                      $node_title = $node['name'];
                      $this->titolo =  $node_title;
                      $this->data = $dataHa['date'];
                      $this->utente = $dataHa['student_id'];
                      //$ts = dt2tsFN($dataHa['date']);
                      //$this->ora =  ts2tmFN($ts);
                      $this->descrizione = $dataHa['description'];
                    }
                }

       }


        function edit_tag($dataHa){

                 $sess_id_user = $GLOBALS['sess_id_user'];
                 $id_bk = $dataHa[0]['id'];

                 $dataAr = array();
                 array_push($dataAr,array(translateFN('Corso'),$dataHa[0]['corso']));
                 array_push($dataAr,array(translateFN('Nodo'),$dataHa[0]['titolo']));
                 array_push($dataAr,array(translateFN('Data'),$dataHa[0]['data']));
                 array_push($dataAr,array(translateFN('Id'),$dataHa[0]['id_nodo']));

                 $t = new Table();
                 $t->initTable('0','center','0','0','100%','','','','','0','0');
                 $t->setTable($dataAr,$caption="",$summary=translateFN("Caratteristiche del tag "));
                 $t->getTable();

                 $formatted_data =  $t->data;


                 $data = array(
                     array(
                          'label'=>'',
                          'type'=>'textarea',
                          'name'=>'description',
                          'value'=>$dataHa[0]['descrizione'],
                          'rows'=>'10',
                          'cols'=>'80',
                          'wrap'=>'virtual'
                          ),
                     array(
                          'label'=>'',
                          'type'=>'submit',
                          'name'=>'Submit',
                          'value'=>translateFN('Salva')
                          ),
                     array(
                          'label'=>'',
                          'type'=>'hidden',
                          'name'=>'id_bk',
                          'value'=>$id_bk
                          ),
                     array(
                          'label'=>'',
                          'type'=>'hidden',
                          'name'=>'id_user',
                          'value'=>$sess_id_user
                          ),
                     array(
                          'label'=>'',
                          'type'=>'hidden',
                          'name'=>'op',
                          'value'=>'update'
                          )

                 );
                 $f = new Form();
                 $f->initForm("tags.php","POST","Edit");
                 $f-> setForm($data);
                 $formatted_data.=  $f->getForm();

                 return $formatted_data;
        }

 function add_tag($existing_tagsAr){


                 $dh = $GLOBALS['dh'];
                 $error = $GLOBALS['error'];
                 $sess_id_course = $GLOBALS['sess_id_course'];
  		 $sess_id_node = $GLOBALS['sess_id_node'];
                 $sess_id_user = $GLOBALS['sess_id_user'];
                 $debug = $GLOBALS['debug'];
		 $existing_tagAr = array('bello','interessante','confuso','dubbio');

	
		/*	
		    array(
                          'label'=>'Tag',
                          'type'=>'text',
                          'name'=>'description',
                          'value'=>translateFN('Descrizione del nodo')
                          ),
			*/
                 $data = array(
                     array(
                          'label'=>'',
                          //'type'=>'text',
			'type' => 'hidden',                          
			'name'=>'id_node',
                          'value'=>$sess_id_node
                          ),
                     array(
                          'label'=>'',
                          'type'=>'submit',
                          'name'=>'Submit',
                          'value'=>translateFN('Salva')
                          ),
                     array(
                          'label'=>'',
                          'type'=>'hidden',
                          'name'=>'id_user',
                          'value'=>$sess_id_user
                          ),
                     array(
                          'label'=>'',
                          'type'=>'hidden',
                          'name'=>'op',
                          'value'=>'update'
                          )

                 );
		// versione con select	
		$select_field =  array(
                          'label'=>'Tag',
                          'type'=>'select', //text',
                          'name'=>'booomark_title',
                          'value'=>$existing_tagsAr //translateFN("Tag")
                          );
		// versione con input
		$input_field =     array(
                          'label'=>'Tag',
                          'type'=>'text',
                          'name'=>'booomark_title',
                          'value'=>translateFN("Tag")
                          );
	        if (!is_array($existing_tagsAr))
			array_unshift($data,$input_field);
		else
			array_unshift($data,$select_field);

                 $f = new Form();
                 $f->initForm("tags.php","POST","Edit");
                 $f-> setForm($data);
                 $formatted_data.=  $f->getForm();//."</td></tr>";

                 return $formatted_data;
        }

function get_tagsFN($sess_id_course_instance,$id_bk){
		// ritorna una lista di tag con la descrizione uguale a quella passata
		 $dh = $GLOBALS['dh'];
                   $error = $GLOBALS['error'];
                  //$sess_id_course_instance = $GLOBALS['sess_id_course_instance'];
                  $sess_id_user = $GLOBALS['sess_id_user'];

                   $debug = $GLOBALS['debug'];
		$dataHa = $dh->get_bookmark_info($id_bk);
		$description = $dataHa['description'];
                $out_fields_ar = array('id_nodo','data','descrizione','id_utente_studente');
		$clause = "descrizione = '$description'";
                $dataHa = $dh->_find_bookmarks_list($out_fields_ar, $clause);
                if (AMA_DataHandler::isError($dataHa)){
                        $msg = $dataHa->getMessage();
                        return $msg;
                       // header("Location: $error?err_msg=$msg");
                }
		return $dataHa;
}

function get_class_tagsFN($sess_id_node,$sess_id_course_instance,$ordering='s'){
		//   ritorna una lista di tag per questo nodo, da tutta la classe

                  $dh = $GLOBALS['dh'];
                   $error = $GLOBALS['error'];
                  $sess_id_course_instance = $GLOBALS['sess_id_course_instance'];
                  $sess_id_user = $GLOBALS['sess_id_user'];

                   $debug = $GLOBALS['debug'];

                $out_fields_ar = array('id_nodo','data','descrizione','id_utente_studente');
                $dataHa = $dh->find_bookmarks_list($out_fields_ar,'',$sess_id_course_instance,$sess_id_node);
                if (AMA_DataHandler::isError($dataHa)){
                        $msg = $dataHa->getMessage();
                        return $msg;
                       // header("Location: $error?err_msg=$msg");
                }
//print_r($dataHa);
		switch ($ordering){
			case 'd': // date or id
			case 'i':
			default;
				$ordered_tagsHa = $dataHa;
				break;
			case 'a':	// ordering on absolute activity index
				//???
				break;
			case 's': // somiglianza tra activity index degli autori con quello dell'utente
				$student_classObj = New Student_class($sess_id_course_instance);
				// conviene farsi dare la lista e ordinarla una volta per tutte ?
				$student_listAr =  $student_classObj->student_list;
				foreach ($student_listAr as $student){
						$id_student = $student['id_utente_studente'];
						$student_dataHa =  $student_classObj->find_student_index_att($id_course,$sess_id_course_instance,$id_student);
						$user_activity_index = $student_dataHa['index_att'];
						$class_student_activityAr[$id_student] =$user_activity_index;
						//echo "$id_student : $user_activity_index <br>";
				}
				$user_activity_index = $class_student_activityAr[$sess_id_user];
				//print_r($class_student_activityAr);				
				//	asort ($class_student_activityAr,SORT_NUMERIC); // ordinamento su indice attività
				//print ($user_activity_index."<br>");
				$ord_tag_ind = array();
				$tagsdataHa= array();
				foreach  ($dataHa as $bk_Ar){
						//print_r($bk_Ar);
						$id_bk = $bk_Ar[0]; //BK id
						//$node = $bk_Ar[1];
						//$date = $bk_Ar[2]; //date
						//$description = $bk_Ar[3]; //description
						$author_id =  $bk_Ar[4];
						$author_activity_index = $class_student_activityAr[$author_id];
						$distance = abs($author_activity_index-$user_activity_index);
						$ord_tag_ind[$id_bk] = $distance;
						$tagsdataHa[$id_bk] = $bk_Ar;
						//echo ("$id_bk : $author_activity_index ; $distance<br>");
				}
//print_r($ord_tag_ind);
				asort ($ord_tag_ind,SORT_NUMERIC); // ordinamento su distanza ia da user
//print_r($ord_tag_ind);
				$ordered_tagsHa = array();
				foreach  ($ord_tag_ind as $id_bk =>$distance){
						//echo ("$id_bk => $distance ;");
						$ordered_tagsHa[] = $tagsdataHa[$id_bk];
				}
//print_r($ordered_tagsHa);
			break;
		//...
		}
              	return  $ordered_tagsHa;
                
		}

 function format_as_tag($dataHa){
                 $id_bk = $dataHa[0]['id'];


                 $formatted_dataHa = array();
                 $formatted_dataHa['corso'][0] = translateFN('Corso');
                 $formatted_dataHa['data'][0] = translateFN('Data');
                 //$formatted_dataHa['ora'][0] = translateFN('Ora');
                 $formatted_dataHa['titolo'][0] = translateFN('Nodo');
                 $formatted_dataHa['descrizione'][0] =  translateFN('Tag');

                 $formatted_dataHa['corso'][1] = $dataHa[0]['corso'];
                 $formatted_dataHa['data'][1] = $dataHa[0]['data'];
                 //$formatted_dataHa['ora'][1] = $dataHa[0]['ora'];
                 $formatted_dataHa['titolo'][1] = $dataHa[0]['titolo'];
                 $formatted_dataHa['descrizione'][1] =  $dataHa[0]['descrizione'];

                 $t = new Table();
                 $t->initTable(0,'default','0','0','100%','','','','',0,0);
                 $t->setTable($formatted_dataHa,translateFN("Dettaglio tag"),'');
                 $res = $t->getTable();
                 return $res;

        }

        function format_as_tags($dataAr){
          $dh = $GLOBALS['dh'];
          $debug = $GLOBALS['debug'];
          $reg_enabled = $GLOBALS['reg_enabled'];
          $id_profile = $GLOBALS['id_profile'];
          $sess_id_user = $SESSION['sess_id_user'];
          if (!is_array($dataAr) || (!count($dataAr))){
            $res = translateFN("Nessuna tag");
            // header("Location: $error?err_msg=$msg");
          } else {
            $formatted_dataHa = array();
            $k=-1;
            foreach ($dataAr as $bookmark)  {
              $id_bk = $bookmark[0];
              $id_node = $bookmark[1];
              $date =   $bookmark[2];
              $author_id=   $bookmark[4];
              $node = $dh->get_node_info($id_node);
              $title = $node['name'];
              $icon  = $node['icon'];
              $description =   $bookmark[3];
              $authorHa = $dh->_get_user_info($author_id);
              $author_uname = $authorHa['username'];
              $k++;
              $formatted_dataHa[$k]['autore'] = "<a href=\"tags.php?op=list_by_user&id_auth=$author_id\">$author_uname</a>";
              $formatted_dataHa[$k]['data'] =  ts2dFN($date);
              $formatted_dataHa[$k]['tag'] = "<a href=\"tags.php?op=list_by_tag&id_bk=$id_bk\"><img src=\"img/check.png\" border=0>&nbsp;$description</a>";

              if (is_array($dh->get_tutor($author_id))) { // tag del tutor differenziate ??
                $formatted_dataHa[$k]['id_nodo'] = "<a href=\"view.php?id_node=$id_node\"><img src=\"img/$icon\" border=0> $title</a> (".translateFN("Tutor").")";
//vito 13 gennaio 2009
//                if ($id_profile == AMA_TYPE_TUTOR){
//                  $formatted_dataHa[$k]['del'] =  "<a href=\"tags.php?op=delete&id_bk=$id_bk\">
//                  <img src=\"img/delete.png\" name=\"del_icon\" border=\"0\"
//                  alt=\"" . translateFN("Elimina") . "\"></a>";
//                  $formatted_dataHa[$k]['edit'] =  "<a href=\"tags.php?op=edit&id_bk=$id_bk\">
//                  <img src=\"img/edit.png\" name=\"edit_icon\" border=\"0\"
//                  alt=\"" . translateFN("Edit") . "\"></a>";
//                } else {
//                  $formatted_dataHa[$k]['del'] = "-";
//                  $formatted_dataHa[$k]['edit'] = "-";
//                }

              } else {
                $formatted_dataHa[$k]['nodo'] = "<a href=\"view.php?id_node=$id_node\"><img src=\"img/$icon\" border=0> $title</a>";
// vito 13 gennaio 2009
//                if ($reg_enabled AND $author_id == $sess_user_id){
//                  $formatted_dataHa[$k]['del'] =  "<a href=\"tags.php?op=delete&id_bk=$id_bk\">
//                  <img src=\"img/delete.png\" name=\"del_icon\" border=\"0\"
//                  alt=\"" . translateFN("Elimina") . "\"></a>";
//                  $formatted_dataHa[$k]['edit'] =  "<a href=\"tags.php?op=edit&id_bk=$id_bk\">
//                  <img src=\"img/edit.png\" name=\"edit_icon\" border=\"0\"
//                  alt=\"" . translateFN("Edit") . "\"></a>";
//                } else {
//                  $formatted_dataHa[$k]['del'] = "-";
//                  $formatted_dataHa[$k]['edit'] = "-";
//                }
              }
              $formatted_dataHa[$k]['zoom'] =  "<a href=\"tags.php?op=zoom&id_bk=$id_bk\">
              <img src=\"img/zoom.png\" name=\"zoom_icon\" border=\"0\"
              alt=\"" . translateFN("Zoom") . "\"></a>";
            }

            $t = new Table();
            $t->initTable('','default','2','1','100%','','','','',1,0);
            $t->setTable($formatted_dataHa,translateFN("Tag"),'');
            $res = $t->getTable();
          }
          return $res;
          
        }


}
?>
