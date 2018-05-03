<?php
/**
 * Html_element, Table, Ilist, Form and Tform classes
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

class HTML_element  {
	var $data;
	var $error;

      function print_element(){
             if (empty($this->error) and (!empty($this->data)))
                print $this->data;
      }

      function get_element(){
             if (empty($this->error) and (!empty($this->data)))
                 return $this->data;
      }

      function get_error(){
             if (!empty($this->error))
                 return $this->error;
      }
}



class Table extends HTML_element {
/*
Classe per la costruzione di tabelle HTML
Il parametro $data dev'essere un array associativo con chiavi  uguali ai nomi delle colonne
Se i dati non sono corretti restituisce null e setta la variabile error.

Esempio di chiamata:
  $data = array(
              array('nome'=>'fghj','cognome'=>'sdfg','et�=>'11'),
              array('nome'=>'sdfj','cognome'=>'ghj','et�=>'22'),
              array('nome'=>'fghj','cognome'=>'hjk','et�=>'33')
              );

$t = new Table();
$t->initTable('1','left','2','1','70%');
$t->setTable($data);
$t->printTable();
*/

      var $border;
      var $align;
      var $cellspacing;
      var $cellpadding;
      var $width;
      var $col1;
      var $bcol1;
      var $col2;
      var $bcol2;
      var $id;


      function __construct(){
               $this->initTable();
      }

       function initTable($border='?',$align='',$cellspacing='',$cellpadding='',$width='',
                                                  $col1='',$bcol1='',$col2='',$bcol2='',
                                                $labelcol='',$labelrow='',
                                                $rules='',$style = 'default',$id=null){
                if ($border=="?"){
                // no specified parameter
		/*
                        $rootdir = $GLOBALS['root_dir'];
                        @include("$rootdir/templates/tables.inc.php");
                        $table_style = 'default';
                        if (isset($tableParametersHa) AND array_key_exists($table_style,$tableParametersHa)){
                           $border= $tableParametersHa[$table_style]['border'];
                           $align=$tableParametersHa[$table_style]['align'];
                           $cellspacing=$tableParametersHa[$table_style]['cellspacing'];
                           $cellpadding=$tableParametersHa[$table_style]['cellpadding'];
                           $width= $tableParametersHa[$table_style]['width'];
                           $col1=$tableParametersHa[$table_style]['col1'];
                           $bcol1=$tableParametersHa[$table_style]['bcol1'];
                           $col2=$tableParametersHa[$table_style]['col2'];
                           $bcol2=$tableParametersHa[$table_style]['bcol2'];
                           $labelcol = $tableParametersHa[$table_style]['labelcol'];
                           $labelrow=$tableParametersHa[$table_style]['labelrow'];
                           $rules=$tableParametersHa[$table_style]['rules'];
                        } else {
                        // no default parameter file found, using hardcoded parameters
                           $border= 1;
                           $align="center";
                           $cellspacing=0;
                           $cellpadding=1;
                           $width= '90%';
                           $col1='';
                           $bcol1='white';
                           $col2='';
                           $bcol2='white';
                           $labelcol = '';
                           $labelrow='';
                           $rules='';
                        }
			*/
			  // no default parameter file found, using hardcoded parameters
                           $border= 1;
                           $align="center";
                           $cellspacing=0;
                           $cellpadding=1;
                           $width= '100%';
                           $col1='';
                           $bcol1='';
                           $col2='';
                           $bcol2='';
                           $labelcol = '';
                           $labelrow='';
                           $rules='';
                }

                // setting object variables
	        $this->style = $style;
		$this->border = (int)$border;
		$this->align = $align;
		$this->cellspacing = (int)$cellspacing;
		$this->cellpadding = (int)$cellpadding;
		$this->width = $width;
                $this->id = $id;

		if (!empty($col1))
					$this->col1 = $col1;
		if (!empty($col2))
					$this->col2 = $col2;
		if (!empty($bcol1))
					$this->bcol1 = $bcol1;
		if (!empty($bcol2))
					$this->bcol2 = $bcol2;

		$this->labelcol = $labelcol;
		$this->labelrow= $labelrow;
		if (!empty($rules)) {
			$this->rules = $rules;
		} else {
			$this->rules ='groups';
		}

      }

       function setTable($data,$caption="Tabella",$summary="Tabella"){
          if (gettype($data)!='array'){
               $this->error =translateFN("Il formato dei dati non &egrave; valido");
          } else {
            if (count($data)){
             $firstKey = key($data);
             $riga = $data[$firstKey];
             $totcol= count($riga);
             // vito, 18 feb 2009
             //$str = "<table class=\"".$this->style."_table\" rules=\"".$this->rules."\" summary=\"$summary\" width=\"".$this->width."\" cellspacing =\"".$this->cellspacing."\" cellpadding =\"".$this->cellpadding."\" border=\"".$this->border."\" align=\"".$this->align."\">\r\n";
             $idTable = ' ';
             if ($this->id != NULL) $idTable = ' id='.$this->id;
             $str = "<table class=\"".$this->style."_table\" summary=\"$summary\"".$idTable.">\r\n";
//             $str = "<table class=\"".$this->style."_table\" summary=\"$summary\">\r\n";
             $str.= "<caption>$caption</caption>\r\n";
             if ($this->labelcol) {

                         // Colgroups
                     $str.= "<colgroup>\r\n\t";
                     for ($c=0;$c<=$totcol;$c++){
                       $str.="<col id=\"c$c\" />";
                      // $str.="<col>";
                     }


                     $str.="\r\n</colgroup>\r\n";
                        // Headers
                     // vito, 18 feb 2008
                     //$str.="<thead class=\"".$this->style."_thead\" align=\"".$this->align.">\"";
                     $str.="<thead class=\"".$this->style."_thead\">";
                     $str.="\t<tr>\r\n";

                     reset($data);
                     $firstKey = key($data);
                     $riga = $data[$firstKey];
                                 // $riga = $data[0];
                     $str.= "\t<th class=\"".$this->style."_th\">&nbsp;</th>";
                     $h=0;
                     if (is_array($riga)){
                       foreach ($riga as $key=>$value){
                          $h++;
                          if (!empty($this->labelcol)){
                             // $str .= "<th id=a$h>$key</th>";
                              $str .= "<th class=\"".$this->style."_th\">$key</th>";
                          } else {
                             // $str .= "<th id=a$h>&nbsp;</th>";
                              $str .= "<th>&nbsp;</th>";
                          }
                       }
                     }
                     $str.="\t</tr>\r\n";
                     $str .= "\r\n</thead>\r\n";

             } else {
                    $str.="<thead></thead>\r\n";
             }
             $str .="<tbody>\r\n";
             reset($data);
             $r=0;

             foreach ($data as $riga){
                      $r++;
                      if (gettype($r/2)== 'integer'){
                             if (!empty($this->col1)){
                                  $str .= "\t<tr style=\"color:".$this->col1.";\"  bgcolor=\"".$this->bcol1."\">";
                             }else {
                                  $str .= "\t<tr class=\"".$this->style."_tr_odd\">";
                             }

                      } else {
                             if (!empty($this->col2)){
                                  $str .= "\t<tr style=\"color:".$this->col2.";\"  bgcolor=\"".$this->bcol2."\">";
                             }else {
                                  $str .= "\t<tr class=\"".$this->style."_tr_even\">";
                             }
                      }
                      $str .= "\r\n\t\t";
                      if ($this->labelrow){
                         $str .= "<td class=\"".$this->style."_td_label\">$r</td>";
                      } else {
                         $str .= "<td>&nbsp;</td>";
                      }

                      $h=0;
                      if (is_array($riga)){
                         foreach ($riga as $key=>$value){
                               $h++;
                              // $str .= "<td id=a$h>$value</td>";
                               $str .= "<td>$value</td>";
                         }
                      } else {
                         $str .= "<td class=\"".$this->style."_td\">&nbsp;</td>";
                      }
                      $str .= "\r\n\t</tr>\r\n";
                 }
                 $str .="</tbody>\r\n";
                 $str .= "</table>\r\n";
                 $this->data = $str;
             }

        }

      }

      function printTable(){
             return $this->print_element();
      }

      function getTable(){
             return $this->get_element();
      }



// end class Table
}

class IList extends HTML_element {
/*
Classe per la costruzione di liste HTML
Il parametro $data dev'essere un array anche multiplo
Se i dati non sono corretti restituisce null e setta la variabile error.

Esempio di chiamata:
  $data = array(
             'pippo',
             'pluto',
             $nipotiniAr,
             'paperino'
              );

$lObj = new IList();
$lObj->initList('1','a',3);
$lObj->setList($data);
$lObj->printList();

oppure:
$var = $lObj->getList();

*/

      var $type; // disc, square, circle
      var $start_tag;
      var $end_tag;
      var $ordered;
      var $startvalue;
      var $style; // a css class


      function __construct(){
               $this->initList();
      }

      function initList($ordered='0',$type='',$startvalue=1,$style="default"){
                $this->ordered = $ordered;
                $this->startvalue = $startvalue;
                $this->style = $style;
                if ($ordered){
                       $this->start_tag = "<OL class='$style' start='$startvalue'>\n";
                       $this->end_tag = "</OL>\n";
                } else {
                       $this->start_tag = "<UL class='$style'>\n";
                       $this->end_tag = "</UL>\n";
                }
                if (!empty($type))
                       $this->type = $type;
                else
                       if ($ordered)
                          $this->type = '1';
                       else
                          $this->type = 'disc';
      }

      function setList($data){
          if (gettype($data)!='array'){
               $this->error =translateFN("Il formato dei dati non &egrave; valido");
          } else {
             $str = $this->start_tag;
             foreach ($data as $riga){
             if (is_array($riga)){
                 $lObj = new Ilist();
                 $lObj->initList($this->ordered,$this->type,$this->startvalue);
                 $lObj->setList($riga);
                 $str.= $lObj->getList();
             }
             else  {
                if ($this->type)
                           $str .= "<li class=\"".$this->style."_li\" type=".$this->type.">$riga</li>\n";
                else
                            $str .= "<li>$riga</li>\n";


             }

             }
             $str .= $this->end_tag;
             $this->data = $str;
          }

      }

      function printList(){
             return $this->print_element();
      }

      function getList(){
             return $this->get_element();
      }



// end class IList
}



class Form extends HTML_element {
/*
Classe per la costruzione di form HTML.
Il parametro $data dev'essere un array associativo con chiavi type,name,label,size,rows,col,wrap,maxlength,value
Se i dati non sono corretti restituisce null e setta la variabile error.

Esempio di chiamata:
$data = array(
                     array(
                          'label'=>'username',
                          'type'=>'text',
                          'name'=>'username',
                          'size'=>'20',
                          'maxlenght'=>'40'
                          ),
                     array(
                          'label'=>'password',
                          'type'=>'password',
                          'name'=>'password',
                          'size'=>'20',
                          'maxlength'=>'40'
                          ),
                     array(
                          'label'=>'',
                          'type'=>'submit',
                          'name'=>'Submit',
                          'value'=>'Clicca qui'
                          )

                    );
$f = new Form();
$f->initForm("http://altrascuola.it/ada/pippo.php","GET","Pippo");
$f-> setForm($data);
$f->printForm();
*/


    var $action;
    var $method;
    var $enctype ;

    function __construct(){
    // per default prende il nome del file chiamante
//      $action =  array_pop(split('[/\\]',$PHP_SELF));  // = index

      $action = whoami().".php";
      $this->initForm($action);
    }

    function initForm($action,$method='POST',$enctype= "application/x-www-form-urlencoded") {
       if (!empty($action)) {
            $this->action = $action;
       }
       $this->method = $method;
       $this->enctype = $enctype;
      }

    function setForm($dataHa,$name = "Form1"){

      if ((empty($dataHa)) or (gettype($dataHa)!='array')){
            $this->error = translateFN("I dati non sono validi");
      } else {
        $str = "<form method=\"$this->method\" action=\"$this->action\" enctype=\"$this->enctype\">\r\n";

        foreach ($dataHa as $riga){
            foreach ($riga as $campo=>$valore){
                    switch ($campo){
                        case 'label':
                              $str .= "$valore";
                        break;
                        case 'type':
                              switch ($valore){
                                 case 'textarea':
                                    $str .= "<textarea";
                                    $state = 'textarea';
                                    break;
                                 case 'submit':
                                 case 'text':
                                 case 'password':
                                 case 'radio':
                                 case 'checkbox':
                                 case 'reset':
                                     $state = 'input';
                                     $str .= " <input type=\"$valore\"";
                                     break;
                                 case 'hidden':
                                     $state = 'input hidden';
                                     $str .= " <input type=\"$valore\"";
                                     break;
                                 case 'select':
                                     $state = 'select';
                                     $str .= " <select ";
                              }
                        break;
                        case 'name':
                              $str .= " name=\"$valore\"";
                              if ($state == 'select')
                                   $str .= ">\n";
                        break;
                        case 'checked':
                              if  ($valore!="")
                                  $str .= " checked=\"$valore\"";
                        break;
                        case 'value':
                              switch ($state){
                              case 'textarea':
                                   $textarea_value= $valore;
                                   break;
                              case 'select':
                                   foreach ($valore as $val){
                                        $str .= "<option value='$val'>$val</option>\n";
                                   }
                                   $str .= " </select>\n ";
                                   break;
                              default:
                                  $str .= " value =\"$valore\"";
                              }
                        break;
                        case 'size':
                              $str .= " size =\"$valore\"";
                        break;
                        case 'maxlength':
                              $str .= " maxlength=\"$valore\"";
                        break;
                        case 'rows':
                              $str .= " rows =\"$valore\"";
                        break;
                        case 'cols':
                              $str .= " cols =\"$valore\"";
                        break;
                        case 'wrap':
                              $str .= " wrap =\"$valore\"";
                        break;


                }

            }
           switch ($state){
                   case 'textarea':
                          $str .=">$textarea_value</textarea><br><br>\r\n";
                          break;
                   case 'select':
                          $str .="<br><br>\r\n";
                          break;
                   //case 'input':
                   default:
                          if (strstr($state,'hidden'))
                              $str .=">\r\n";
                          else
                              $str .="><br><br>\r\n";

            }
        }
        $str .= "</form>\r\n" ;

        $this->data = $str;
       }
    }

      function printFrom(){
             return $this->print_element();
      }

      function getForm(){
             return $this->get_element();
      }


// end class Form
}

class Tform extends Form {


  function setForm($dataHa,$name = ""){

	  if (!empty($name)) {
	      $fname = $name ;
	  } else {
	      $fname = $this->name ;

	  }
      if ((empty($dataHa)) or (gettype($dataHa)!='array')){
            $this->error = translateFN("I dati non sono validi");
      } else {
        $str = "<form name=\"$fname\" method=\"$this->method\" action=\"$this->action\" enctype=\"$this->enctype\" target=\"$this->target\">\r\n";
		$str.="<table>\n";

        foreach ($dataHa as $riga){
		  if (!strstr($state,'submit'))
				$str.="<tr>\n";

            foreach ($riga as $campo=>$valore){
                    switch ($campo){
                        case 'label':
                              $str .= "<td>$valore</td>\n";
                        break;
                        case 'type':
                              switch ($valore){
                                 case 'textarea':
                                    $str .= "<td><textarea";
                                    $state = 'textarea';
                                    break;
                                 case 'button':
                                    $str .= "<td><button";
                                    $state = 'button';
                                    break;
                                 case 'reset': // reset is compulsory !!! otherwise row will not get closed
                                     $state = 'input';
                                     $str .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"$valore\"";
                                     break;
                                 case 'submit':
                                     $state = 'input submit';
                                     $str .= "<td><input type=\"$valore\"";
                                     break;
                                 case 'text':
                                 case 'password':
                                 case 'radio':
                                 case 'checkbox':
                                     $state = 'input';
                                     $str .= "<td><input type=\"$valore\"";
                                     break;
                                 case 'hidden':
                                     $state = 'input hidden';
                                     $str .= "<td><input type=\"$valore\"";
                                     break;
                                 case 'select':
                                     $state = 'select';
                                     $str .= "<td><select ";
                              }
                        break;
                        case 'name':
                              $str .= " name=\"$valore\"";
                              if ($state == 'select')
                                   $str .= ">\n";
                        break;
                        case 'checked':
                              if  ($valore!="")
                                  $str .= " checked=\"$valore\"";
                        break;
                        case 'disabled':
                              if  ($valore!="")
                                  $str .= " disabled=\"$valore\"";
                        break;
                        case 'readonly':
                              if  ($valore!="")
                                  $str .= " readonly=\"$valore\"";
                        break;
                        case 'value':
                              switch ($state){
                              case 'textarea':
                                   $textarea_value= $valore;
                                   break;
                              case 'button':
                                   $button_value= $valore;
                                   break;
                              case 'select':
                                   foreach ($valore as $val){
                                        $str .= "<option value='$val'>$val</option>\n";
                                   }
                                   $str .= " </select>\n ";
							       break;
                              default:
                                  $str .= " value =\"$valore\"";
                              }
                        break;
                        case 'size':
                              $str .= " size =\"$valore\"";
                        break;
                        case 'maxlength':
                              $str .= " maxlength=\"$valore\"";
                        break;
                        case 'rows':
                              $str .= " rows =\"$valore\"";
                        break;
                        case 'cols':
                              $str .= " cols =\"$valore\"";
                        break;
                        case 'wrap':
                              $str .= " wrap =\"$valore\"";
                        break;
                        case 'onClick':
                              $str .= " onClick =\"$valore\"";
                        break;
                }

            }
           switch ($state){
                   case 'textarea':
                          $str .=">$textarea_value</textarea></td>\r\n";
				          $str .= "</tr>\r\n" ;
                          break;
                   case 'button':
                          $str .=">$button_value</button></td>\r\n";
				          $str .= "</tr>\r\n" ;
                          break;
                   case 'select':
                          $str .="</td>\r\n";
			              $str .= "</tr>\r\n" ;
                          break;
                   //case 'input':
                   default:
                          if (strstr($state,'submit'))
                              $str .=">";
                          else {
                              $str .="></td>\r\n";
					          $str .= "</tr>\r\n" ;
						  }

            }

        }
        $str .= "</table>\r\n" ;
        $str .= "</form>\r\n" ;

        $this->data = $str;
       }
    }




}

?>