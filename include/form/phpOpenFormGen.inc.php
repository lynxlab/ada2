<?php

// *******************************************************************************************
//
// Funzione per la generazione di form.
//
// ultima modifica 23/06/2001 by Marco Benini
//
//
// Sono definiti a livello di td tipi di class per la formattazione mediante cascading style sheet.
// class utilizzate: input.text textarea td.input td.name select td.error
//
// ********************************************************************************************
//          Derivato da:
//          Formgenerator Demofile V1.0.4
//          Functionversion V1.1
//
//          copyright 2000 by Ludwig Ruderstaller - ruderstaller@cwd.at
// ********************************************************************************************


  function MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,$action,$which,$errors,$submit,$lang_submit="",$lang_reset_opt=""){
//  MakeForm($fields,$names,$edittypes,$necessary,$values,$options,$maxsize,"","add",false,false)
//print_r($errors);
     // messaggi
     $nec_symbol ="*";
     $lang_ness=translateFN("I campi contrassegnati con");
     $lang_ness.=" $nec_symbol ";
     $lang_ness.=translateFN("sono obbligatori");
     $lang_error=translateFN("Riempire");



     if (empty($lang_submit)) //default
              $lang_submit=translateFN("Invia");
     switch($lang_reset_opt){
            case "":
                     $lang_reset=translateFN("Reset");
                     break;
            case "no":
                     $lang_reset = "";
                     break;
             default:
                        $lang_reset = $lang_reset_opt;
     } // switch

     $lang_error2=translateFN("Solo ");
     $lang_error3=translateFN(" caratteri consentiti!");

     // variabili di controllo tabella inclusa nel form
     $tb["width"] = "450";
     $tb["cellpadding"] = "3";
     $tb["cellspacing"] = "4";
     $tb["align"] = "center";

     // inizializzazione variabile per return dati
     $str = "";
     $frm_str = "";

     // -- Submit button needed?
     // permette di definire se inserire o meno il bottone di submit (nel caso ad es. che si voglia
     // usare la funzione per una visualizzazione dei dati sensa campi di input)
     if($submit){
         $frm_str = "\n<form enctype=\"multipart/form-data\" method=\"post\" action=\"$action\" "; // HEADER
     }

     // viene costruita una tabella con 2 colonne
     $str .= "<table border=\"0\" cellpadding=\"". $tb["cellpadding"] ."\" cellspacing=\"". $tb["cellspacing"] ."\" width=\"". $tb["width"] ."\" align=\"". $tb["align"] ."\">\n";

     if($submit){
         if(in_array("true",$necessary[$which])){
             $str .= "<tr><td class=\"name\" colspan=\"2\">$lang_ness</td></tr>\n";

         }
     }

     // -- Submit End -- //
     $necessary_fields = "";
     for($i=0;$i<count($fields[$which]);$i++){
         if($necessary[$which][$i]==true){
             $addon=$nec_symbol;
	     $necessary_fields.=$fields[$which][$i].",";
         }else{
             $addon="";
         }

         if(($errors[$i]) && ($edittypes[$which][$i]!="hidden")){
             if($errors[$i]=="1"){
                  $str .= "<tr><td class=\"error\" colspan=\"2\" align=\"center\">$lang_error</td></tr>";
             }elseif($errors[$i]=="2"){
                  $str .= "<tr><td class=\"error\" colspan=\"2\" align=\"center\">$lang_error2 ".$maxsize[$which][$i]." $lang_error3</td></tr>";
             }
         }

         if($edittypes[$which][$i]=="hidden"){
             ////////////////////////
             //-- Hidden Fields: --//
             ////////////////////////
             $str .= "<tr><td><input type=\"hidden\" name=\"".$fields[$which][$i]."\" value=\"".$values[$which][$i]."\"></td></tr>\n";
           
         }elseif($edittypes[$which][$i]=="text" || $edittypes[$which][$i]=="password"){
             ////////////////////////////
             //-- Text input Fields: --//
             ////////////////////////////
             if($maxsize[$which][$i]!=""){$addon2="maxlength=\"".$maxsize[$which][$i]."\"";}
             $str .= "<tr><td align=\"left\" class=\"name\">".$names[$which][$i]."$addon</td><td align=\"left\" class=\"input\"><input class=\"text\" type=\"".$edittypes[$which][$i]."\" name=\"".$fields[$which][$i]."\" value=\"".$values[$which][$i]."\" $addon2 id=\"".$fields[$which][$i]."\"></td></tr>\n";

         }elseif($edittypes[$which][$i]=="select"){
             ///////////////////////
             //-- Select Field: --//
             ///////////////////////
             $opt=explode(":",$options[$which][$i]);
             $name=explode(":",$names[$which][$i]);
             $str .= "<tr><td align=\"left\" class=\"name\">".$name[0]."$addon</td><td align=\"left\" class=\"input\"><select name=\"".$fields[$which][$i]."\">\n<option value=\"\">-- scelta --</option>\n";
             $forgetit=array_shift($name);
                 for($j=0;$j<count($opt);$j++){
                     if($values[$which][$i]==$opt[$j]){
                         $presel="selected";
                     }else{
                         $presel="";
                     }
                     $str .= "<option $presel value=\"".$opt[$j]."\">$name[$j]</option>\n";
                 }
             $str .= "</select></td></tr>\n";

          }elseif($edittypes[$which][$i]=="multiple"){
              ///////////////////////////////
              //-- Multiple Select Field: --//
              ///////////////////////////////
              $opt=explode(":",$options[$which][$i]);
              $name=explode(":",$names[$which][$i]);
              $naa=$fields[$which][$i];
              $str .= "<tr><td align=\"left\" class=\"name\">".$name[0]."$addon</td><td align=\"left\" class=\"input\"><select name=\"".$fields[$which][$i]."[]\" multiple rows=\"5\">\n";
              $forgetit=array_shift($name);
              $count=0;
                  for($j=0;$j<count($opt);$j++){
                      if(is_array($values[$which][$i])){
                          if(in_array($opt[$j],$values[$which][$i])){
                              $presel="selected";
                          }else{
                              $presel="";
                          }
                      }
                      $str .= "<option $presel value=\"".$opt[$j]."\">". $name[$j] ."</option>\n";
                      $count++;
                  }
                  unset($count);

                  $str .= "</select></td></tr>\n";

          }elseif($edittypes[$which][$i]=="checkbox"){
              /////////////////////////
              //-- Checkbox Field: --//
              /////////////////////////
              $name=explode(":",$names[$which][$i]);
              $opt=explode(":",$options[$which][$i]);
              $str .= "<tr><td align=\"left\" class=\"name\">".$name[0]."$addon</td><td align=\"left\" class=\"input\">\n";
              $forgetit=array_shift($name);
              $count=1;
              for($j=0;$j<count($name);$j++){
                  if($values[$which][$i][$j]=="1"){
                      $presel="checked";
                  }else{
                      $presel="";
                  }
                  $str .= "<input $presel type=\"checkbox\" name=\"".$fields[$which][$i]."[$opt[$j]]"."\" value=\"1\">&nbsp;&nbsp;$name[$j]<br>\n";
                  $count++;
              }
              unset($count);
              $str .= "</tr>\n";

          }elseif($edittypes[$which][$i]=="radio"){
              //////////////////////
              //-- Radio Field: --//
              //////////////////////
              $opt=explode(":",$options[$which][$i]);
              $name=explode(":",$names[$which][$i]);
              $str .= "<tr><td align=\"left\" class=\"name\">".$name[0]."$addon</td><td align=\"left\" class=\"input\">\n";
              $forgetit=array_shift($name);
              for($j=0;$j<count($name);$j++){
                  if($values[$which][$i]==$opt[$j]){
                      $presel="checked";
                  }else{
                      $presel="";
                  }
                  $str .= "<input $presel type=\"radio\" name=\"".$fields[$which][$i]."\" value=\"$opt[$j]\">&nbsp;&nbsp;$name[$j]<br>\n";
              }
              $str .= "</tr>\n";

          }elseif($edittypes[$which][$i]=="noinput"){
              /////////////////
              //-- NoInput --//
              /////////////////
              $str .= "<tr><td align=\"left\" class=\"name\">".$names[$which][$i]."$addon</td><td align=\"left\" class=\"input\">".$values[$which][$i]."\n<input type=\"hidden\" name=\"".$fields[$which][$i]."\" value=\"".$values[$which][$i]."\">\n</td></tr>\n";

          }elseif($edittypes[$which][$i]=="nodata"){
              /////////////////
              //-- NoData --//
              /////////////////
              $str .= "<tr><td align=\"left\" class=\"name\">".$names[$which][$i]."$addon</td><td align=\"left\" class=\"input\">".$values[$which][$i] ."\n</td></tr>\n";

          }elseif($edittypes[$which][$i]=="textarea"){
              ///////////////////
              //-- TextArea: --//
              ///////////////////
              $str .= "<tr><td align=\"left\" class=\"name\">".$names[$which][$i]."$addon</td><td align=\"left\" class=\"input\"><textarea rows=10 cols=60 wrap=\"virtual\" name=\"".$fields[$which][$i]."\">".$values[$which][$i]."</textarea></td></tr>\n";

          }elseif($edittypes[$which][$i]=="file"){
              //////////////
              //-- File --//
              //////////////
              $str .= "<tr><td align=\"left\" class=\"name\">".$names[$which][$i]."$addon</td><td align=\"left\" class=\"input\"><input type=\"file\" name=\"".$fields[$which][$i]."\" values=\"".$values[$which][$i]."\"></td></tr>";

          }elseif($edittypes[$which][$i]=="link"){
              //////////////
              //-- Link --//
              //////////////
              $opzioni='';
              if (sizeof($options[$which][$i])>0) {
              	foreach ($options[$which][$i] as $key=>$value) {
              		$opzioni.=' '.$key.'='.'"'.$value.'"';
              	}
              }

              $str .= "<tr><td align=\"left\" class=\"name\" colspan=\"2\"><a href=\"".$values[$which][$i]."\" ".$opzioni.">".$names[$which][$i]."</a></td></tr>";
// vito, 17 giugno 2009
          }elseif($edittypes[$which][$i]=="buttons"){
              /////////////////
              //-- Buttons --//
              /////////////////
              $str .= "<tr><td align=\"left\" class=\"name\">".$names[$which][$i]."$addon</td><td align=\"left\" class=\"input\">".$values[$which][$i]."\n";
            
          }              
      }

      $str.="<tr><td class=\"name\" colspan=\"2\" align=\"center\">";
      if($submit){
          //$str .= "<input type=\"submit\" name=\"submit\" value=\"$lang_submit\">&nbsp;&nbsp;&nbsp;";
          $str .= "<input type=\"submit\" name=\"submit\" value=\"$lang_submit\" >&nbsp;&nbsp;&nbsp;";
          if ($lang_reset) {
              $str.=" <input type=\"reset\" name=\"reset\" value=\"$lang_reset\" >";
          }
      }
      $str.="</td></tr>\n";

   //   $str .= "</table>\n</form>"; # FOOTER
      $str .= "</table>\n";
      
      // vito, 20 apr 2009, added $submit
      if ($submit && $necessary_fields!=""){
        $frm_str.=" onSubmit=\"return checkNec();\"";
        // steve 4/01/10
       //$frm_str.=">\n";
        
        $necessary_fields = rtrim($necessary_fields,',');
       	$str .= "<div id='cfl' title='$necessary_fields'></div>\n";
      } 
      // steve 4/01/10
      $frm_str.=">\n";
      
      
      // vito, 20 apr 2009
      if($submit){
        $str .="</form>"; # FOOTER
      }
      // restituisce la stringa che contiene il form creato
      return $frm_str.$str ;
}

?>