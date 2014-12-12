<?php
/**
 * 
 * @package		
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link					
 * @version		0.1
 */

require_once CORE_LIBRARY_PATH .'/includes.inc.php';


class BaseHtmlLib {

  public static function link($href, $text) {
      $a = CDOMElement::create('a', "href:$href");
      if ($text instanceof CBase) {
          $a->addChild($text);
      }else {
          $a->addChild(new CText($text));
      }
      return $a;
  }
  public static function selectElement($element_attributes='', $data = array()) {
    $select = CDOMElement::create('select', $element_attributes);
    foreach($data as $value) {
      $option = CDOMElement::create('option',"value:$value");
      $option->addChild(new CText($value));
      $select->addChild($option);
    }
    return $select;
  }
  
  public static function selectElement2($element_attributes='', $data = array(), $selected=NULL) {
    $select = CDOMElement::create('select', $element_attributes);
    foreach($data as $value => $text) {
      $option = CDOMElement::create('option',"value:$value");
      if($selected != NULL && $selected == $value) {
        $option->setAttribute('selected','selected');
      }
      $option->addChild(new CText($text));
      $select->addChild($option);
    }
    return $select;
  }
  
  static public function radioButtons($element_attributes='', $data = array(), $radio_name) {
    $radios = CDOMElement::create('div');
    foreach($data as $radio_value => $radio_text) {
      $radio = CDOMElement::create('radio');
      $radio->setAttribute('name', $radio_name);
      $radio->setAttribute('value', $radio_value);
      
      $radios->addChild($radio);
      $radios->addChild(new CText($radio_text));
      $radios->addChild(new CText('<br>'));
    }
    return $radios;
  }

  static public function CheckBox($element_attributes='', $data = array(), $check_name) {
    $checks = CDOMElement::create('div');
    foreach($data as $check_value => $check_text) {
      $check = CDOMElement::create('check');
      $check->setAttribute('name', $check_name);
      $check->setAttribute('value', $check_value);

      $checks->addChild($check);
      $checks->addChild(new CText($check_text));
      $checks->addChild(new CText('<br>'));
    }
    return $checks;
  }
  
  public static function plainListElement($element_attributes='', $data = array(), $WithLabel=TRUE) {
    
    $ul = CDOMElement::create('ul', $element_attributes);
	if (!empty($data)) {
		foreach($data as $label => $value) {
		  $li = CDOMElement::create('li');
		  if($value instanceof CBase) {
			  $li->addChild($value);
		  }
		  elseif ($WithLabel) {
			$li->addChild(new CText('<b>'.$label.' </b>'));
			$li->addChild(new CText($value));
		  }else {
			$li->addChild(new CText($value));
		  }
		  $ul->addChild($li);
		}
	}
    return $ul;
  }
  
  public static function labeledListElement($element_attributes='', $data = array()) {
    
    $ul = CDOMElement::create('ul', $element_attributes);
    foreach($data as $label => $text) {
      $li = CDOMElement::create('li');
      if($text instanceof CBase) {
          $li->addChild($text);
      }
      else {
        //$li->addChild(new CText('<b>'.$label.' </b>'));
        $left_span = CDOMElement::create('span','class:list_label');
        $left_span->addChild(new CText(translateFN($label)));
        $right_span = CDOMElement::create('span', 'class:list_text');
        $right_span->addChild(new CText($text));
        $li->addChild($left_span);
        $li->addChild($right_span);        
      }
      $ul->addChild($li);
    }
    return $ul;
  }
  
  
  public static function tableElement($element_attributes='', $thead_data=array(), $tbody_data = array(), $tfoot_data = array(), $caption = '') {
    
    $table = CDOMElement::create('table', $element_attributes);
    /*
     * CSS class names
     */
    $css_table_class = $table->getAttribute('class');
    if($css_table_class === null) {
      $css_table_class    = 'default_table';
      $css_thead_class    = 'default_thead';
      $css_tfoot_class    = 'default_tfoot';
      $css_th_class       = 'default_th';
      $css_td_class       = 'default_td';
      $css_odd_row_class  = 'default_tr_odd';
      $css_even_row_class = 'default_tr_even'; 

      $table->setAttribute('class', $css_table_class);
    }
    else {
      $css_thead_class    = $css_table_class.'_thead';
      $css_tfoot_class    = $css_table_class.'_tfoot';
      $css_th_class       = $css_table_class.'_th';
      $css_td_class       = $css_table_class.'_td';
      $css_odd_td_class   = $css_table_class.'_td_odd';
      $css_even_td_class  = $css_table_class.'_td_even';
      $css_odd_row_class  = $css_table_class.'_tr_odd';
      $css_even_row_class = $css_table_class.'_tr_even'; 
    }

    /*
     * Caption
     */
	if (!empty($caption)) {
		$c = CDOMElement::create('caption');
		$c->addChild(new CText($caption));
		$table->addChild($c);
	}

    /*
     * THead
     */
    if(is_array($thead_data) && sizeof($thead_data) > 0) {
      $thead = CDOMElement::create('thead', "class:$css_thead_class");
      $tr = CDOMElement::create('tr');
      //$thead_data = $data[0];
      foreach($thead_data as $thead_col) {
        $th = CDOMElement::create('th',"class:$css_th_class");
        if($thead_col instanceof CBase) {
          $th->addChild($thead_col);
        }
        else {
          $th->addChild(new CText($thead_col));
        }
        $tr->addChild($th);
      }
      $thead->addChild($tr);
      $table->addChild($thead);
    }
    
    /*
     * TBody
     */
    $tbody = CDOMElement::create('tbody');
    $parity = 0;
    //$max_rows = sizeof($tbody_data);
    //for($i = 0; $i < $max_rows; $i++) {
    foreach($tbody_data as $table_row) {
      //$table_row = $tbody_data[$i];
      
      $row = CDOMElement::create('tr');
      if($parity%2 == 0) {
        $row->setAttribute('class',"$css_even_row_class");
      }
      else {
        $row->setAttribute('class',"$css_odd_row_class");
      }
      $parityCol = 0;
      foreach($table_row as $column_in_row) {
        $column = CDOMElement::create('td',"class:$css_td_class");
          if($parityCol%2 == 0) {
            if (isset($css_even_td_class)) $column->setAttribute('class',"$css_even_td_class");
          }
          else {
            if (isset($css_odd_td_class)) $column->setAttribute('class',"$css_odd_td_class");
          }
        if($column_in_row instanceof CBase) {
          $column->addChild($column_in_row);
        }
        else {
          $column->addChild(new CText($column_in_row));
        }
        $row->addChild($column);
        $parityCol++;
      }
      $tbody->addChild($row);
      $parity++;
    }

    $table->addChild($tbody);

    /*
     * TFoot
     */
    if(is_array($tfoot_data) && sizeof($tfoot_data) > 0) {
      $tfoot = CDOMElement::create('tfoot', "class:$css_tfoot_class");
      $tr = CDOMElement::create('tr');
      //$tfoot_data = $data[0];
      foreach($tfoot_data as $tfoot_col) {
        $th = CDOMElement::create('th',"class:$css_th_class");
        if($tfoot_col instanceof CBase) {
          $th->addChild($foot_col);
        }
        else {
          $th->addChild(new CText($tfoot_col));
        }
        $tr->addChild($th);
      }
      $tfoot->addChild($tr);
      $table->addChild($tfoot);
    }

    return $table;
  }
  
  public static function getPaginationBar($current_page, $page_titles, $base_href) {
    $div = CDOMElement::create('div','class:pagination_bar');

    $number_of_pages = count($page_titles);
    $i = 1;
    for ($i = 1; $i <= $number_of_pages; $i++) {
      
      if($i == $current_page) {
        $div->addChild(new CText($page_titles[$i]));        
      }
      else {
        $href = $base_href .'&page='.$i;
        $link = CDOMElement::create('a', "href:$href");
        $link->addChild(new CText($page_titles[$i]));
        $div->addChild($link);
      }
      $div->addChild(new CText(' | '));  
    }
    
    return $div;
  }
}
?>