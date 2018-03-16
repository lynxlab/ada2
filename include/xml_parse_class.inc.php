<?php

/*
  Classe course_xml_file_process per l'estrazione dei dati corso da file xml e
  inserimento nel database di ADA.
  Viene utilizzato Expat per il parsing del file xml.

  Fasi del parsing del file xml:
    - creazione di una nuova istanza della classe
    - setting iniziali delle variabili generali (id autore, id corso,
      nome file xml, ...)
    - lettura del file xml e conseguente parsing
    - invocazione nodo per nodo della funzione di aggiunta nodi al database
      viene preparato l'array per passare idati alla funzione add_node secondo
      il formato richiesto dalla funzione add_node di ama.inc.php
    - copia i media file dalla directory di upload a quella di "produzione"
      (directory dell'autore con root definita di default o, se presente, viene
      utilizzato il mediapath presente nel database)
    - unset dei vari array contenti i dati processati

  Es.: parsing file xml data.xml

        // XML file process
        // utilizzo classe processa XML
        $xp = new course_xml_file_process ;

        $set_ha = array(
          "id_author"=>3,
          "id_course"=>25,
          "xml_file"=>"data.xml",
          "media_path"=>"miopath/"
          );
        $xp->set_init($set_ha) ;

        // parsing file xml
        $ris_ar = $xp->course_xml_file_parse();
        $xp->data_void();

        // elimina l'oggetto dalla memoria
        unset($xp);
*/

class course_xml_file_process{

    // dichiarazione variabili

    var $file = "" ;  // xml data file
    var $currentTag = "" ; // utilizzato per tener traccia del tag in processo
    var $parser ;
    var $livello = 0 ;
    var $livellolink = 0 ;
    var $totale_nodi = 0 ;
	var $init_error ="";
    var $dati_nodo_ar = array() ;
    var $dati_media_ar = array() ;
    var $file_no_copy = array() ;

    var $error = array("") ; // array errori

    var $str = ""; // per debug

    //vito, 26 may 2009
    var $need_to_call_addslashes = false;
    // vito, 26 may 2009
    function course_xml_file_process() {
      $this->need_to_call_addslashes = get_magic_quotes_gpc();
    }

    // Setting iniziale
    function set_init($set_ha){
        global $debug;
        $add_or_upgrade_ok = false;

        if(!is_array($set_ha)){
            $this->initerror = translateFN("Dati di inizializzazione in formato non corretto") ;
            return false;
        }else{
            $this->livello = 0 ;
            $this->livellolink = 0 ;
            $this->livellomedia = 0;
            $this->set_ha['id_author'] = $set_ha['id_author'];
            $this->set_ha['id_course'] = $set_ha['id_course'];
            $this->set_ha['xml_file'] = $set_ha['xml_file'];
            $this->set_ha['media_path'] = $set_ha['media_path'];
        }



        // verifying if course has instances already started

         $id_course = $this->set_ha['id_course'];

         $dh = new AMA_DataHandler();

         // inserimento dati nel database
         $instances = $dh->course_has_instances($id_course);
         if ($instances){
                  $field_list_ar = array('data_inizio_previsto',
                                         'data_inizio'
                                        );
                  $today = time();
                  $clause = "id_corso = $id_course and data_inizio >= $today";
                  $course_instancesAr = $dh->course_instance_find_list($field_list_ar, $clause);

                  //      or else a loop on   course_instance_get($id) ...
               //  mydebug(__LINE__,__FILE__,$course_instancesAr);
                  if (count($course_instancesAr))
                   // AND (UPGRADE_COURSE_MODE = 1)   ???
                     {
                       // Removing all course data
                        $res = $dh->remove_course_content($id_course);
                        // $debug=1; mydebug(__LINE__,__FILE__,$res);$debug=0;
                        if (AMA_DataHandler::isError($res)) {
                         // ch'aggi'a fa'?
    		                $this->initerror = translateFN("Impossibile rimuovere il corso con id:").$id_course ;
                            $add_or_upgrade_ok = false;
                           //  print $res->$message;
                        }   else {
                            $add_or_upgrade_ok = true;
                        }

                     } else {
                       $add_or_upgrade_ok = true;
                     }

         }  else {
             $add_or_upgrade_ok = true;
         }

		if ($add_or_upgrade_ok)	{
	        if (!$this->set_ha['id_author']) {
                $this->initerror = translateFN("Impossibile trovare l'autore con id:").$id_author;
				return false;
			}	elseif  (!$this->set_ha['id_course']) {
                $this->initerror = translateFN("Impossibile trovare il corso con id:").$id_course ;
				return false;
			}	elseif  (@filetype($this->set_ha['xml_file'])!="file") {
                $this->initerror = translateFN("Errore nel tipo di file");
				return false;
			} else
				return true;
		}	else
			return false;
    }


    // Funzioni

    function startElement($parser,$name,$attrs){
          // tag attualmente i fase di parsing

          $this->currentTag = $name;
         // print $this->currentTag;
          // azione a seconda del tag
          switch($name){
              case "NODE":
                   // gestione livello nodo (+1)
                   $this->livello = $this->livello + 1 ;
                   // tipo di nodo - verifica propriet� nodo

                   $this->dati_nodo_ar[$this->livello]['TYPE'] = $attrs['TYPE'];

                   break; // fine case NODE


              case "LINKS":
                  // gestione livellolink (+1)
                  $this->livellolink = $this->livellolink + 1;

                  // tipo di link - verifica propriet� link
                  switch($attrs['TYPE']){
                      case "INTERNAL":
                          $this->dati_nodo_ar[$this->livello]['LINKS'][$this->livellolink]['TYPE'] = 0 ;
                        break;
                      case "EXTERNAL":
                          $this->dati_nodo_ar[$this->livello]['LINKS'][$this->livellolink]['TYPE'] = 1 ;
                        break;
                      default:
                          $this->dati_nodo_ar[$this->livello]['LINKS'][$this->livellolink]['TYPE'] = 0 ;
                        break;
                  }
                break; // fine case LINKS

              case "MEDIA":
                    // gestione livellomedia (+1)
                  $this->livellomedia = $this->livellomedia + 1;
                  $this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]['TYPE']=$attrs['TYPE'];
                  $this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]['COPYRIGHT']=$attrs['COPYRIGHT'];

                  /*
                  switch($attrs['TYPE']){
                      case "IMG":
                          $this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]['TYPE'] = 0 ;
                        break;
                      case "AUDIO":
                          $this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]['TYPE'] = 1 ;
                        break;
                      case "VIDEO":
                          $this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]['TYPE'] = 2 ;
                        break;
                      case "DOC":
                          $this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]['TYPE'] = 3 ;
                        break;
                      default:
                          $this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]['TYPE'] = 0 ;
                        break;
                  }
                  switch($attrs['COPYRIGHT']){
                      case "0":
                          $this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]['COPYRIGHT'] = 0 ;
                        break;
                      case "1":
                          $this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]['COPYRIGHTt'] = 1 ;
                        break;
                      default:
                          $this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]['COPYRIGHT'] = 0 ;
                        break;
                  }
                  */
                break; // fine case MEDIA

               default:
                 break; // fine case default
              }
          }



    function endElement($parser,$name){
            // azione a seconda del tag
            switch($name) {
              case "NODE":
                  /* per DEBUG
                    @$this->totale_nodi=$this->totale_nodi+1;
                    echo "Nodi totale parziale-><b>".$this->totale_nodi."</b><br>";  // per DEBUG
                  */

                  // add node
                  $this->data_send_node();

                  // vuota l'array dai valori presenti
                  @$this->dati_nodo_ar[$this->livello]['TYPE'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['TITLE'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['ID'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['NAME'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['SUPER'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['LINKS'] = //array() ;
                  @$this->dati_nodo_ar[$this->livello]['COLOR'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['BGCOLOR'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['LEVEL'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['ORDER'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['ICON'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['VERSION'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['CORRECTNESS'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['COPYRIGHT'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['FAMILY'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['PARAGRAPH'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['TEXT'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['POSITION'] = "" ;
                  @$this->dati_nodo_ar[$this->livello]['MEDIA'] = array() ;

                  // conteggio nodi processati
                  $this->totale_nodi=$this->totale_nodi+1;

                  // gestione livello nodo (-1)
                  $this->livello = $this->livello - 1 ;
                  // gestione livellolink (azzerato)
                  $this->livellolink = 0 ;
                  // gestione livellomedia (azzerato)
                  $this->livellomedia = 0;

                break;

              case "MEDIA":
                    // echo " - $name fine<br>";  // per DEBUG
                  break;


              default:
            }

            // clear la variabile che tiene conto del tag processato al momento
            $this->currentTag = "";
    }



    // processa dati contenuti nel tag                              Tag;
    function characterData($parser,$data){
        // gestione dei dati presenti nel tag
        // inserisce i dati nell'array dei dati del nodo
        //print $this->currentTag;
        switch($this->currentTag){
              // riferiti al NODO
            case "ID":
                $this->dati_nodo_ar[$this->livello]['ID'] = "$data";
		/*
		global $debug;
		$debug=1;
	    	mydebug(__LINE__,__FILE__,array($this->livello,$this->dati_nodo_ar[$this->livello]['ID']));
	    	$debug=0;
		*/

		/*********************************************************/
		/* Modifica Graffio 26/03/03                             */
		/* Azzera il livello media e link nel caso di nuovo nodo */
	        $this->livellomedia = 0;
	        $this->livellolink = 0;
		/*********************************************************/
              break;
            case "NAME":
                $this->dati_nodo_ar[$this->livello]['NAME'] = "$data";
              break;
            case "SUPER":
                $this->dati_nodo_ar[$this->livello]['SUPER'] = "$data";
              break;
            case "PARAGRAPH":
                // mydebug(__LINE__,__FILE__, $data);
                if(empty($this->dati_nodo_ar[@$this->livello]['TEXT'])){
                    $this->dati_nodo_ar[$this->livello]['TEXT'] = $data ;
                }else{
                    $this->dati_nodo_ar[$this->livello]['TEXT'] .= $data ;
                }
              break;
            case "CORRECTNESS":
                $this->dati_nodo_ar[$this->livello]['CORRECTNESS'] = "$data";
                break;
            case "LEVEL":
              $this->dati_nodo_ar[$this->livello]['LEVEL'] = "$data";
              break;
            case "VERSION":
                    $this->dati_nodo_ar[$this->livello]['VERSION'] = "$data";
                    break;
            case "ORDER":
                    $this->dati_nodo_ar[$this->livello]['ORDER'] = "$data";
                    break;
            case "COPYRIGHT":
                    $this->dati_nodo_ar[$this->livello]['COPYRIGHT'] = "$data";
                    break;
            case "COLOR":
                    $this->dati_nodo_ar[$this->livello]['COLOR'] = "$data";
                    break;
            case "BGCOLOR":
                    $this->dati_nodo_ar[$this->livello]['BGCOLOR'] = "$data";
                    break;
            case "POSITION":
                    $this->dati_nodo_ar[$this->livello]['POSITION'] = "$data";
                    break;
            case "TITLE":
                    $this->dati_nodo_ar[$this->livello]['TITLE'] = "$data";
                    break;
            case "FAMILY":
                    $this->dati_nodo_ar[$this->livello]['FAMILY'] = "$data";
                    break;


            // ---------------- LINK --------------------------------------


            case "NODETO":
                $this->dati_nodo_ar[$this->livello]['LINKS'][$this->livellolink]['NODETO'] = "$data";

              break;
            case "NODEFROM":
                $this->dati_nodo_ar[$this->livello]['LINKS'][$this->livellolink]['NODEFROM'] = "$data";
              break;
            case "STYLE":
                $this->dati_nodo_ar[$this->livello]['LINKS'][$this->livellolink]['STYLE'] = "$data";
              break;
            case "MEANING":
                $this->dati_nodo_ar[$this->livello]['LINKS'][$this->livellolink]['MEANING'] = "$data";
              break;
            case "LPOSITION":
                $this->dati_nodo_ar[$this->livello]['LINKS'][$this->livellolink]['LPOSITION'] = "$data";
              break;
            case "ACTION":
                $this->dati_nodo_ar[$this->livello]['LINKS'][$this->livellolink]['ACTION'] = "$data";
              break;

            // ---------------- MEDIA --------------------------------------

            case "MEDIA":
                // DA FINIRE: in funzione della struttura del xml
                // PROVVISORIO: TYPE=0 E COPYRIGHT=0

	    // $tmp_debug = @$this->dati_nodo_ar[$this->livello]['MEDIA'];
                //if (empty($this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia])){
                    $this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]['FILENAME']=$data;
		    /*
	    	    global $debug;
		    $debug=1;
	    	    mydebug(__LINE__,__FILE__,$this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia]);
		    mydebug(__LINE__,__FILE__,array($this->livello,$this->livellomedia));
	    	    // mydebug(__LINE__,__FILE__,$this->dati_nodo_ar[1]['MEDIA'][1]);
		    $debug=0;
		    */

                    //$this->dati_nodo_ar[$this->livello]['MEDIA']['file_name']=$data;
                    //$this->dati_nodo_ar[$this->livello]['MEDIA']['type']=0;
                    //$this->dati_nodo_ar[$this->livello]['MEDIA']['copyright']=0;
                //} else {
                //       if(!in_array($data,$this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia])){
                //           array_push($this->dati_nodo_ar[$this->livello]['MEDIA'][$this->livellomedia],array("FILENAME"=>$data,"TYPE"=>0,"COPYRIGHT"=>0));
                //       }
                //}


                // inserisce i dati nell'array dei media per la copia dei media
                // file dalla directory temporanea a quella dei mediapath,
                // prima controllo se gia' presenti
                if(!@in_array($data,$this->dati_media_ar)){
                    @$this->dati_media_ar[] = "$data";
                }
              break;

            // ----------------- TAGS GENERALI ----------------------------
            case "DOCDATE":
                // non ne e' fatto il reset (1 solo tag DOCDATE presente)
                $this->dati_nodo_ar['DATE'] = "$data";
              break;
            case "DOCTITLE":
                // non ne e' fatto il reset (1 solo tag DOCTITLE presente)
                $this->dati_nodo_ar['TITLE'] = "$data";
              break;
            case "INFO":
                // non ne e' fatto il reset (1 solo tag INFO presente)
                $this->dati_nodo_ar['INFO'] = "$data";
              break;

              default:
            }

    }



    function course_xml_file_parse(){
        // inizializza il parser, codifica ISO-8859-1 (alternativa UTF-8 o US-ASCII)
        $this->parser = xml_parser_create("ISO-8859-1");
//        xml_set_object($this->parser,&$this);
        xml_set_object($this->parser,$this);

        // funzioni di callback: setting iniziale
        xml_set_element_handler($this->parser,"startElement","endElement");
        xml_set_character_data_handler($this->parser,"characterData");

        // apertura file xml
        if(!($fp = fopen($this->set_ha['xml_file'], "r"))){
            die("Cannot locate XML data file: $file");
        }

        // aumentata la durata del time limit per l'esecuazione di uno script
        set_time_limit(60) ;

        // lettura e parse dei dati
     //   while ($data = fread($fp, 4096)){
        while ($data = fgets($fp, 4096)){ // one line at a time...
			$data =  preg_replace("(\r\n|\n|\r)", "", $data); //CR
    /*
            // error handler - !!!!! DA RIVEDERE !!!!!
            //  mydebug(__LINE__,__FILE__, $data);

            // per correggere l'eliminazione delle entit� HTML (di expat??)
            $trans = get_html_translation_table (HTML_ENTITIES);

            // se arrivassero i dati bruti: $encoded = strtr ($data, $trans);
            $trans = array_flip ($trans);
            $trans['&apos;'] = "'";
            // mydebug(__LINE__,__FILE__, $trans);

            $coded_data = strtr ($data, $trans);
            // mydebug(__LINE__,__FILE__, $coded_data);
    */
            // parsing del file xml
    //        if(!xml_parse($this->parser, $coded_data, feof($fp))){ // use with coded data
            if(!xml_parse($this->parser, $data, feof($fp))){
                // return esito negativo
                $msg['0'] = "errore";
                $msg['errori'] = array(0=>"XML error: ".
                xml_error_string(xml_get_error_code($this->parser))
                ." line ". xml_get_current_line_number($this->parser)) ;

                return $msg;
            }
        }

        // clean up del parser
        xml_parser_free($this->parser);

        // copia i media file nella directory indicata dal media_path
        if(empty($this->set_ha['media_path'])) {
            $this->set_ha['media_path'] = MEDIA_PATH_DEFAULT;
        }
        // chiamata alla funzione di copia media file
        if($this->media_transfer()){
            $media_path_result = translateFN("OK") ;
        }else{
            $media_path_result = translateFN("errore nella copia dei media") ;
        }

        // ripristina il time limit per l'esecuzione di uno script
        set_time_limit(30) ;

        // return dell'esito delle operazioni di parsing
        if(count($this->error) > 1){
            $msg = array(
                      "0"=>"errore",
                      "1"=>$this->totale_nodi,
                      "2"=>$media_path_result,
                      "3"=>$this->file_no_copy,
                      "errori"=>$this->error
                      );
        }else{
            $msg = array(
                      "0"=>"OK",
                      "1"=>$this->totale_nodi,
                      "2"=>$media_path_result,
                      "3"=>$this->file_no_copy
                    );
        }
        return $msg;

    }



    // svuota l'array dei dati
    function data_void(){
        $this->dati_nodo_ar = "" ;
        $this->dati_media_ar = "" ;
    }



    // Funzione per copia dei media file nella directory filepath
    function media_transfer(){
        global $root_dir;
        global $debug;
        // directories di origine e destinazione media file
        $dir_from = realpath(UPLOAD_PATH . $this->set_ha['id_author'] ."/media") ;

        $slash = strrchr($this->set_ha['media_path'],"/");
        if (strlen($slash)>1){
            $m_path = $this->set_ha['media_path']."/";
        } else {
            $m_path = $this->set_ha['media_path'];
        }
        $dir_to = $m_path;


        if ($debug){
                echo "DIRFROM $dir_from<br>";
                echo "DIRTO $dir_to<br>";
        }
        // controlla i file presenti nella directory di destinazione
        $files_to = searchdir($dir_to) ;
        // if  (empty($files_to))    die "Directory non esistente<br>";

        // intersezione array e rinomina file dell'intersezione
        $intersect_ar = array_intersect($files_to,$this->dati_media_ar);
        foreach ($intersect_ar as $key => $val){
            //$info = stat($dir_to.'/'.$val);
            rename($dir_to.$val,$dir_to.date("Y_m_d_H_i_s_").$val) ;
        }

        // copia file nella directory di destinazione
        for($i=0;$i < count($this->dati_media_ar);$i++){
           $file_to = $dir_to.$this->dati_media_ar[$i];
           $file_from = $dir_from.'/'.$this->dati_media_ar[$i];
		   if (!stristr($this->dati_media_ar[$i],"http:")) {
			 	//echo "Va copiato: $file_from $file_to <br>";
	           if (!file_exists($file_from) OR !copy($file_from,$file_to))
    	       {
        	        $this->file_no_copy[] = $this->dati_media_ar[$i] ;
            	}
		   } else {
			 //echo "NON va copiato: ".$this->dati_media_ar[$i]."<br>";
		   }
        }

        return "true" ;
    }



    // Funzione inserimento dati nodo nel DB
    function data_send_node(){
         // inizializzazione variabili
         global $debug;
         $data_ha = array() ;
         $linkAr = array() ;
         $res = "" ;




         // preparazione array dati nodo

         $data_ha['id_node_author'] = $this->set_ha['id_author'] ;
         $data_ha['id'] = $this->set_ha['id_course'] ."_". strtr($this->dati_nodo_ar[$this->livello]['ID'],"n: ","") ;

         if ($this->dati_nodo_ar[$this->livello]['SUPER']== 'SELF'){
             $data_ha['parent_id'] = '';
             // $this->dati_nodo_ar[$this->livello]['TEXT'] = '';
         }elseif($this->dati_nodo_ar[$this->livello]['SUPER']== 'TOP'){
            $data_ha['parent_id'] = $this->set_ha['id_course'] ."_0";
         }else{
            $data_ha['parent_id'] = $this->set_ha['id_course'] ."_".$this->dati_nodo_ar[$this->livello]['SUPER'] ;
         }


         $data_ha['title'] = $this->html_prepare($this->dati_nodo_ar['TITLE']);
         $data_ha['creation_date'] = $this->dati_nodo_ar['DATE'] ;

         $data_ha['type'] = $this->dati_nodo_ar[$this->livello]['TYPE'] ;
         $data_ha['name'] = $this->html_prepare($this->dati_nodo_ar[$this->livello]['NAME'] );

         //vito, 27 mar 2009: here we need to parse the text of the node in order to convert internal links
         //$data_ha['text'] = $this->html_prepare($this->dati_nodo_ar[$this->livello]['TEXT']) ;
         $prepared_text = $data_ha['text'] = $this->html_prepare($this->dati_nodo_ar[$this->livello]['TEXT']) ;

         // vito, 26 may 2009
         if($this->need_to_call_addslashes) {
           $pattern     = '/<LINK TYPE=INTERNAL VALUE=\\\"([0-9]+)\\\">/';
         }
         else {
           $pattern     = '/<LINK TYPE=INTERNAL VALUE="([0-9]+)">/';
         }

         $replacement = '<LINK TYPE="INTERNAL" VALUE="\\1">';
         $data_ha['text'] = preg_replace($pattern, $replacement, $prepared_text);

         // preparazione array posizione nodo
         $_position = $this->dati_nodo_ar[$this->livello]['POSITION'] ;
         $positionAr = explode(',',$_position);

         @$data_ha['pos_x0']=$positionAr[0];
         @$data_ha['pos_y0']=$positionAr[1];
         @$data_ha['pos_x1']=$positionAr[2];
         @$data_ha['pos_y1']=$positionAr[3];

         // dati generali
         $data_ha['order'] =$this->dati_nodo_ar[$this->livello]['ORDER'];
         $data_ha['level'] = $this->dati_nodo_ar[$this->livello]['LEVEL'] ;
         $data_ha['version'] = $this->dati_nodo_ar[$this->livello]['VERSION'] ;
         $data_ha['n_contacts'] = "0" ;
         $data_ha['icon'] = "node.gif" ; // DEFAULT
         $data_ha['bgcolor'] = $this->html_prepare($this->dati_nodo_ar[$this->livello]['BGCOLOR']);
         $data_ha['color'] = $this->html_prepare($this->dati_nodo_ar[$this->livello]['COLOR']);
         $data_ha['correctness'] = $this->dati_nodo_ar[$this->livello]['CORRECTNESS'] ;
         $data_ha['copyright'] = $this->dati_nodo_ar[$this->livello]['COPYRIGHT'];
//         $data_ha['family'] = $this->dati_nodo_ar[$this->livello]['FAMILY'];
          $data_ha['family'] = 'default';

         // preparazione array LINKS
         if(is_array(@$this->dati_nodo_ar[$this->livello]['LINKS'])){
            // $linkAr = $this->dati_nodo_ar[$this->livello]['LINKS'] ;
             $linksAr = array();
             if(count($this->dati_nodo_ar[$this->livello]['LINKS'])>0){
                 for($i=1;$i<=count($this->dati_nodo_ar[$this->livello]['LINKS']);$i++){
                 // non utilizzato l'id del nodo che si sta processando
                 // ma il dato proveniente da "NODEFROM" di "LINKS"
                 // se il corso � rovinato potrebbero esserci dei problemi
                 // quindi nascondiamo un po' di warnings
                     @$linkAr[$i]['id_nodo'] = $this->set_ha['id_course'] ."_". $this->dati_nodo_ar[$this->livello]['LINKS'][$i]['NODEFROM'];
                     @$linkAr[$i]['id_nodo_to'] = $this->set_ha['id_course'] ."_".$this->dati_nodo_ar[$this->livello]['LINKS'][$i]['NODETO'];
                     @$linkAr[$i]['tipo'] =  $this->dati_nodo_ar[$this->livello]['LINKS'][$i]['TYPE'];
                     @$linkAr[$i]['stile'] =  $this->dati_nodo_ar[$this->livello]['LINKS'][$i]['STYLE'];
                     @$linkAr[$i]['significato'] =  $this->dati_nodo_ar[$this->livello]['LINKS'][$i]['MEANING'];
                     @$linkAr[$i]['azione'] =  $this->dati_nodo_ar[$this->livello]['LINKS'][$i]['ACTION'];

                     $linkAr[$i]['id_utente'] = $this->set_ha['id_author'] ;
                     $linkAr[$i]['data_creazione'] = $this->dati_nodo_ar['DATE'] ;
                     if (!empty($this->dati_nodo_ar[$this->livello]['LINKS'][$i]['LPOSITION'])){
                            $_position = $this->dati_nodo_ar[$this->livello]['LINKS'][$i]['LPOSITION'];
                            $positionAr = explode(',',$_position);
                     } else {
                            $positionAr =array(0,0,100,100);
                     }
                     $linkAr[$i]['posizione'] = $positionAr;
                    // mydebug(__LINE__,__FILE__,$linkAr[$i]);
                 }
             }
         }
         $data_ha['links_ar'] = $linkAr;

         // MEDIA non ancora implementato completamente
         // preparazione array MEDIA

         if(is_array(@$this->dati_nodo_ar[$this->livello]['MEDIA'])){
            $mediaAr= array();
            if(count($this->dati_nodo_ar[$this->livello]['MEDIA'])>0){
                 for($i=1;$i<=count($this->dati_nodo_ar[$this->livello]['MEDIA']);$i++){
                      $mediaAr[$i]['tipo'] = $this->dati_nodo_ar[$this->livello]['MEDIA'][$i]['TYPE'];
                      $mediaAr[$i]['copyright'] = $this->dati_nodo_ar[$this->livello]['MEDIA'][$i]['COPYRIGHT'];
                      $mediaAr[$i]['nome_file'] = $this->dati_nodo_ar[$this->livello]['MEDIA'][$i]['FILENAME'];

		      /*
		      global $debug; $debug=1;
		      mydebug(__LINE__,__FILE__,$mediaAr[$i]);
		      */
                 }
            }
            $data_ha['resources_ar'] = $mediaAr;
         }


         // ACTIONS non ancora implementato
         $data_ha['actions_ar'] = "" ;

         // creazione nuova istanza della classe AMA
         $dh = new AMA_DataHandler();

         // inserimento dati nel database

         $res = $dh->add_node($data_ha);

         // gestione errori nell'inserimento dei dati del nodo nel database
         if(is_object($res) && stristr($res->message,'Error')){
             // print $res->message ;
             array_push($this->error,$res->message) ;

         }
    }


    // Funzione per la preparazione delle stringhe
    function html_prepare ($stringa){
       // return addslashes(htmlentities($stringa), ENT_COMPAT | ENT_HTML401, ADA_CHARSET);
      /*
       * vito, 26 may 2009
       * if magic_quotes is ON, we need to call addslashes here,
       * because AMA will not call addslashes.
       * if magic_quotes is OFF, we do not call addslashes here,
       * since it will be called by AMA
       */
      //if(get_magic_quotes_gpc()) {
      if($this->need_to_call_addslashes){
        return addslashes(nl2br($stringa));
      }
      return nl2br($stringa);
    }

} // fine della classe

?>
