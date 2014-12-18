<?php
//
// +----------------------------------------------------------------------+
// | ADA version 1.8                                                              |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2006 Lynx                                         |
// +----------------------------------------------------------------------+
// |                                                                                         |
// |              HTML ADMIN OUTPUT                                       |
// |                                                                                         |
// |                                                                                         |
// |                                                                                         |
// |                                                                                         |
// |                                                                                         |
// +----------------------------------------------------------------------+
// | Author: Marco Benini                                                       |
// |                                                                                          |
// +----------------------------------------------------------------------+
//

class htmladmoutput{



    // Get & Set Propiet&agrave;


    // Metodi

    // Funzione back ad una determinata pagina
    function go_file_back($file_back,$label){
        // inizializzazione variabili
        $str = "";

        if(!$label) {
           $label = "Home";
        }

        $str = "<p>&nbsp;</p>";//</div>";
        $str .= "<p align=\"center\"><a href=\"$file_back\">$label</a></p>";
        return $str ;
    }


/*
    // Funzione scrittura form di login per l'autenticazione
    function form_login($file_action){
        // inizializzazione variabili
        $str = "";

        $str .= "<form method=\"post\" action=\"$file_action\">";
        $str .= "<p>username: <input type=\"text\" name=\"username\" size=\"20\" maxlength=\"40\"></p>";
        $str .= "<p>password: <input type=\"text\" name=\"password\" size=\"20\" maxlength=\"40\" ></p> ";
        $str .= "<input type=\"submit\" name=\"Submit\" value=\"Submit\">";
        $str .= "</form>" ;

        return $str ;
    }


*/
    // Funzione scrittura form aggiungi amministratore
    function form_addadmin($file_action,$file_back){
        // inizializzazione variabili
        $str = "";

        // nome
        $fields["add"][]="admin[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="admin[cognome]";
        $names["add"][]="Cognome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="admin[email]";
        $names["add"][]="e-mail";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="admin[username]";
        $names["add"][]="username";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password
        $fields["add"][]="admin[password]";
        $names["add"][]="password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password check
        $fields["add"][]="admin[passwordcheck]";
        $names["add"][]="ripeti password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="admin[telefono]";
        $names["add"][]="telefono";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=12;

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,"Home");

        return $str ;
    }



    // Funzione scrittura form aggiungi autore
    function form_addauthor($file_action,$file_back){
        // inizializzazione variabili
        $str = "";

        // nome
        $fields["add"][]="autore[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="autore[cognome]";
        $names["add"][]="Cognome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="autore[email]";
        $names["add"][]="e-mail";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="autore[username]";
        $names["add"][]="username";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password
        $fields["add"][]="autore[password]";
        $names["add"][]="password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password check
        $fields["add"][]="autore[passwordcheck]";
        $names["add"][]="ripeti password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="autore[telefono]";
        $names["add"][]="telefono";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=12;

        // tariffa
        $fields["add"][]="autore[tariffa]";
        $names["add"][]="tariffa";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=12;

        // profilo
        $fields["add"][]="autore[profilo]";
        $names["add"][]="profilo";
        $edittypes["add"][]="textarea";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,"Home");

        return $str ;
    }



    // Funzione scrittura form aggiungi studente
    function form_addstudent($file_action){
        // inizializzazione variabili
        $str = "";

        // nome
        $fields["add"][]="student[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="student[cognome]";
        $names["add"][]="Cognome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="student[email]";
        $names["add"][]="e-mail";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="student[username]";
        $names["add"][]="username";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password
        $fields["add"][]="student[password]";
        $names["add"][]="password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password check
        $fields["add"][]="student[passwordcheck]";
        $names["add"][]="ripeti password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="student[telefono]";
        $names["add"][]="telefono";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=12;

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        //$str .= $this->go_file_back($file_back,"Home");

        return $str ;
    }



    // Funzione scrittura form aggiungi tutor
    function form_addtutor($file_action,$file_back){
        // inizializzazione variabili
        $str = "";

        // nome
        $fields["add"][]="tutor[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="tutor[cognome]";
        $names["add"][]="Cognome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="tutor[email]";
        $names["add"][]="e-mail";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="tutor[username]";
        $names["add"][]="username";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password
        $fields["add"][]="tutor[password]";
        $names["add"][]="password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password check
        $fields["add"][]="tutor[passwordcheck]";
        $names["add"][]="ripeti password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="tutor[telefono]";
        $names["add"][]="telefono";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=12;

        // tariffa
        $fields["add"][]="tutor[tariffa]";
        $names["add"][]="tariffa";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=12;

        // profilo
        $fields["add"][]="tutor[profilo]";
        $names["add"][]="profilo";
        $edittypes["add"][]="textarea";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,"Home");

        return $str ;
    }



    // Funzione scrittura form aggiungi course
  function form_addcourse($file_action,$file_back,$authors_ha,$is_author=0){

    $root_dir = $GLOBALS['root_dir'];
    // inizializzazione variabili
    $str = "";

    // nome
    $fields["add"][]="course[nome]";
    $names["add"][]="Nome";
    $edittypes["add"][]="text";
    $necessary["add"][]="true";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]=32;

    // titolo
    $fields["add"][]="course[titolo]";
    $names["add"][]="Titolo";
    $edittypes["add"][]="text";
    $necessary["add"][]="true";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]=128;

    // autore
    if ($is_author){
      $fields["add"][]="course[id_autore]";
      $names["add"][]="Autore";
      $edittypes["add"][]="hidden";
      $necessary["add"][]="true";
      $values["add"][]=$authors_ha[0][0];
      $options["add"][]="";
      $maxsize["add"][]="";
    }
    else {
      $labels_sel = "";
      $val_sel = "";
      $max = count($authors_ha) ;
      for ($i=0; $i<$max; $i++){
        $labels_sel .= ":". $authors_ha[$i][1] ." ". $authors_ha[$i][2] ." ";
        if($i != ($max-1)) {
          $val_sel .= $authors_ha[$i][0] .":" ;
        }
        else{
          $val_sel .= $authors_ha[$i][0] ;
        }
      }

      $fields["add"][]="course[id_autore]";
      $names["add"][]="Autore $labels_sel";
      $edittypes["add"][]="select";
      $necessary["add"][]="true";
      $values["add"][]="";
      $options["add"][]="$val_sel";
      $maxsize["add"][]="";
    }

    // descrizione
    $fields["add"][]="course[descr]";
    $names["add"][]="descrizione";
    $edittypes["add"][]="textarea";
    $necessary["add"][]="";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]="";

    // data creazione
    if ($is_author){
      $gma = today_dateFN();
      $fields["add"][]="course[d_create]";
      $names["add"][]="data creazione (GG/MM/AAAA)";
      $edittypes["add"][]="hidden";
      $necessary["add"][]="";
      $values["add"][]=$gma;
      $options["add"][]="";
      $maxsize["add"][]=12;
    }
    else 	{
      $fields["add"][]="course[d_create]";
      $names["add"][]="data creazione (GG/MM/AAAA)";
      $edittypes["add"][]="text";
      $necessary["add"][]="";
      $values["add"][]="";
      $options["add"][]="";
      $maxsize["add"][]=12;
    }

    // data pubblicazione
    if (!$is_author){
      $fields["add"][]="course[d_publish]";
      $names["add"][]="data pubblicazione";
      $edittypes["add"][]="text";
      $necessary["add"][]="";
      $values["add"][]="";
      $options["add"][]="";
      $maxsize["add"][]=12;
    }
    // media path
    $fields["add"][]="course[media_path]";
    $names["add"][]="media path";
    $edittypes["add"][]="text";
    $necessary["add"][]="";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]=50;


    $temp_dir_base = $root_dir."/layout/";
    $layout_OK = dirTree($temp_dir_base);
    $val_sel = "";
    $max = count($layout_OK) ;
    for ($i=0; $i<$max; $i++){
      //if (($layout_OK[$i]!='.') && ($layout_OK[$i]!='..'))
      if($i != ($max-1)) {
        $val_sel .= $layout_OK[$i] .":" ;
      }
      else{
        $val_sel .= $layout_OK[$i] ;
      }
    }
    // $layout_OK [] = "";
    $fields["add"][]="course[layout]";
    $names["add"][]="Layout: $val_sel";
    $edittypes["add"][]="select";
    $necessary["add"][]="";
    $values["add"][]=$course['layout'];
    $options["add"][]=$val_sel;
    $maxsize["add"][]=20;

    // id_nodo_toc
    $fields["add"][]="course[id_nodo_toc]";
    $names["add"][]="id_nodo_toc";
    $edittypes["add"][]="text";
    $necessary["add"][]="";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]="";

    // id_nodo_iniziale
    $fields["add"][]="course[id_nodo_iniziale]";
    $names["add"][]="id_nodo_iniziale";
    $edittypes["add"][]="text";
    $necessary["add"][]="";
    $values["add"][]="";
    $options["add"][]="";
    $maxsize["add"][]="";

    // file XML possibili

    // vito, 15 giugno 2009
    $message = "";
    if ($is_author && (int)$_GET['modello'] == 1) {
      $course_models = read_dir(AUTHOR_COURSE_PATH_DEFAULT,'xml');

        /*
         * vito, 30 mar 2009
         * Decomment the following lines (those after the comment SEARCH INTO AUTHOR'S UPLOAD DIR)
         * to enable searching for course models into author's
         * upload dir in addition to those stored into AUTHOR_COURSE_PATH_DEFAULT dir.
         *
         * It is necessary to handle this change in admin/author_course_xml_to_db_process.php:
         * now it builds the root dir relative position for the given xml file by prefixing it
         * with AUTHOR_COURSE_PATH_DEFAULT. If we allow searching into the author's upload dir
         * we have to avoid adding this prefix because the filename will be already a root dir
         * relative filename.
         *
         * If an author wants to create a new course based on an existing course model,
         * show him the course models in the course model repository, (common to all authors) and
         * the ones he has uploaded, stored in UPLOAD_PATH/<authorid>.
         * Otherwise, if an admin wants to create a course from an existing model, show him only the
         * course models stored in the course model repository.
         */
        // SEARCH INTO AUTHOR'S UPLOAD DIR
        //        if (!is_array($course_models)) {
        //          $course_models = array();
        //        }
        //        if ($is_author) {
        //	      $authors_uploaded_files = UPLOAD_PATH.$authors_ha[0][0];
        //	      $authors_course_models  = read_dir($authors_uploaded_files, 'xml');
        //	      $course_models = array_merge($course_models, $authors_course_models);
        //        }

      $num_files = 0;
      if (is_array($course_models)) {
        $num_files = sizeof($course_models);
      }


      $val_sel='';
      $label_sel='';
      if ($num_files>0) {
        foreach ($course_models as $value) {
          //vito, 30 mar 2009
          // SEARCH INTO AUTHOR'S UPLOAD DIR
          //$val_sel.=$value['path_to_file'].":";
          $val_sel.=$value['file'].":";

          $label_sel.=":".$value['file'];
        }
        $val_sel=substr($val_sel,0,-1);
        // vito, 12 giugno 2009
        //}
        //if ($is_author AND ((int)$_GET['modello'])==1) {
          $fields["add"][]="course[xml]";
          $names["add"][]="XML".$label_sel;
          $edittypes["add"][]="select";
          $necessary["add"][]="";
          $values["add"][]="";
          $options["add"][]=$val_sel;
          $maxsize["add"][]="";
        //}
      }
      else {
        $message = translateFN("Non sono presenti modelli di corso. E' comunque possibile creare un corso vuoto.");
      }
    }

    // creazione del form
    $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

    // scrittura stringa back
    //  $str .= $this->go_file_back($file_back,"Home");

    return $message.$str ;
  }



    // Funzione administrators list - table output
    function list_admin($file_back,$field_list_ar,$users){
        // definizione variabili
        $str = "";
        $n = 0;

        // administrators number from database query
        $n = count($users);
        if($n>0){

            // creazione della tabella
            $tb = new Table() ;
            // setting delle caratteristiche della tabella
//            $tb->initTable($border= '1',$align="center",$cellspacing='0',$cellpadding='1',            $width= '60%',$col1="black", $bcol1="red",$col2="black",$bcol2="yellow");
            $tb->initTable('0','center','0','0','','','','','','0','0');

            $i = 0;
            while($i<$n){
                $id = $users[$i][0];
                $j = 1 ; // reset dei valori
                $val = ""; // reset dei valori
                while ( $j <= count($field_list_ar) ){
                    $val .=  $users[$i][$j++] . " " ;
                }
                $i++;
                // gestione link azioni

                $delete_img_link = "<a href=\"#\" onclick=\"if (confirm ('". translateFN("cancellare il corso?") . "')) window.location ='delete_admin.php?id=$id'\"><img src=\"img/delete.png\" border=\"0\" alt=\"rimuovi\"></a>";
                $zoom_img_link = "<a href=\"zoom_admin.php?id=$id\"><img src=\"img/zoom.png\" border=\"0\" alt=\"zoom in\"></a>";
                $edit_img_link = "<a href=\"edit_admin.php?id=$id\"><img src=\"img/edit.png\" border=\"0\" alt=\"edit\"></a>";

                $val2 = "$delete_img_link $zoom_img_link $edit_img_link";

                $data[] =  array(translateFN("id")=>$id,translateFN("nome e cognome")=>trim($val),translateFN("azioni")=>$val2);
            }

            $tb->setTable($data,$caption=translateFN("Lista amministratori"),$summary=translateFN("Tabella"));
            $str .= $tb->getTable();

        }else{
            $str = translateFN("<p>nessun risultato</p>");
        }

        // scrittura stringa back
       // $str .= $this->go_file_back($file_back,"Home");

        return $str ;
    }



    // Funzione students list - table output
    function list_student($file_action,$file_back,$field_list_ar,$users,$clause="",$clause_1="", $clause_2="", $courses_ha){
        // definizione variabili


        $str = "";

        // students number from database query
        $n = count($users);

        // FORM di ricerca
        // cognome
        $desc = translateFN("Cerca per cognome");
        $fields["add"][]="clause";
        $names["add"][]=$desc;
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="$clause";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // user name
        $desc = translateFN("Cerca per username");
        $fields["add"][]="clause_1";
        $names["add"][]=$desc;
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]="$clause_1";
        $options["add"][]="";
        $maxsize["add"][]=16;

        // corsi ai quali e' iscritto lo studente
        $labels_sel = "";
        $val_sel = "";
	$max = 0;
	if (is_array($courses_ha)) $max = count($courses_ha);
        for ($i=0; $i<$max; $i++){
            $labels_sel .= ":". $courses_ha[$i][1] ." - id classe ". $courses_ha[$i][2] ." - ";
            // echo $labels_sel;
            $id_classe = $courses_ha[$i][2];

            // Descrizione per totale studenti del corso
            if ($id_classe == $clause_2) $titolo_corso_tab = $courses_ha[$i][1] ." - id classe ". $courses_ha[$i][2];

            if($i != ($max-1)){
                $val_sel .= $id_classe .":";
            }else{
                $val_sel .= $id_classe;
            }


        }
        // echo $val_sel;
        $fields["add"][]="clause_2";
        $names["add"][]= translateFN("Cerca per corso") . " " . $labels_sel;
        $edittypes["add"][]="select";
        $necessary["add"][]="";
        // $values["add"][]="";
        $values["add"][]="";
        $options["add"][]="$val_sel";
        $maxsize["add"][]="";


        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        $n = 0;

        // students number from database query
        $n = count($users);
        if($n>0){

            // Verifica il numero di elementi dell'array del corso
            // Se sono 4 la tabella deve includere anche lo status dell'iscrizione
            // RICERCA PER CORSO
            $ric_corso = 0;
            $tmp = count($users[0]);
            if ($tmp  > 4)
                    $ric_corso = 1;
            //

            // creazione della tabella
            $tb = new Table() ;
            // setting delle caratteristiche della tabella
//            $tb->initTable($border= '1',$align="center",$cellspacing='0',$cellpadding='1',            $width= '60%',$col1="black", $bcol1="red",$col2="black",$bcol2="yellow");
            $tb->initTable('0','center','1','0','','','','','','1','0');
            $i = 0;
            while($i<$n){
                $id = $users[$i][0];
                $j = 1 ; // reset dei valori
                $val = ""; // reset dei valori
                while ( $j <= '2' ){
                //while ( $j <= count($field_list_ar) ){
                    $val .=  $users[$i][$j++] . " " ;
                }
                $val1 = $users[$i][3];
                if ($ric_corso==1)
                    $val2 = $users[$i][4];
                $i++;
                // gestione link azioni
                if (isset($val2)) {
                       $delete_img_link = ""; // se lo studente è iscritto ad un corso non può cancellarlo
                } else {
                       $delete_img_link = "<a href=\"#\" onclick=\"if (confirm ('". translateFN("cancellare l\'utente?") . "')) window.location = 'delete_student.php?id=$id'\"><img src=\"img/delete.png\" border=\"0\"></a>";
                }
                $zoom_img_link = "<a href=\"zoom_student.php?id=$id\"><img src=\"img/zoom.png\" border=\"0\" alt=\"zoom in\"></a>";
                $edit_img_link = "<a href=\"edit_student.php?id=$id\"><img src=\"img/edit.png\" border=\"0\" alt=\"edit\"></a>";

                $val3 = "$delete_img_link $zoom_img_link $edit_img_link";

                if ($ric_corso==1) {
                        $data[] =  array(
                                         translateFN("id")=>$id,
                                         translateFN("nome e cognome")=>trim($val),
                                         translateFN("username")=>trim($val1),
                                         translateFN("status")=>trim($val2),
                                           translateFN("azioni")=>$val3
                                       );
                }else{
                        $data[] =  array(
                                         translateFN("id")=>$id,
                                         translateFN("nome e cognome")=>trim($val),
                                         translateFN("username")=>trim($val1),
                                         translateFN("azioni")=>$val3
                                         );
                }

            }
            // genera descrizione totale studenti
            if ($ric_corso == 1) {
                    $caption = "Corso $titolo_corso_tab - " . translateFN("Totale studenti") . ": "  . $n;
            }else{
                    $caption = translateFN("Totale studenti") . ": " . $n;
            }
            // $tb->setTable($data,$caption=translateFN("Lista studenti"),$summary=translateFN("Tabella"));
            $tb->setTable($data,$caption,$summary=translateFN("Tabella"));
            $str .= $tb->getTable();

        }else{
            $str .= "<br><strong>".translateFN("Nessun risultato")."</strong>";
        }

        // scrittura stringa back
        // $str .= $this->go_file_back($file_back,translateFN("Home"));

        return $str ;
    }



    // Funzione authors list - table output
    function list_authors($file_action,$file_back,$field_list_ar,$authors,$clause){
        // definizione delle variabili
        $str = "";
        $n = 0;

        // authors number from database query
        $n = count($authors);

        // FORM di ricerca
        $fields["add"][]="clause";
        $names["add"][]="Cerca per tariffa";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="$clause";
        $options["add"][]="";
        $maxsize["add"][]=12;

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // LISTA autori
        if($n>0){
            // creazione della tabella
            $tb = new Table() ;

            // setting delle caratteristiche della tabella
//            $tb->initTable($border= '1',$align="center",$cellspacing='0',$cellpadding='1',            $width= '68%',$col1="black", $bcol1="red",$col2="black",$bcol2="yellow");
            $tb->initTable('0','center','0','0','','','','','','0','0');

            $i = 0;
            while($i<$n){
                $id = $authors[$i][0];
                $j = 1 ; // reset dei valori
                $val = ""; // reset dei valori
                while ( $j <= count($field_list_ar) ){
                    $val .=  $authors[$i][$j++] . " " ;
                }
                $i++;
                // gestione link azioni
                $delete_img_link = "<a href=\"#\" onclick=\"if (confirm ('". translateFN("cancellare l\'utente?") . "')) window.location = 'delete_author.php?id=$id'\"><img src=\"img/delete.png\" border=\"0\" alt=\"".translateFN('Elimina')."\"></a>";
                $zoom_img_link = "<a href=\"zoom_author.php?id=$id\"><img src=\"img/zoom.png\" border=\"0\" alt=\"".translateFN('Zoom in')."\"></a>";
                $edit_img_link = "<a href=\"edit_author.php?id=$id\"><img src=\"img/edit.png\" border=\"0\" alt=\"".translateFN('Modifica')."\"></a>";

                $val2 = "$delete_img_link $zoom_img_link $edit_img_link";

                $data[] =  array(translateFN("id")=>$id,translateFN("nome e cognome")=>trim($val),translateFN("azioni")=>$val2);
            }

            $tb->setTable($data,$caption=translateFN("Lista autori"),$summary=translateFN("Tabella"));
            $str .= $tb->getTable();

        }else{
            $str = translateFN("<p>nessun risultato</p>");
        }

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Home"));

        return $str ;
    }



    // Funzione courses list - table output
    function list_courses($file_action,$file_back,$field_list_ar,$courses,$key){
        $dh = $GLOBALS['dh'];
        // inizializzazione variabili

        $str = "";
        $n = 0;

        // authors number from database query
        $n = count($courses);

        // FORM di ricerca
        $fields["add"][]="key";
        $names["add"][]="Cerca corso";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="$key";
        $options["add"][]="";
        $maxsize["add"][]=12;

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // LISTA corsi
        if($n>0){
            // creazione della tabella
            $tb = new Table() ;

            // setting delle caratteristiche della tabella
//            $tb->initTable($border= '1',$align="center",$cellspacing='0',$cellpadding='1',            $width= '68%',$col1="black", $bcol1="red",$col2="black",$bcol2="yellow");
            $tb->initTable('0','center','0','0','','','','','','0','0');

            $i = 0;
            while($i<$n){
                $id = $courses[$i][0];
                $j = 1 ; // reset dei valori
                $val = ""; // reset dei valori
                while ( $j <= count($field_list_ar) ){
                    $val .=  $courses[$i][$j++] . " " ;
                }
                $i++;
                // gestione link per azioni
                if ( $dh-> course_has_instances($id)){
                      //$delete_img_link = "&nbsp;-&nbsp;";
                      // vito, 17 giugno 2009
                  $delete_img_link = '<img src="img/dis_delete.png" alt="zoom in" border="0">';
                } else {

                      $delete_img_link = "<a href=\"#\" onclick=\"if (confirm ('". translateFN("cancellare il corso?") . "')) window.location ='delete_course.php?id=$id'\"><img src=\"img/delete.png\" alt=\"rimuovi\" border=\"0\"></a>";
                }
                $zoom_img_link = "<a href=\"zoom_course.php?id=$id\"><img src=\"img/zoom.png\" alt=\"zoom in\" border=\"0\"></a>";
                $edit_img_link = "<a href=\"edit_course.php?id=$id\"><img src=\"img/edit.png\" alt=\"edit\" border=\"0\"></a>";
                $instance = "<a href=\"course_instance.php?id_corso=$id\"><img src=\"img/student.png\" alt=\"classi\" border=\"0\"></a>";
                $xml_to_db = "<a href=\"course_xml_to_db.php?id=$id\"><img src=\"img/xml.png\" alt=\"XML\" border=\"0\"></a>";

                $val2 = "$delete_img_link $zoom_img_link $edit_img_link $xml_to_db $instance";

                // output tabella: con e senza id del corso visualizzato
                // $data[] =  array(translateFN("id")=>$id,translateFN("titolo")=>trim($val),translateFN("azioni")=>$val2);
                $data[] =  array(translateFN("titolo")=>trim($val),translateFN("azioni")=>$val2);
             }

            $tb->setTable($data,$caption=translateFN("Lista corsi"),$summary=translateFN("Tabella"));
            $str .= $tb->getTable();

        }else{
            $str = translateFN("nessun risultato");
        }

        // scrittura stringa back
       // $str .= $this->go_file_back($file_back,translateFN("Home"));

        return $str ;
    }


    // Funzione tutors list - table output
    function list_tutors($file_action,$file_back,$field_list_ar,$users,$clause){
        // inizializzazione variabili
        $str = "";

        // authors number from database query
        $n = count($users);

        // FORM di ricerca
        $fields["add"][]="clause";
        $names["add"][]=translateFN("Cerca nel profilo");
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]="$clause";
        $options["add"][]="";
        $maxsize["add"][]=12;

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // LISTA tutor
        if($n>0){
            // creazione della tabella
            $tb = new Table() ;

            // setting delle caratteristiche della tabella
//            $tb->ininitTable($border= '1',$align="center",$cellspacg='0',$cellpadding='1',            $width= '68%',$col1="black", $bcol1="red",$col2="black",$bcol2="yellow");
            $tb->initTable('0','center','0','0','','','','','','0','0');

            $i = 0;
            while($i<$n){
                $id = $users[$i][0];
                $j = 1 ; // reset dei valori
                $val = ""; // reset dei valori
                while ( $j <= count($field_list_ar) ){
                    $val .=  $users[$i][$j++] . " " ;
                }
                $i++;
                // gestione link per azioni
                $delete_img_link = "<a href=\"#\" onclick=\"if (confirm ('". translateFN("cancellare il tutor?") . "')) window.location ='delete_tutor.php?id=$id'\"><img src=\"img/delete.png\" border=\"0\" alt=\"rimuovi\"></a>";
                $zoom_img_link = "<a href=\"zoom_tutor.php?id=$id\"><img src=\"img/zoom.png\" border=\"0\" alt=\"zoom in\"></a>";
                $edit_img_link = "<a href=\"edit_tutor.php?id=$id\"><img src=\"img/edit.png\" border=\"0\" alt=\"edit\"></a>";

                $val2 = "$delete_img_link $zoom_img_link $edit_img_link";

                $data[] =  array(translateFN("id")=>$id,translateFN("nome e cognome")=>trim($val),translateFN("azioni")=>$val2);
            }

            $tb->setTable($data,$caption=translateFN("Lista tutor"),$summary=translateFN("Tabella"));
            $str .= $tb->getTable();

        }else{
            $str = "<p>".translateFN("nessun risultato")."</p>";
        }

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Home"));

        return $str ;
    }



    // Funzione admin modify form - table output
    function form_editadmin($file_action,$file_back,$admin,$id){
        // inizializzazione variabili
	$http_root_dir =   $GLOBALS['http_root_dir'];
        $root_dir =   $GLOBALS['root_dir'];
        $duplicate_dir_structure =   $GLOBALS['duplicate_dir_structure'];
        $str = "";

        // nome
        $fields["add"][]="admin[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$admin['nome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="admin[cognome]";
        $names["add"][]="Cognome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$admin['cognome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="admin[email]";
        $names["add"][]="e-mail";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$admin['email'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="admin[username]";
        $names["add"][]="username";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$admin['username'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password
        $fields["add"][]="admin[password]";
        $names["add"][]="password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password check
        $fields["add"][]="admin[passwordcheck]";
        $names["add"][]="ripeti password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="admin[telefono]";
        $names["add"][]="telefono";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$admin['telefono'];
        $options["add"][]="";
        $maxsize["add"][]=12;
/*
        // layout
        $fields["add"][]="admin[layout]";
        $names["add"][]="layout";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$student['layout'];
        $options["add"][]="";
        $maxsize["add"][]=20;
*/
/*
if ($duplicate_dir_structure) {
                $temp_dir_base = $root_dir."/templates/main";
                $layout_OK = dirTree($temp_dir_base);
                // $val_sel = "";

                $max = count($layout_OK) ;
                for ($i=0; $i<$max; $i++){
                    if($i != ($max-1)){
                        $val_sel .= $layout_OK[$i] .":" ;
                    }else{
                        $val_sel .= $layout_OK[$i] ;
                        }
                }

                // $layout_OK [] = "";
//                print_r($layout_OK);

                $fields["add"][]="admin[layout]";
//                 $names["add"][]="layout";
                $names["add"][]="Layout: $val_sel";
                $edittypes["add"][]="select";
                $necessary["add"][]="";
                 $values["add"][]=$admin['layout'];
                $options["add"][]=$val_sel;
                $maxsize["add"][]=20;

        }else{
                $fields["add"][]="admin[layout]";
                $names["add"][]="layout";
                $edittypes["add"][]="text";
                $necessary["add"][]="";
                $values["add"][]=$admin['layout'];
                $options["add"][]="";
                $maxsize["add"][]=20;
        }
*/
        // id
        $fields["add"][]="id";
        $names["add"][]="id";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$id";
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);


        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione author modify form - table output
    function form_editauthor($file_action,$file_back,$autore,$id,$admin){
        // inizializzazione variabili
	$http_root_dir =   $GLOBALS['http_root_dir'];
        $root_dir =   $GLOBALS['root_dir'];
        $duplicate_dir_structure =   $GLOBALS['duplicate_dir_structure'];
        $str = "";

        // nome
        $fields["add"][]="autore[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$autore['nome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="autore[cognome]";
        $names["add"][]="Cognome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$autore['cognome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="autore[email]";
        $names["add"][]="e-mail";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$autore['email'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="autore[username]";
        $names["add"][]="username";
        if ($admin==1) {
                $edittypes["add"][]="text";
        } else {
                // disabled: users cannot change their usernames
                $edittypes["add"][]="hidden";
        }
        $necessary["add"][]="";
        $values["add"][]=$autore['username'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password
        $fields["add"][]="autore[password]";
        $names["add"][]="password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password check
        $fields["add"][]="autore[passwordcheck]";
        $names["add"][]="ripeti password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="autore[telefono]";
        $names["add"][]="telefono";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$autore['telefono'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // tariffa
        $fields["add"][]="autore[tariffa]";
        $names["add"][]="tariffa";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$autore['tariffa'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // profilo
        $fields["add"][]="autore[profilo]";
        $names["add"][]="profilo";
        $edittypes["add"][]="textarea";
        $necessary["add"][]="";
        $values["add"][]=$autore['profilo'];
        $options["add"][]="";
        $maxsize["add"][]="";

/*
        // layout
        $fields["add"][]="autore[layout]";
        $names["add"][]="layout";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$autore['layout'];
        $options["add"][]="";
        $maxsize["add"][]=20;
*/
 if ($duplicate_dir_structure) {
                $temp_dir_base = $root_dir."/templates/main";
                $layout_OK = dirTree($temp_dir_base);
                // $val_sel = "";

                $max = count($layout_OK) ;
                for ($i=0; $i<$max; $i++){
                    if($i != ($max-1)){
                        $val_sel .= $layout_OK[$i] .":" ;
                    }else{
                        $val_sel .= $layout_OK[$i] ;
                        }
                }

                $fields["add"][]="autore[layout]";
//                 $names["add"][]="layout";
                $names["add"][]="Layout: $val_sel";
                $edittypes["add"][]="select";
                $necessary["add"][]="";
                 $values["add"][]=$autore['layout'];
                $options["add"][]=$val_sel;
                $maxsize["add"][]=20;

        }else{
                $fields["add"][]="autore[layout]";
                $names["add"][]="layout";
                $edittypes["add"][]="text";
                $necessary["add"][]="";
                $values["add"][]=$autore['layout'];
                $options["add"][]="";
                $maxsize["add"][]=20;
        }

        // id
        $fields["add"][]="id";
        $names["add"][]="id";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$id";
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione tutor modify tutor - table output
    function form_edittutor($file_action,$file_back,$tutor,$id,$admin){
        // inizializzazione variabili
	$http_root_dir =   $GLOBALS['http_root_dir'];
        $root_dir =   $GLOBALS['root_dir'];
        $duplicate_dir_structure =   $GLOBALS['duplicate_dir_structure'];

        $str = "";

        // nome
        $fields["add"][]="tutor[nome]";
        $names["add"][]=translateFN("Nome");
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$tutor['nome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="tutor[cognome]";
        $names["add"][]=translateFN("Cognome");
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$tutor['cognome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="tutor[email]";
        $names["add"][]=translateFN("e-mail");
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$tutor['email'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="tutor[username]";
        $names["add"][]=translateFN("username");
        if ($admin==1) {
                $edittypes["add"][]="text";
        } else {
                // disabled: users cannot change their usernames
                $edittypes["add"][]="hidden";
        }
        $necessary["add"][]="";
        $values["add"][]=$tutor['username'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password
        $fields["add"][]="tutor[password]";
        $names["add"][]=translateFN("password");
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password check
        $fields["add"][]="tutor[passwordcheck]";
        $names["add"][]=translateFN("ripeti password");
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="tutor[telefono]";
        $names["add"][]=translateFN("telefono");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$tutor['telefono'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // tariffa
        $fields["add"][]="tutor[tariffa]";
        $names["add"][]=translateFN("tariffa");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$tutor['tariffa'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // profilo
        $fields["add"][]="tutor[profilo]";
        $names["add"][]=translateFN("profilo");
        $edittypes["add"][]="textarea";
        $necessary["add"][]="";
        $values["add"][]=$tutor['profilo'];
        $options["add"][]="";
        $maxsize["add"][]="";

        // layout
/*
        $fields["add"][]="tutor[layout]";
        $names["add"][]="layout";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$tutor['layout'];
        $options["add"][]="";
        $maxsize["add"][]=20;
*/
/*        // layout
        if ($duplicate_dir_structure) {
                $temp_dir_base = $root_dir."/templates/main";
                $layout_OK = dirTree($temp_dir_base);
                // $val_sel = "";

                $max = count($layout_OK) ;
                for ($i=0; $i<$max; $i++){
                    if($i != ($max-1)){
                        $val_sel .= $layout_OK[$i] .":" ;
                    }else{
                        $val_sel .= $layout_OK[$i] ;
                        }
                }

                // $layout_OK [] = "";
//                print_r($layout_OK);

                $fields["add"][]="tutor[layout]";
//                 $names["add"][]="layout";
                $names["add"][]="Layout: $val_sel";
                $edittypes["add"][]="select";
                $necessary["add"][]="";
                 $values["add"][]=$tutor['layout'];
                $options["add"][]=$val_sel;
                $maxsize["add"][]=20;

        }else{
                $fields["add"][]="tutor[layout]";
                $names["add"][]="layout";
                $edittypes["add"][]="text";
                $necessary["add"][]="";
                $values["add"][]=$tutor['layout'];
                $options["add"][]="";
                $maxsize["add"][]=20;
        }
*/
        // id
        $fields["add"][]="id";
        $names["add"][]="id";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$id";
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }

       // Funzione tutor modify switcher - table output
    function form_editswitcher($file_action,$file_back,$switcher,$id,$admin){
        // inizializzazione variabili
	    $http_root_dir =   $GLOBALS['http_root_dir'];
        $root_dir =   $GLOBALS['root_dir'];
        $duplicate_dir_structure =   $GLOBALS['duplicate_dir_structure'];

        $str = "";

        // nome
        $fields["add"][]="switcher[nome]";
        $names["add"][]=translateFN("Nome");
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$switcher['nome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="switcher[cognome]";
        $names["add"][]=translateFN("Cognome");
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$switcher['cognome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="switcher[email]";
        $names["add"][]=translateFN("e-mail");
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$switcher['email'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="switcher[username]";
        $names["add"][]=translateFN("username");
        if ($admin==1) {
                $edittypes["add"][]="text";
        } else {
                // disabled: users cannot change their usernames
                $edittypes["add"][]="hidden";
        }
        $necessary["add"][]="";
        $values["add"][]=$switcher['username'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password
        $fields["add"][]="switcher[password]";
        $names["add"][]=translateFN("password");
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password check
        $fields["add"][]="switcher[passwordcheck]";
        $names["add"][]=translateFN("ripeti password");
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="switcher[telefono]";
        $names["add"][]=translateFN("telefono");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$switcher['telefono'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // tariffa
        $fields["add"][]="switcher[tariffa]";
        $names["add"][]=translateFN("tariffa");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$switcher['tariffa'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // profilo
        $fields["add"][]="switcher[profilo]";
        $names["add"][]=translateFN("profilo");
        $edittypes["add"][]="textarea";
        $necessary["add"][]="";
        $values["add"][]=$switcher['profilo'];
        $options["add"][]="";
        $maxsize["add"][]="";

        // layout
/*
        $fields["add"][]="switcher[layout]";
        $names["add"][]="layout";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$switcher['layout'];
        $options["add"][]="";
        $maxsize["add"][]=20;
*/
/*        // layout
        if ($duplicate_dir_structure) {
                $temp_dir_base = $root_dir."/templates/main";
                $layout_OK = dirTree($temp_dir_base);
                // $val_sel = "";

                $max = count($layout_OK) ;
                for ($i=0; $i<$max; $i++){
                    if($i != ($max-1)){
                        $val_sel .= $layout_OK[$i] .":" ;
                    }else{
                        $val_sel .= $layout_OK[$i] ;
                        }
                }

                // $layout_OK [] = "";
//                print_r($layout_OK);

                $fields["add"][]="switcher[layout]";
//                 $names["add"][]="layout";
                $names["add"][]="Layout: $val_sel";
                $edittypes["add"][]="select";
                $necessary["add"][]="";
                 $values["add"][]=$switcher['layout'];
                $options["add"][]=$val_sel;
                $maxsize["add"][]=20;

        }else{
                $fields["add"][]="switcher[layout]";
                $names["add"][]="layout";
                $edittypes["add"][]="text";
                $necessary["add"][]="";
                $values["add"][]=$switcher['layout'];
                $options["add"][]="";
                $maxsize["add"][]=20;
        }
*/
        // id
        $fields["add"][]="id";
        $names["add"][]="id";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$id";
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }

    function form_confirmpassword($file_action,$file_back,$username,$id_user,$id_course,$token){
      // inizializzazione variabili
	    $str = "";
    	// password
        $fields["add"][]="user[password]";
        $names["add"][]="password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password check
        $fields["add"][]="user[passwordcheck]";
        $names["add"][]="ripeti password";
        $edittypes["add"][]="password";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // uid
        $fields["add"][]="user[uid]";
        $names["add"][]="uid";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$id_user";
        $options["add"][]="";
        $maxsize["add"][]="";

          // course
        $fields["add"][]="course";
        $names["add"][]="course";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$id_course";
        $options["add"][]="";
        $maxsize["add"][]="";

        // username
        $fields["add"][]="user[username]";
        $names["add"][]="username";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$username";
        $options["add"][]="";
        $maxsize["add"][]="";

        // token
        $fields["add"][]="token";
        $names["add"][]="tok";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$token";
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
      //  $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;


    }

    // Function to get username for password changing
    function form_getUsername($file_action){
        // inizializzazione variabili
	$http_root_dir =   $GLOBALS['http_root_dir'];
    $root_dir =   $GLOBALS['root_dir'];

	$str = "";
	// username
        $fields["add"][]="username";
        $names["add"][]="username or email address";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=isset($username) ? $username : null;
        $options["add"][]="";
        $maxsize["add"][]=50;

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        // $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }

    // Funzione studente modify form - table output
    function form_editstudent($file_action,$file_back,$student,$id,$admin=0){
        // inizializzazione variabili
	$http_root_dir =   $GLOBALS['http_root_dir'];
        $root_dir =   $GLOBALS['root_dir'];
        $duplicate_dir_structure =   $GLOBALS['duplicate_dir_structure'];
	$str = "";

        // nome
        $fields["add"][]="student[nome]";
        $names["add"][]=translateFN("Nome");
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$student['nome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="student[cognome]";
        $names["add"][]=translateFN("Cognome");
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$student['cognome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="student[email]";
        $names["add"][]=translateFN("e-mail");
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$student['email'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="student[username]";
        $names["add"][]=translateFN("username");
        if ($admin==1) {
                $edittypes["add"][]="text";
        } else {
                // disabled: users cannot change their usernames
                $edittypes["add"][]="hidden";
        }
        $necessary["add"][]="";
        $values["add"][]=$student['username'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password
        $fields["add"][]="student[password]";
        $names["add"][]=translateFN("password");
        $edittypes["add"][]="password";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

        // password check
        $fields["add"][]="student[passwordcheck]";
        $names["add"][]=translateFN("ripeti password");
        $edittypes["add"][]="password";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=50;

         // età
        $fields["add"][]="student[birthdate]";
        $names["add"][]=translateFN("Data di Nascita");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$student['birthdate'];
        $options["add"][]="";
        $maxsize["add"][]=12;
        
        // comune di nascita
        $fields["add"][]="student[birthcity]";
        $names["add"][]=translateFN("Comune o stato estero di nascita");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$student['birthcity'];
        $options["add"][]="";
        $maxsize["add"][]=254;
        
        // provincia di nascita
        $fields["add"][]="student[birthprovince]";
        $names["add"][]=translateFN("Provincia di nascita");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$student['birthprovince'];
        $options["add"][]="";
        $maxsize["add"][]=254;

        // telefono
        $fields["add"][]="student[telefono]";
        $names["add"][]=translateFN("telefono");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$student['telefono'];
        $options["add"][]="";
        $maxsize["add"][]=40;

        // città
        $fields["add"][]="student[citta]";
        $names["add"][]=translateFN("citta");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$student['citta'];
        $options["add"][]="";
        $maxsize["add"][]=40;

        // provincia
        $fields["add"][]="student[provincia]";
        $names["add"][]=translateFN("provincia");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$student['provincia'];
        $options["add"][]="";
        $maxsize["add"][]=40;

        // nazione ??
        /*
        $fields["add"][]="student[nazione]";
        $names["add"][]=translateFN("nazione");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$student['nazione'];
        $options["add"][]="";
        $maxsize["add"][]=40;
        */

        // codice fiscale
        $fields["add"][]="student[codice_fiscale]";
        $names["add"][]=translateFN("Codice fiscale");
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$student['codice_fiscale'];
        $options["add"][]="";
        $maxsize["add"][]=40;


        // condizione particolare
        $partCondAr = array('category 1','category 2','category 3','category 4');
        $partCondStr = implode(":",$partCondAr);

        $partCondKeyAr = array('1','2','3','4');
        $partCondKeyStr = implode(":",$partCondKeyAr);

        // preferenze di accesso
        $prefAr = array('home','eg-kiosk','eg-station');
        $prefStr = implode(":",$prefAr);
        $prefKeyAr = array('1','2','3');
        $prefKeyStr = implode(":",$prefKeyAr);


/*
        // layout
        if ($duplicate_dir_structure) {
                $temp_dir_base = $root_dir."/templates/main";
                $layout_OK = dirTree($temp_dir_base);
                // $val_sel = "";

                $max = count($layout_OK) ;
                for ($i=0; $i<$max; $i++){
                    if($i != ($max-1)){
                        $val_sel .= $layout_OK[$i] .":" ;
                    }else{
                        $val_sel .= $layout_OK[$i] ;
                        }
                }

                // $layout_OK [] = "";
//                print_r($layout_OK);

                $fields["add"][]="student[layout]";
//                 $names["add"][]="layout";
                $names["add"][]="Layout: $val_sel";
                $edittypes["add"][]="select";
                $necessary["add"][]="";
                 $values["add"][]=$student['layout'];
                $options["add"][]=$val_sel;
                $maxsize["add"][]=20;

        }else{
                $fields["add"][]="student[layout]";
                $names["add"][]="layout";
                $edittypes["add"][]="text";
                $necessary["add"][]="";
                $values["add"][]=$student['layout'];
                $options["add"][]="";
                $maxsize["add"][]=20;
        }
*/
        // id
        $fields["add"][]="id";
        $names["add"][]="id";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$id";
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }




    function form_editcourse($file_action,$file_back,$course,$authors,$id){
        // inizializzazione variabili
        $root_dir = $GLOBALS['root_dir'];
        $str = "";

        $n_authors = count($authors);
        $sel_author_id = $course['id_autore'];

        // nome
        $fields["add"][]="course[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$course['nome'];
        $options["add"][]="";
        $maxsize["add"][]=32;

        // titolo
        $fields["add"][]="course[titolo]";
        $names["add"][]="Titolo";
        $edittypes["add"][]="text";
        $necessary["add"][]="true";
        $values["add"][]=$course['titolo'];
        $options["add"][]="";
        $maxsize["add"][]=128;

        /*   mod steve 22/04/09
         *  we don't want to let admin modify the author of the course!
        // id_autore
        $labels_sel = "";
        $val_sel = "";
        $max = count($authors) ;
        for ($i=0; $i<$max; $i++){
            $labels_sel .= ":". $authors[$i][1] ." ". $authors[$i][2] ." ";
            if($i != ($max-1)){
                $val_sel .= $authors[$i][0] .":" ;
            }else{
                $val_sel .= $authors[$i][0] ;
            }
        }
        $fields["add"][]="course[id_autore]";
        $names["add"][]="Autore $labels_sel";
        $edittypes["add"][]="select";
        $necessary["add"][]="true";
        $values["add"][]=$sel_author_id ;
        $options["add"][]="$val_sel";
        $maxsize["add"][]="";
        */
        // so we draw a "noinput" field for the name and an hidden field for the id
        //
        $label = "";
        $val = "";
        $max = count($authors) ;
        for ($i=0; $i<$max; $i++){
            if ($authors[$i][0] == $sel_author_id){
              $label = $authors[$i][1] ." ". $authors[$i][2];
              $val = $authors[$i][0];
            }
        }

        // author's id: it is necessary !
        $fields["add"][]="course[id_autore]";
        $names["add"][]="Autore";
        $edittypes["add"][]="hidden";
        $values["add"][]= "$val";

        // author's name
        $fields["add"][]="nome_autore";
        $names["add"][]="Autore";
        $edittypes["add"][]="noinput";
        $values["add"][]= "$label";

        /* end mod */

        // descrizione
        $fields["add"][]="course[descr]";
        $names["add"][]="descrizione";
        $edittypes["add"][]="textarea";
        $necessary["add"][]="";
        $values["add"][]=$course['descr'];
        $options["add"][]="";
        $maxsize["add"][]="";

        // data pubblicazione
        $fields["add"][]="course[d_create]";
        $names["add"][]="data creazione (GG/MM/AAAA)";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$course['d_create'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // data pubblicazione
        $fields["add"][]="course[d_publish]";
        $names["add"][]="data pubblicazione (GG/MM/AAAA)";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$course['d_publish'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // media path
        $fields["add"][]="course[media_path]";
        $names["add"][]="media path";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        $values["add"][]=$course['media_path'];
        $options["add"][]="";
        $maxsize["add"][]=50;

 	$temp_dir_base = $root_dir."/templates/main";
	$layout_OK = dirTree($temp_dir_base);
	// $val_sel = "";
	$max = count($layout_OK) ;
	for ($i=0; $i<$max; $i++){
	if($i != ($max-1)){
		$val_sel .= $layout_OK[$i] .":" ;
	}else{
		$val_sel .= $layout_OK[$i] ;
		}
	}
	// $layout_OK [] = "";
	$fields["add"][]="course[id_layout]";
	$names["add"][]="Layout: $val_sel";
	$edittypes["add"][]="select";
	$necessary["add"][]="";
	$values["add"][]=$course['id_layout'];
	$options["add"][]=$val_sel;
	$maxsize["add"][]=20;

        // id
        $fields["add"][]="id";
        $names["add"][]="id";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$id";
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione admin zoom - table output
    function zoom_admin($file_back,$admin_ha,$links_ha){
        // inizializzazione variabili
        $str = "";

        // nome
        $fields["add"][]="admin_ha[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$admin_ha['nome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="admin_ha[cognome]";
        $names["add"][]="Cognome";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$admin_ha['cognome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="admin_ha[email]";
        $names["add"][]="e-mail";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$admin_ha['email'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="admin_ha[username]";
        $names["add"][]="username";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$admin_ha['username'];
        $options["add"][]="";
        $maxsize["add"][]=50;

//        // password
//        $fields["add"][]="admin_ha[password]";
//        $names["add"][]="password";
//        $edittypes["add"][]="noinput";
//        $necessary["add"][]="";
//        $values["add"][]=$admin_ha['password'];
//        $options["add"][]="";
//        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="autore[telefono]";
        $names["add"][]="telefono";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$admin_ha['telefono'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // azioni
        $fields["add"][]="azioni";
        $names["add"][]="cancella/modifica";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$links_ha["delete_img_link"] ." ".$links_ha["edit_img_link"];
        $options["add"][]="";
        $maxsize["add"][]=12;


        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,"","add",false,false);

        // scrittura stringa back
       // $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione tutor zoom - table output
    function zoom_tutor($file_back,$tutor_ha,$links_ha,$admin=0){
        // inizializzazione variabili
        $str = "";

        // nome
        $fields["add"][]="tutor_ha[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$tutor_ha['nome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="tutor_ha[cognome]";
        $names["add"][]="Cognome";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$tutor_ha['cognome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="tutor_ha[email]";
        $names["add"][]="e-mail";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$tutor_ha['email'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // profilo
        $fields["add"][]="tutor_ha[profilo]";
        $names["add"][]="profilo";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$tutor_ha['profilo'];
        $options["add"][]="";
        $maxsize["add"][]="";

        // username
        $fields["add"][]="tutor_ha[username]";
        $names["add"][]="username";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$tutor_ha['username'];
        $options["add"][]="";
        $maxsize["add"][]=50;


        if ($admin){
//        // password
//        $fields["add"][]="tutor_ha[password]";
//        $names["add"][]="password";
//        $edittypes["add"][]="noinput";
//        $necessary["add"][]="";
//        $values["add"][]=$tutor_ha['password'];
//        $options["add"][]="";
//        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="tutor_ha[telefono]";
        $names["add"][]="telefono";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$tutor_ha['telefono'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // tariffa
        $fields["add"][]="tutor_ha[tariffa]";
        $names["add"][]="tariffa";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$tutor_ha['tariffa'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // azioni
        $fields["add"][]="azioni";
        $names["add"][]="cancella/modifica";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$links_ha["delete_img_link"] ." ".$links_ha["edit_img_link"];
        $options["add"][]="";
        $maxsize["add"][]=12;

        }



        // id
        //$fields["add"][]="id";
        //$names["add"][]="id";
        //$edittypes["add"][]="hidden";
        //$necessary["add"][]="";
        //$values["add"][]="$id";
        //$options["add"][]="";
        //$maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,"","add",false,false);

        // scrittura stringa back
       // $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione student zoom - table output
    function zoom_student($file_back,$student_ha,$links_ha,$admin=0){
        // inizializzazione variabili
        $str = "";

        // nome
        $fields["add"][]="student[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$student_ha['nome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="student[cognome]";
        $names["add"][]="Cognome";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$student_ha['cognome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="student[email]";
        $names["add"][]="e-mail";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$student_ha['email'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="student[username]";
        $names["add"][]="username";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$student_ha['username'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        if ($admin){
//        // password
//        $fields["add"][]="student[password]";
//        $names["add"][]="password";
//        $edittypes["add"][]="noinput";
//        $necessary["add"][]="";
//        $values["add"][]=$student_ha['password'];
//        $options["add"][]="";
//        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="student[telefono]";
        $names["add"][]="telefono";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$student_ha['telefono'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // azioni
        $fields["add"][]="azioni";
        $names["add"][]="cancella/modifica";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$links_ha["delete_img_link"] ." ".$links_ha["edit_img_link"];
        $options["add"][]="";
        $maxsize["add"][]=12;

        }
        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,"","add",false,false);

        // scrittura stringa back
       // $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione author zoom - table output
    function zoom_author($file_back,$author_ha,$links_ha,$admin=0){
        // inizializzazione variabili
        $str = "";
        $file_action = "";

        // nome
        $fields["add"][]="author_ha[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$author_ha['nome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // cognome
        $fields["add"][]="author_ha[cognome]";
        $names["add"][]="Cognome";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$author_ha['cognome'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // email
        $fields["add"][]="author_ha[email]";
        $names["add"][]="e-mail";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$author_ha['email'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // username
        $fields["add"][]="author_ha[username]";
        $names["add"][]="username";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$author_ha['username'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        if ($admin){
//        // password
//        $fields["add"][]="author_ha[password]";
//        $names["add"][]="password";
//        $edittypes["add"][]="noinput";
//        $necessary["add"][]="";
//        $values["add"][]=$author_ha['password'];
//        $options["add"][]="";
//        $maxsize["add"][]=50;

        // telefono
        $fields["add"][]="author_ha[telefono]";
        $names["add"][]="telefono";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$author_ha['telefono'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // tariffa
        $fields["add"][]="author_ha[tariffa]";
        $names["add"][]="tariffa";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$author_ha['tariffa'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // profilo
        $fields["add"][]="author_ha[profilo]";
        $names["add"][]="profilo";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$author_ha['profilo'];
        $options["add"][]="";
        $maxsize["add"][]="";

        // azioni
        $fields["add"][]="azioni";
        $names["add"][]="cancella/modifica";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$links_ha["delete_img_link"] ." ".$links_ha["edit_img_link"];
        $options["add"][]="";
        $maxsize["add"][]=12;

        }
        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,false);

        // scrittura stringa back
     //   $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione course zoom - table output
    function zoom_course($file_back,$course_ha,$links_ha,$author_ha){
        // inizializzazione variabili
        $str = "";
        $links = "";
        $n_authors = count($author_ha);
        $sel_author_id = $course_ha['id_autore'];

        // nome
        $fields["add"][]="course_ha[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$course_ha['nome'];
        $options["add"][]="";
        $maxsize["add"][]=32;

        // titolo
        $fields["add"][]="course_ha[titolo]";
        $names["add"][]="Titolo";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$course_ha['titolo'];
        $options["add"][]="";
        $maxsize["add"][]=128;

        // autore
        $fields["add"][]="course_ha[id_autore]";
        $names["add"][]="Autore";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$author_ha['nome'] ." ". $author_ha['cognome'];
        $options["add"][]="";
        $maxsize["add"][]="";

        // descrizione
        $fields["add"][]="course_ha[descr]";
        $names["add"][]="descrizione";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$course_ha['descr'];
        $options["add"][]="";
        $maxsize["add"][]="";

        // data pubblicazione
        $fields["add"][]="course_ha[d_create]";
        $names["add"][]="data creazione (GG/MM/AAAA)";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$course_ha['d_create'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // data pubblicazione
        $fields["add"][]="course_ha[d_publish]";
        $names["add"][]="data pubblicazione";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$course_ha['d_publish'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // media path
        $fields["add"][]="course_ha[media_path]";
        $names["add"][]="media path";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$course_ha['media_path'];
        $options["add"][]="";
        $maxsize["add"][]=50;

        // azioni
        while(list($key,$val)=each($links_ha)){
            $links .= " ". $links_ha["$key"] ." " ;
        }
        $fields["add"][]="azioni";
        $names["add"][]="cancella/modifica";
        $edittypes["add"][]="buttons";
        $necessary["add"][]="";
        $values["add"][]=$links ;
        $options["add"][]="";
        $maxsize["add"][]=12;

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,"","add",false,false);

        // scrittura stringa back
     //   $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione course xml to db - table output
    function xml_to_db_course($file_action,$file_back,$course_ha,$author_ha,$filelist_ar,$id){
        // inizializzazione variabili
        $str = "";

        $n_authors = count($author_ha);
        $sel_author_id = $course_ha['id_autore'];

        // nome
        $fields["add"][]="course_ha[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$course_ha['nome'];
        $options["add"][]="";
        $maxsize["add"][]=32;

        // titolo
        $fields["add"][]="course_ha[titolo]";
        $names["add"][]="Titolo";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$course_ha['titolo'];
        $options["add"][]="";
        $maxsize["add"][]=128;

        // autore
        $fields["add"][]="course_ha[id_autore]";
        $names["add"][]="Autore";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$author_ha['nome'] ." ". $author_ha['cognome'];
        $options["add"][]="";
        $maxsize["add"][]="";

        // descrizione
        $fields["add"][]="course_ha[descr]";
        $names["add"][]="descrizione";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$course_ha['descr'];
        $options["add"][]="";
        $maxsize["add"][]="";

        // data pubblicazione
        $fields["add"][]="course_ha[d_create]";
        $names["add"][]="data creazione (GG/MM/AAAA)";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$course_ha['d_create'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // data pubblicazione
        $fields["add"][]="course_ha[d_publish]";
        $names["add"][]="data pubblicazione";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$course_ha['d_publish'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // media path
        $fields["add"][]="course_ha[media_path]";
        $names["add"][]="media path";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$course_ha['media_path'];
        $options["add"][]="";
        $maxsize["add"][]=50;


        // xml file
        $labels_sel = "";
        $val_sel = "";
        $max = count($filelist_ar) ;
        for ($i=0; $i<$max; $i++){
            $labels_sel .= ":". $filelist_ar[$i] ;
            if($i != ($max-1)){
                $val_sel .= $filelist_ar[$i] .":" ;
            }else{
                $val_sel .= $filelist_ar[$i] ;
            }
        }

        $fields["add"][]="xml_file";
        $names["add"][]="File xml $labels_sel";
        $edittypes["add"][]="select";
        $necessary["add"][]="true";
        $values["add"][]="";
        $options["add"][]="$val_sel";
        $maxsize["add"][]="";

        // id
        $fields["add"][]="id";
        $names["add"][]="id";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$id";
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        // $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione course instances list - table output
    function course_instances_list($file_back,$field_list_ar,$db_data_ar,$id_corso){

        $dh = $GLOBALS['dh'];

        $str = "";
        $id = "";
        $tb = "";
        $n = 0;

        // administrators number from database query
        $n = count($db_data_ar);
        if($n>0){

            // creazione della tabella
            $tb = new Table() ;
            // setting delle caratteristiche della tabella
            $tb->initTable('1','center','0','1','','','','','','1','0');
            $i = 0;
            while($i<$n){
                $id_instance = $db_data_ar[$i][1]; // valore dell'istanza

                $tutor_id =  $dh->course_instance_tutor_get($id_instance);
                if (!empty($tutor_id)){
                           $tutor = $dh->get_tutor($tutor_id);
//                           $tutor_uname = "<a href=\"zoom_tutor.php?id=$tutor_id\">".$tutor['username']."</a>";
                           $tutor_uname = $tutor['username'];
                } else {
                          $tutor_uname = translateFN("Non assegnato");
                }

                $j = 1 ; // reset dei valori
                $val = ""; // reset dei valori
                if($db_data_ar[$i][0]==0 or empty($db_data_ar[$i][2])){
                    $val[0] = translateFN("Non iniziato");
                }else{
                    $val[0] = ts2dFN($db_data_ar[$i][2]);
                    // $val[0] = date("Y-m-d",$db_data_ar[$i][2]);
                }
                $val[1] =  $db_data_ar[$i][3] ." ";
                if($db_data_ar[$i][4]==0 or empty($db_data_ar[$i][4])){
                    $val[2] = translateFN("Non previsto");
                }else{
                    $val[2] = ts2dFN($db_data_ar[$i][4]);
                    // $val[2] = date("Y-m-d",$db_data_ar[$i][4]);
                }
                $i++;
                // definizione dei link per le azioni
                $delete_img_link = "<a href=\"#\" onclick=\"if (confirm ('". translateFN("cancellare la classe (istanza corso)?") . "')) window.location = 'course_instance_delete.php?id_corso=$id_corso&id_instance=$id_instance'\"><img src=\"img/delete.png\" border=\"0\" alt=\"rimuovi\"></a>";
                $zoom_img_link = "<a href=\"course_instance_zoom.php?id_corso=$id_corso&id_instance=$id_instance\"><img src=\"img/zoom.png\" border=\"0\" alt=\"zoom in\"></a>";
                $edit_img_link = "<a href=\"course_instance_edit.php?id_corso=$id_corso&id_instance=$id_instance\"><img src=\"img/edit.png\" border=\"0\" alt=\"edit\"></a>";
                // vito, 6 apr 2009
                //$student_manage = "<a href=\"course_instance_students_manage.php?id_corso=$id_corso&id_instance=$id_instance\"><img src=\"img/student.png\" alt=\"classi\" border=\"0\"></a>";
                $student_manage = "<a href=\"course_instance_students_manage_subscribe.php?id_corso=$id_corso&id_instance=$id_instance\"><img src=\"img/student.png\" alt=\"classi\" border=\"0\"></a>";
                $tutor_manage = "<a href=\"course_instance_tutor_manage.php?id_corso=$id_corso&id_instance=$id_instance\">$tutor_uname</a>";

//              $val[3] = "$delete_img_link $zoom_img_link $edit_img_link $student_manage $tutor_manage";

                $data[] =  array(
                                 "ID"=>$id_instance,
                                 translateFN("Durata")=>$val[1],
                                 translateFN("Previsto")=>$val[2],
                                 translateFN("Iniziato")=>$val[0],
                                 translateFN("Zoom")=>$zoom_img_link,
                                 translateFN("Classe")=>$student_manage,
                                 translateFN("Tutor")=>$tutor_manage
                                 );
            }

            $tb->setTable($data,$caption=translateFN("Elenco classi:"),$summary=translateFN("Tabella"));
            $str .= $tb->getTable();

        }else{
            $str = $this->info(translateFN("nessun risultato"));
        }

        // scrittura stringa back
      //  $str .= $this->go_file_back($file_back,"indietro alla lista corsi");

        return $str ;
    }



    // Funzione course instance zoom - table output
    function course_instance_zoom($file_back,$instance_ha,$links_ha){
        // inizializzazione variabili
        $str = "";

        // Data di inizio
        $fields["add"][]="instance_ha[data_inizio]";
        $names["add"][]="data inizio";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="yes";
        // $values["add"][]=date("Y-m-d",$instance_ha['data_inizio']);
        $values["add"][]=ts2dFN($instance_ha['data_inizio']);
        $options["add"][]="";
        $maxsize["add"][]=20;

        // Durata
        $fields["add"][]="instance_ha[durata]";
        $names["add"][]="durata in giorni";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$instance_ha['durata'];
        $options["add"][]="";
        $maxsize["add"][]=12;

        // Data d'inizio prevista
        $fields["add"][]="instance_ha[data_inizio_previsto]";
        $names["add"][]="data inizio prevista ";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=ts2dFN($instance_ha['data_inizio_previsto']);
        // $values["add"][]=date("Y-m-d",$instance_ha['data_inizio_previsto']);
        $options["add"][]="";
        $maxsize["add"][]=20;

        // azioni
        $fields["add"][]="azioni";
        $names["add"][]="cancella/modifica";
        $edittypes["add"][]="nodata";
        $necessary["add"][]="";
        $values["add"][]=$links_ha["delete_img_link"] ." ".$links_ha["edit_img_link"]." ";
        $options["add"][]="";
        $maxsize["add"][]=12;

	//print_r($values);

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,"","add",false,false);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione course instance add form - table output
    function course_instance_add_form($file_action,$file_back,$id_course){
        // inizializzazione variabili
        $str = "";

        // Data di inizio
        $fields["add"][]="instance_ha[data_inizio]";
        $names["add"][]="data inizio (GG/MM/AAAA)";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=22;

        // Durata
        $fields["add"][]="instance_ha[durata]";
        $names["add"][]="durata in giorni";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=20;

        // Data d'inizio prevista
        $fields["add"][]="instance_ha[data_inizio_previsto]";
        $names["add"][]="data inizio prevista (GG/MM/AAAA)";
        $edittypes["add"][]="text";
        $necessary["add"][]="yes";
        $values["add"][]="";
        $options["add"][]="";
        $maxsize["add"][]=22;

        // id
        $fields["add"][]="id_corso";
        $names["add"][]="id";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$id_course";
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione course instance edit - table output
    function course_instance_edit_form($file_action,$file_back,$instance_ha,$id_instance,$id_corso){
        // inizializzazione variabili
        $val = "";
        $str = "";

        // Data d'inizio prevista
        $fields["add"][]="instance_ha[data_inizio_previsto]";
        $names["add"][]="data inizio prevista (GG/MM/AAAA)";
        $edittypes["add"][]="text";
        $necessary["add"][]="yes";
        if($instance_ha['data_inizio_previsto']==0 or empty($instance_ha['data_inizio_previsto'])){
            $val = "";
        }else{
            $val = ts2dFN($instance_ha['data_inizio_previsto']);
        }
        $values["add"][]=$val;
        $options["add"][]="";
        $maxsize["add"][]=22;

        // Durata
        $fields["add"][]="instance_ha[durata]";
        $names["add"][]="durata in giorni";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        if($instance_ha['durata']==0 or empty($instance_ha['durata'])){
            $val = "";
        }else{
            $val = $instance_ha['durata'];
        }
        $values["add"][]=$val;
        $options["add"][]="";
        $maxsize["add"][]=12;

        // Data di inizio
        $fields["add"][]="instance_ha[data_inizio]";
        $names["add"][]="data inizio (GG/MM/AAAA)";
        $edittypes["add"][]="text";
        $necessary["add"][]="";
        if($instance_ha['data_inizio']==0 or empty($instance_ha['data_inizio'])){
            $val = "";
        }else{
            $val = ts2dFN($instance_ha['data_inizio']);
        }
        $values["add"][]=$val;
        $options["add"][]="";
        $maxsize["add"][]=22;

        // id_instance
        $fields["add"][]="id_instance";
        $names["add"][]="id";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]="$id_instance";
        $options["add"][]="";
        $maxsize["add"][]="";

        // id_corso
        $fields["add"][]="id_corso";
        $names["add"][]="id_corso";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="";
        $values["add"][]=$id_corso;
        $options["add"][]="";
        $maxsize["add"][]="";



        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione courses instances list - table output
    function courses_instances_list($file_action,$file_back,$field_list_ar,$courses_instances_ha,$courses_names_ha,$key,$id_student){

        $dh = $GLOBALS['dh'];
        $http_root_dir = $GLOBALS['http_root_dir'];


        // inizializzazione variabili
        $val = "";
        $val2= "";
        $val3= "";
        $str = "";
        $data = array();
        $titolo = "";

        // courses instances number from database query
        $n = count($courses_instances_ha);

        // LISTA istanze corsi
        if($n>0){
            // creazione della tabella
            $tb = new Table() ;

            // setting delle caratteristiche della tabella
            $tb->initTable('1','center','0','1','','','','','','1','1');
            // Syntax: $border,$align,$cellspacing,$cellpadding,$width,$col1, $bcol1,$col2, $bcol2
            // $caption = "<strong>".translateFN("Corsi ai quali sei iscritt*")."</strong>";

            $i = 0;

            foreach ($courses_instances_ha as $courses_instance){
                $id_corso = $courses_instance[1];
                $keys = array_keys($courses_names_ha);
                if (in_array($id_corso,$keys)) {
                       $titolo = "<a href='$http_root_dir/info.php?id_course=$id_corso'>".$courses_names_ha[$id_corso]."</a>";
                       $id_instance = $courses_instance[0];
                      // TUTOR non mostrato qui
                       //$tutor_id =  $dh->course_instance_tutor_get($id_instance);
                       //if (!empty($tutor_id)){
                         //   $tutor = $dh->get_tutor($tutor_id);
                          //  $tutor_uname = $tutor['username'];
                       //} else {
                         //   $tutor_uname = translateFN("Non assegnato");
                      // }

                       $j = 1 ; // reset dei valori
                       $val = ""; // reset dei valori
                       if ($courses_instance[2] == 0) {
                         $val[0] = translateFN("Non iniziato");
                       }
                       else {
                         $val[0] =  date("d-m-Y",$courses_instance[2]) ." " ;

                       }
                       $val[1] =  $courses_instance[3] ." ";
                       if ($courses_instance[4] == 0) {
                               $val[2] =  translateFN("Non previsto");
                       }
                       else {
                               $val[2] =  date("d-m-Y",$courses_instance[4]) ." ";
                       }
                       $i++;

                       // links per le azioni
                     //  $zoom_img_link = "<a href='$http_root_dir/info.php?id_course=$id_corso'><img src='img/zoom.png' alt='zoom in' border=0></a>";
                       $subscribe_img_link = "<a href=\"$http_root_dir/iscrizione/student_course_instance_subscribe.php?id_instance=$id_instance&id_student=$id_student&back_url=$file_back\"><img src=\"img/edit.png\" alt=\"iscriviti\" border=\"0\"></a>";
                       $unsubscribe_img_link = "<a href=\"$http_root_dir/iscrizione/student_course_instance_unsubscribe.php?id_instance=$id_instance&id_student=$id_student&back_url=$file_back\"><img src=\"img/delete.png\" alt=\"elimina iscrizione\" border=\"0\"></a>";

                       // controlla se lo studente e' gia'iscritto o preiscritto alla istanza del corso

                       $iscr = $dh->get_subscription($id_student,$id_instance);

                       // courses_instances[2] is the start date of the course instance
                       $now = time();
                       if( ($courses_instance[4] == 0) ||
                           ($courses_instance[4] > 0 && $courses_instance[4] > $now)) {
                         if($iscr['tipo']==0){
                           $val3 = $subscribe_img_link ;
                         }else{
                           $val3 = $unsubscribe_img_link;
                         }
                       }
                       else {
                         if($iscr['tipo']==0){
                           $val3 = '' ;
                         }else{
                           $val3 = $unsubscribe_img_link;
                         }
                       }


                       $val[3] = "$zoom_img_link $val3";
                       $data[] =  array(
                                        translateFN("corso")=>$titolo,
                                        translateFN("durata")=>$val[1],
                                        translateFN("inizio previsto")=>$val[2],
                                        translateFN("iniziato")=>$val[0],
                                     //   translateFN("tutor")=>$tutor_uname,
                                        translateFN("azioni")=>$val[3]
                                        );
                }

            }

            $tb->setTable($data,$caption=translateFN("Lista corsi"),$summary=translateFN("Tabella"));
            $str .= $tb->getTable();

                                                // Search Form
            $str .= "<br>";
            if (!empty($file_action)) {
                        // FORM di ricerca
                        $fields["add"][]="key";
                        $names["add"][]=translateFN("Cerca per argomento");
                        $edittypes["add"][]="text";
                        $necessary["add"][]="true";
                        $values["add"][]="$key";
                        $options["add"][]="";
                        $maxsize["add"][]=12;

                        // creazione del form di ricerca corsi
                        $str .= MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

            }


        }else{

            $str = $this->info(translateFN("Nessun risultato"));
        }

        // scrittura stringa back
       // $str .= $this->go_file_back($file_back,translateFN("Home degli studenti"));

        return $str ;
    }


/*
    // Funzione student course zoom - table output
    function student_course_zoom($file_back,$course_ha,$links_ha,$author_ha){
        // inizializzazione valori
        $n_authors = count($author_ha);
        $sel_author_id = $course_ha['id_autore'];
        $links = "";

        // nome
        $fields["add"][]="course_ha[nome]";
        $names["add"][]="Nome";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$course_ha['nome'];
        $options["add"][]="";
        $maxsize["add"][]=32;

        // titolo
        $fields["add"][]="course_ha[titolo]";
        $names["add"][]="Titolo";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$course_ha['titolo'];
        $options["add"][]="";
        $maxsize["add"][]=128;

        // autore
        $fields["add"][]="course_ha[id_autore]";
        $names["add"][]="Autore";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="true";
        $values["add"][]=$author_ha['nome'] ." ". $author_ha['cognome'];
        $options["add"][]="";
        $maxsize["add"][]="";

        // descrizione
        $fields["add"][]="course_ha[descr]";
        $names["add"][]="descrizione";
        $edittypes["add"][]="noinput";
        $necessary["add"][]="";
        $values["add"][]=$course_ha['descr'];
        $options["add"][]="";
        $maxsize["add"][]="";

        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,"","add",false,false);


        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }
*/


    // Funzione course instance tutor form - table output
    function course_instance_tutor_form($file_action,$file_back,$id_instance,$id_tutor_old,$id_corso,$tutors_ar){
        // numero totale di tutor
        $n = count($tutors_ar);

        if($n > 0){
            for($i=0;$i<$n;$i++){
                // scelta tutor
                $fields["add"][]="id_tutor_new";
                //$names["add"][]=  "<a href=\"zoom_tutor.php?id=$id_tutor_old\">".$tutors_ar[$i][1] ." ". $tutors_ar[$i][2] ."</a>:";
                $names["add"][]=  "<a href=\"zoom_tutor.php?id={$tutors_ar[$i][0]}\">".$tutors_ar[$i][1] ." ". $tutors_ar[$i][2] ."</a>:";
                $edittypes["add"][]="radio";
                $necessary["add"][]="";
                $values["add"][]= "$id_tutor_old";
                $options["add"][]=$tutors_ar[$i][0] .":";
                $maxsize["add"][]=32;
            }

            // delete tutor
            // ADA mod: only when no tutor is assigned we allow to not assign a tutor
            if($id_tutor_old == "no") {
              $fields["add"][]="id_tutor_new";
              $names["add"][]= "nessun tutor :" ;
              $edittypes["add"][]="radio";
              $necessary["add"][]="";
              if($id_tutor_old=="no"){
                  $values["add"][]= "del";
              }else{
                  $values["add"][]= "";
              }
              $options["add"][]="del:";
              $maxsize["add"][]=32;
            }
        }else{
            $str = "<p>". translateFN("Non vi sono tutor.") ."</p>";

            // scrittura stringa back
            $str .= $this->go_file_back($file_back,translateFN("Indietro"));

            return $str ;
        }

        // id old tutor
        $fields["add"][]="id_tutor_old";
        $names["add"][]="id_tutor_old";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="true";
        $values["add"][]=$id_tutor_old;
        $options["add"][]="";
        $maxsize["add"][]=20;

        // id instance
        $fields["add"][]="id_instance";
        $names["add"][]="id_instance";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="true";
        $values["add"][]=$id_instance;
        $options["add"][]="";
        $maxsize["add"][]=20;

        // id corso
        $fields["add"][]="id_corso";
        $names["add"][]="id_corso";
        $edittypes["add"][]="hidden";
        $necessary["add"][]="true";
        $values["add"][]=$id_corso;
        $options["add"][]="";
        $maxsize["add"][]=20;
        // creazione del form
        $str = MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$file_action,"add",false,true);

        // scrittura stringa back
        $str .= $this->go_file_back($file_back,translateFN("Indietro"));

        return $str ;
    }



    // Funzione scrittura  di un messaggio generico di risposta
    function info($messaggio){
         $str = "<p>";
         $str .= $messaggio ;
         $str .= "</p>\n";
         return $str ;
    }


    // Funzione scrittura messaggio di errore indefinito
    function err_undefined(){
        $str = "<p>\n";
        $str .= "[Errore:] Errore indefinito\n";
        $str .= "</p>\n";
        return $str ;
    }



function get_all_instancesFN() {

        $dh = $GLOBALS['dh'];

        $field_list_ar = array('nome','titolo');
        $courses = $dh->get_courses_list($field_list_ar);
        if (AMA_DataHandler::isError($courses)){
                $msg = $courses->getMessage();
                $classi = $msg;
                // header("Location: $error?err_msg=$msg");
            }else{
                $classe = array();
                $field_list_ar = array('data_inizio','durata','data_inizio_previsto');
                $num_cl = 0; //inizializzazione numero classi
                $corsi = count($courses);
                for ($i=0;$i < count($courses);$i++) {
                        $id_corso_tmp=$courses[$i][0];
                        $titolo_tmp=$courses[$i][2];
                        $db_data_ar = $dh->course_instance_get_list($field_list_ar,$id_corso_tmp);
                        if (AMA_DataHandler::isError($db_data_ar)){
                           $dati = $db_data_ar->getMessage();
                           $classi = $dati;
                           // header("Location: course_instance.php?status=$dati");
                        }else{
                                for ($c=0;$c<count($db_data_ar);$c++) {
                                        $id_istance = $db_data_ar[$c][0];
                                        if (!empty($id_istance)) {
                                                $classe[$num_cl][0] = $id_corso_tmp;
                                                $classe[$num_cl][1] = $titolo_tmp;
                                                $classe[$num_cl][2] = $id_istance;
                                                $data_inizio = $db_data_ar[$c][3];
                                                $classe[$num_cl][3] = $data_inizio;
                                                $num_cl++;
                                        }
                                }
                                $classi = $classe;
                        }
                }
        }
        return $classi;
}

function cmp ($a, $b) {
        // Ordina per cognome - il terzo elemento e' il cognome
    return strcmp($a[2],$b[2]);
}


} // fine classe

?>
