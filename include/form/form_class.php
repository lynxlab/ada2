<?php
//error_reporting(E_ALL);
/**
    HTML Form function library for PHP
    Copyright 2000 Jeremy Brand  <jeremy@nirvani.net>
    http://www.jeremybrand.com/Jeremy/Brand/Jeremy_Brand.html

    libHtmlForm for PHP.
    Release 1.0.0
    http://www.nirvani.net/software/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


    Prototypes:

    html_textarea($name, $value="", $cols=40, $rows=5)
    html_input_hidden($name, $value)
    html_input_radio($name, $value, $checked=FALSE)
    html_input_checkbox($name, $value, $checked=FALSE)
    html_input_text($name, $value="", $size=20, $maxlength=100)
    html_input_password($name, $value="", $size=20, $maxlength=100)
    html_input_submit($name="button", $value=" GO ")
    html_input_reset($value=" CANCEL ")
    html_select($name, $value_description_array, $value_selected="",
          $size=1, $multiple=FALSE)

    See each individual function for full usage!!!


**/

Class Form_html{

     //inizializzo variabili della classe
     //scrivere per intero extra_js & enctype
	var $form_name = "";
	var $method = "POST";
	var $action = "";
	var $extra_js = "";
	var $enctype = "";
	var $target = "";

/* DEPRECATED

	var $table_width = "100%";
	var $table_border = "0";
	var $table_height = "100%";
	var $table_align = "center";
	var $table_cellspacing = "2";
	var $table_cellpadding = "2";
	var $td_text_align = "center";
	var $td_field_align = "center";
	var $td_text_valign = "top";
	var $td_field_valign = "top";
	var $td_class = "";
	var $class_table = "";
*/

	var $text_class = "";
	var $id_form = "";
	var $class_form = "";
	/*
	 // vito
	var $arr_mesi = array(	1 => "gennaio",
				2 => "febbraio",
				3 => "marzo",
				4 => "aprile",
				5 => "maggio",
				6 => "giugno",
				7 => "luglio",
				8 => "agosto",
				9 => "settembre",
				10 => "ottobre",
				11 => "novembre",
				12 => "dicembre"
			);
      */
	function write_form(){


		$output= '<form action="'.$this->action.'" method="'.$this->method.'" '.$this->enctype.' '.$this->extra_js.' name="'.$this->form_name.'" '.$this->id_form.' '.$this->class_form.' '.$this->target.'>';

		return $output;
	}


	function open_fieldset($fieldset_id=NULL){

		$output= '<fieldset ';
		$fieldset_id ? $output.= 'id="'.$fieldset_id.'">' : $output.=">";

		return $output;

	}

	function close_fieldset(){
		$output= '</fieldset>';

		return $output;
	}

	function write_legend($legend){

		$output= '<legend>'.$legend.'</legend>';

		return $output;
	}


	function close_form(){

		$output= "</form>";

		return $output;
	}

	function html_textarea($label, $name, $value="", $cols=40, $rows=5, $wrap=NULL, $id=NULL, $div_id=NULL){

		$output = '<div';
		$div_id ? $output.= ' id="'.$div_id.'" >' : $output.='>';

		$output.= '<label for="'.$id.'">'.$label.'</label>';
		$output.= '<textarea wrap="'.$wrap.'" name="'.$name.'" id="'.$id.'" rows="'.$rows.'" cols="'.$cols.'">'.htmlspecialchars($value).'</textarea>';


		$output.= '</div>';
		return $output;
	}


	function html_input_hidden($name, $value, $id=NULL, $style=NULL){

		$output= "<input type=\"hidden\" name=\"" .htmlspecialchars($name). "\" value=\"".htmlspecialchars($value)."\" ".$id." ".$style." />";
		return $output;
      	}

      	function html_input_radio($label, $name, $value, $checked=FALSE, $id=NULL, $div_id= NULL){
		/**  The following allows for making sure that no two radio buttons
	 	 **  of the same name can ever be checked.  Once one is checked, no
		 **  subsequent ones will be allowed to be checked.  I used md5 just because it
		 **  produces a unique hash where all characters are valid for a variable
		 **  name in PHP and which is then made into the static variable
		 **  which is where the state is saved.
		 **  ChangeLog.
		 **  Had to use "global" instead of static.  Static was erroring out
		 **  for some reason.  **/

		$namesum = md5($name);
		$state = "radio_" . $namesum;
		$$state = $GLOBALS['$state'];
		$tmp = "";
		if ($checked && !$$state)
		{
		  $$state = TRUE;
		  $tmp = " checked";        }
		$output.= '<div ';
		$div_id=NULL ? $output.= ' id="'.$div_id.'">' : $output.='>';
		$output.='<label for="'.$id.'">'.$label.'</label>';
		$output.='<input type="radio" name="' .htmlspecialchars($name). '" value="' .htmlspecialchars($value). '" ' .$tmp. ' id="'.$id.'" />';
		//$output.=$label;

		$output.="</div>";
		unset($tmp); unset($state);
		return $output;
      }


      function html_input_checkbox($label, $name, $value, $checked=FALSE, $id=NULL, $div_id=NULL,$class=NULL){
		$tmp = "";
		if ($checked)
		  $tmp = " checked";

		$output= '<div';
		$div_id ? $output.= ' id="'.$div_id.'">' : $output.='>';

		$output.='<label for="'.$id.'">'.$label.'</label>';
		$output.='<input type="checkbox" name="' .htmlspecialchars($name). '" value="' .htmlspecialchars($value). '" '.$tmp.' id="'.$id.'" class="'.$class.'"/>';
		$output.= '</div>';

		return $output;
	}


      function html_input_text($label, $name, $value="", $size=20, $maxlength=100, $disable, $id=NULL, $div_id=NULL){

		if ($size > $maxlength)
		  $size = $maxlength;
		if (strlen($value) > $maxlength)
		  $value = substr($value, 0, $maxlength);

		//$output= '<div ';
		$output= '<span ';

		$div_id ? $output.= ' id="'.$div_id.'">' : $output.='>';

		$output.='<label for="'.$id.'">'.$label.'</label>';
		$output.='<input type="text" name="' .htmlspecialchars($name). '" value="' .htmlspecialchars($value). '" size="' .$size. '" maxlength="' .$maxlength. '" '.$disable.' id="'.$id.'" />';
		//$output.='</div>';
        $output.='</span>';

		return $output;
	}


      function html_input_password($label, $name, $value="", $size=20, $maxlength=100, $disable, $javascript="", $id=NULL){

		if ($size > $maxlength)
		  $size = $maxlength;
		if (strlen($value) > $maxlength)
		  $value = substr($value, 0, $maxlength);

		$output= '<div ';
		$div_id ? $output.= ' id="'.$div_id.'">' : $output.='>';

		$output.='<label for="'.$id.'">'.$label.'</label>';

		$output.='<input type="password" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($value).'" size="'.$size.'" maxlength="'.$maxlength.'"
		'.$disable.' id="'.$id.'" /></td>';

		$output.='</div>';

		return $output;
	}


      function html_input_submit($button_type, $name="button", $value=" GO ", $javascript=NULL, $id=NULL){

		$output='<input type="'.$button_type.'" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($value).'" '.$javascript.' id="'.$id.'" />';

		return $output;
	}


	//deprecated , si usa quella sopra specificando che tipo di button e'
      function html_input_reset($value=" CANCEL ")
      {
		$output= "<input type=\"reset\" value=\"" .htmlspecialchars($value). "\">";

		return $output;
      }

      function html_select($label, $name, $value_description_array, $value_selected="", $size, $multiple=FALSE, $javascript="", $disable, $id=NULL, $div_id){



		$num_elements = count($value_description_array);
		/*if ($size > $num_elements)
		  $size = $num_elements;
		*/
		$mul = "";
		if ($multiple)
		  $mul = " multiple ";

		//$output= '<div ';
		$output = '<span ';
		$div_id ? $output.= ' id="'.$div_id.'">' : $output.='>';


		$output.='<label for="'.$id.'">'.$label.'</label>';

		$output.='<select name="'.htmlspecialchars($name).'" size="'.$size.'" '.$javascript.' '.$disable.' '.$mul.' id="'.$id.'">';
			/**  make sure tmp[0] exists and is NULL,
			 **  needed for this to work corectly **/
		$tmp[""] = "";
		$tmp[$value_selected] = " selected";
			/**  Using error_reporting() here is a
			 **  hack to make sure printing out $tmp[unset] doesn"t bitch.
			 **  Doing it this way is much quicker than doing a compare each
			 **  time through the loop **/
		$orig_error = error_reporting(0);
		foreach ($value_description_array as $key => $val) {
		  $output.= '<option value="'.htmlspecialchars($key).'" '.$tmp[$key].'>' .$val. '</option>';
		}
		error_reporting($orig_error);
		unset($tmp); unset($orig_error); unset($mul);
		$output.= "</select>";

		//$output.="</div>";
        $output .= "</span>";
		return $output;
	}

	function html_file($label, $name, $MAX_FILE_SIZE, $id=NULL, $div_id=NULL){


		$output= '<div ';
		$div_id ? $output.= ' id="'.$div_id.'">' : $output.='>';

		$output.='<label for="'.$id.'">'.$label.'</label>';

		$output.='<input type="file" name="'.htmlspecialchars($name).'" id="'.$id.'">';

		$output.="</div>";
		return $output;
	}

	function stampa_form_data($label, $nome,$gg,$mm,$aaaa,$stampa_null=false,$anno_inizio,$anno_fine, $arr_mesi, $tr_open=true, $tr_closed=true, $javascript="", $disable, $colspan="", $idgg=NULL, $idmm=NULL, $idaaaa=NULL, $style=NULL)
	{
		//global $arr_mesi;
		if($tr_open==true):
			 $output= "<tr>";
		endif;
		$output.= "<td align=\"".$this->td_text_align."\" class=\"".$this->text_class."\"  colspan=\"".$colspan."\">&nbsp;&nbsp;".htmlspecialchars($label)."&nbsp;&nbsp;</td>";
		$output.= "<td align=\"".$this->td_field_align."\" class=\"".$this->td_class."\" colspan=\"".$colspan."\">";
		if($idgg!=NULL)
			$output.='<label for="'.$idgg.'">'.$idgg.'</label>';
		$output.= "<select name=\"gg".$nome."\" ".$idgg." ".$style.">\n";
		if($stampa_null):
			$output.= "<option value=\"\"";
			if($gg==''): $output.= " selected"; endif;
			$output.= ">-</option>";
		endif;
		for($i=1;$i<32;$i++){
			//if(strlen($i)==1){ $i="0".$i;}
			$output.= "<option value=\"".$i."\"";
			if($i==intval($gg)): $output.= " selected"; endif;
			$output.= "\">".$i."</option>\n";
		}//end for
		$output.= "</select>&nbsp;";
		if($idmm!=NULL)
			$output.='<label for="'.$idmm.'">'.$idmm.'</label>';
		$output.= "<select name=\"mm".$nome."\" ".$idmm." ".$style.">";
		if($stampa_null):
			$output.= "<option value=\"\" ";
			if($mm==''): $output.= " selected"; endif;
			$output.= "\">-</option>";
		endif;
		foreach($arr_mesi as $key=>$value){
			$output.= "<option value=\"".$key."\"";
			if($key==intval($mm)): $output.= " selected"; endif;
			$output.= "\">".ucfirst($value)."</option>\n";
		}//end foreach
		$output.= "</select>&nbsp;";
		if($idaaaa!=NULL)
			$output.='<label for="'.$idaaaa.'">'.$idaaaa.'</label>';
		$output.= "<select name=\"aaaa".$nome."\" ".$idaaaa." ".$style.">";
		if($stampa_null):
			$output.= "<option value=\"\"";
			if($aaaa==''): $output.= " selected"; endif;
			$output.= "\">-</option>";
		endif;
		for($i=$anno_inizio;$i<$anno_fine+1;$i++){
			$output.= "<option value=\"".$i."\"";
			if($i==intval($aaaa)): $output.= " selected"; endif;
			$output.= "\">".$i."</option>\n";
		}//end for
		$output.= "</select></td>";
		if($tr_closed==true):
			 $output.= "</tr>";
		endif;

		return $output;
	}

	//DA AGGIORNARE
	function select_regione($label, $name, $first_value, $arr_regioni, $tr_open=true, $tr_closed=true, $colspan=""){

		sort($arr_regioni);

		if($tr_open==true):
			 $output= "<tr>";
		endif;

		$output.= "<td align=\"".$this->td_text_align."\" class=\"".$this->text_class."\"  colspan=\"".$colspan."\">&nbsp;&nbsp;".htmlspecialchars($label)."&nbsp;&nbsp;</td>";
		$output.= "<td align=\"".$this->td_field_align."\" class=\"".$this->td_class."\" colspan=\"".$colspan."\"><SELECT name=\"".htmlspecialchars($name)."\"";

		foreach($arr_regioni as $value){
			$output.= "<option value=\"".$value."\" ";
				if(strtoupper($first_value)==strtoupper($value)):
					$output.= "selected";
				endif;
				$output.= ">".$value."</option>";
		}
		$output.= "</td>";
		if($tr_closed==true):
			 $output.= "</tr>";
		endif;

		return $output;
	}

	//DA AGGIORNARE
	function select_paese($label, $name, $value, $arr_paesi, $tr_open=true, $tr_closed=true, $colspan="", $multiple=FALSE, $size=1, $javascript, $disable)
	{

		/*if($value==""){
			$value="101";
		}*/

		//sort ($arr_paesi);
		if($tr_open==true):
			 $output= "<tr>";
		endif;

		if ($multiple){
		  $mul = " \"MULTIPLE\"";
		}

		$output.= "<td align=\"".$this->td_text_align."\" class=\"".$this->text_class."\"   colspan=\"".$colspan."\">&nbsp;&nbsp;".htmlspecialchars($label)."&nbsp;&nbsp;</td>";
		$output.= "<td align=\"".$this->td_field_align."\" class=\"".$this->td_class."\" colspan=\"".$colspan."\"><SELECT name=\"".htmlspecialchars($name)."\" size=\"" .$size. "\" ".$javascript." ".$disable." ".$mul."";


		for ($i=0; $i<count($arr_paesi); $i++){
			$a="";
			if (($value!="") && ($value == $arr_paesi[$i])){
				$a="selected ";
			}else{
			    $a="";
			}
			$output.= "<option value=\"".$arr_paesi[$i]."\" ".$a.">".$arr_paesi[$i]."</option>";
		}
		$output.= "</td>";
		if($tr_closed==true):
			 $output.= "</tr>";
		endif;

		return $output;
	}

	//DA AGGIORNARE
	function select_provincia($label, $name, $value_db, $arr_provincia, $tr_open=true, $tr_closed=true, $colspan="")
	{
		if($value==""){
			$value="rm";
		}

		//sort ($arr_paesi);
		if($tr_open==true):
			 $output= "<tr>";
		endif;

		$output.= "<td align=\"".$this->td_text_align."\" class=\"".$this->text_class."\"   colspan=\"".$colspan."\">&nbsp;&nbsp;".htmlspecialchars($label)."&nbsp;&nbsp;</td>";
		$output.= "<td align=\"".$this->td_field_align."\" class=\"".$this->td_class."\" colspan=\"".$colspan."\"><SELECT name=\"".htmlspecialchars($name)."\"";

		$output.= "<option value=\"".$option_value."\" ".$a.">".$visible_value."</option>";
		foreach($arr_provincia as $value){
			foreach($value as $new_key => $new_value){

				if($new_key=="targa"){
					$option_value = $new_value;
				}elseif($new_key=="provincia"){
					$visible_value = $new_value;
				}
				if(($option_value!="") && ($option_value == $value_db)){
					$a="selected ";
				}else{
				    $a="";
				}
			}
			$output.= "<option value=\"".$option_value."\" ".$a.">".$visible_value."</option>";
		}


		$output.= "</td>";
		if($tr_closed==true):
			 $output.= "</tr>";
		endif;

		return $output;

	}

	//DA AGGIORNARE
	function stampa_form_ora($label, $name,$hh,$ii,$stampa_null=false, $tr_open=true, $tr_closed=true, $disable, $colspan="")
	{
		if($tr_closed==true):
			 $output= "<tr>";
		endif;

		$output.= "<td align=\"".$this->td_text_align."\" class=\"".$this->text_class."\"  colspan=\"".$colspan."\">&nbsp;&nbsp;".htmlspecialchars($label)."&nbsp;&nbsp;</td>";
		$output.= "<td align=\"".$this->td_field_align."\" class=\"".$this->td_class."\" colspan=\"".$colspan."\"><SELECT name=\"hh".htmlspecialchars($name)."\" ".$disable.">";

		if($stampa_null):
			$output.= "<option value=\"\" ";
			if($hh==''): $output.= "selected"; endif;
			$output.= ">-</option>\n";
		endif;
		for($i=0;$i<24;$i++){
			$output.= "<option value=\"".$i."\"";
			if($i==intval($hh) && $hh!=""): $output.= " selected"; endif;
			$output.= "\">$i</option>\n";
		}//end for
		$output.= "</select>&nbsp;";
		$output.= "<select name=\"ii".$name."\">";
		if($stampa_null):
			$output.= "<option value=\"\" ";
			if($ii==''): $output.= " selected"; endif;
			$output.= ">-</option>\n";
		endif;
		for($i=0;$i<61;$i++){
			$output.= "<option value=\"".$i."\"";
			if($i==intval($ii) && $ii!=""): $output.= " selected"; endif;
			$output.= "\">".$i."</option>\n";
		}//end for
		$output.= "</select>";
		if($tr_closed==true):
			 $output.= "</tr>";
		endif;

		return $output;
	}


	function html_doubletextarea($label1, $label2, $name1, $name2, $label_button1, $label_button2, $name_button1, $name_button2, $value1="", $value2="", $cols1=40, $cols2=40, $rows1=8, $rows2=8,  $colspan=1, $id1=NULL, $id2=NULL, $js1=NULL, $js2=NULL, $style=NULL)
	{
		/**  wrap="virtual" is not part of any W3C HTML standard; at least
		 **  up to 4.01, but nearly any decent browser knows it, and if
		 **  it doesn"t oh well.   It is too nice to not include here. **/
		// if($tr_open==true):
		//	 $output= "<tr>";
		//endif;

		$output.= '<div ';
		$div_id ? $output.= ' id="'.$div_id.'">' : $output.='>';

		$output.='<label for="'.$id.'">'.$label.'</label>';


//		$output.= "<tr><td ";
//		$this->td_field_align ? $output.= ' align="'.$this->td_field_align.'"' : $output.="";
//		$output.= " class=\"".$this->td_class."\" colspan=\"".$colspan."\">&nbsp;&nbsp;".htmlspecialchars($label1)."&nbsp;&nbsp;</td>";
//		$output.=" <td ";
//		$this->td_field_align ? $output.= ' align="'.$this->td_field_align.'"' : $output.="";
//		$output.= " class=\"".$this->td_class."\" colspan=\"".$colspan."\">&nbsp;&nbsp;".htmlspecialchars($label2)."&nbsp;&nbsp;</td>
//		</tr><tr><td ";
//		$this->td_field_align ? $output.= ' align="'.$this->td_field_align.'"' : $output.="";
//		$output.= " class=\"".$this->td_class."\" colspan=".$colspan.">";
//		if($id1!=NULL)
		$output.='<label for="'.$name1.'">'.$label1.'</label>';

//		$output.= html_select

		$output.="<select  size=\"10\" name=\"" .htmlspecialchars($name1). "\" ".$id1." ".$style." ".$js1." multiple>
				";
				foreach ($value1 as $key => $val){
		  			$output.= "  <option value=\"" .$key. "\"" .$tmp[$key]. ">" .$val. "</option>";
				}

			$output.="</select></td><td ";
			$this->td_field_align ? $output.= ' align="'.$this->td_field_align.'"' : $output.="";
			$output.= " class=\"".$this->td_class."\" colspan=".$colspan.">";
			if($id2!=NULL)
				$output.='<label for="'.$name2.'">'.$label2.'</label>';
			$output.="<select size=\"10\"  name=\"" .htmlspecialchars($name2)."\" ".$id2." ".$style." ".$js2." multiple>";


				foreach ($value2 as $key => $val){
		  			$output.= "  <option value=\"" .$key. "\"" .$tmp[$key]. ">" .$val. "</option>";
				}

			$output.="</select>
				</td>
			</tr>";
		return $output;
	}

	//array_db = un array contenente id e categoria
	function select_from_db($label, $name, $array_db, $selected, $disable, $id=NULL, $div_id=NULL, $multiple=NULL, $size=1){

		$output.= '<div ';
		$div_id ? $output.= ' id="'.$div_id.'">' : $output.='>';

		$output.='<label for="'.$id.'">'.$label.'</label>';

		$output.='<select name="'.htmlspecialchars($name).'" '.$disable.' id="'.$id.'" size="'.$size.'"';
		if ($multiple)
		  $output.= " MULTIPLE";

		$output.=">";

		$output.= "<option value=\"0\"></option>";
		foreach($array_db as $value){
			foreach($value as $key => $real_value){
				if($key==0){
					$output.= "<option value=\"".$real_value."\" ";
					if($real_value==$selected){ $output.= " selected";}
					$output.= ">";
				}elseif($key==1){
					$output.= $real_value."</option>";
				}
			}
		}

		$output.= "</select>";
		$output.= "</div>";
		return $output;

	}

}
?>