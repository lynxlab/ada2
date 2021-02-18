<?php
/**
 * History class
 *
 *
 * @package		model
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		user_classes
 * @version		0.1
 */
class History
{
  var $id_course;
  var $id_course_instance;
  var $id_student;

  var $course_data;            // array associativo contenente per ogni nodo del corso:
  // id_nodo, nome, tipo, numero_visite
  var $nodes_count;            // nodi presenti nel corso
  var $visited_nodes_count;    // nodi diversi visitati
  var $node_visits_count;      // array contenente il totale visite per ogni tipo di nodo.
  var $node_visits_ratio;      // visite per nodo
  var $total_time;             // tempo di visita totale

  function __construct($id_course_instance, $id_student)
  {

    $this->id_course_instance = (int) $id_course_instance;
    $this->id_student         = (int) $id_student;
    $this->nodes_count         = 0;
    $this->node_visits_count   = array(ADA_LEAF_TYPE  => 0,
    ADA_GROUP_TYPE => 0,
    ADA_NOTE_TYPE  => 0);
    $this->node_visits_ratio   = 0;
    $this->visited_nodes_count = 0;

  }

  function setCourseInstance( $id_course_instance )
  {
    $this->id_course_instance = $id_course_instance;
  }

  function setCourse ( $id_course )
  {
    $this->id_course = $id_course;
  }

  /**
   * get_total_visited_nodes
   * If a node type is specified, returns the number of student visits for $node_type nodes.
   * If a node type is not specified, returns the sum of student visits for all the nodes in this course.
   *
   * @param int $node_type - ADA node type, as defined in ada_config.inc.php
   * @return int - number of visited nodes
   */
  function get_total_visited_nodes ($node_type=null)
  {
    if ( !isset($this->course_data) )
    {
      $this->get_course_data();
    }

    if (!is_null($node_type) && strlen($node_type)>0)
    {
      return $this->node_visits_count[$node_type];
    }

    $total_visited_nodes = 0;
    foreach ($this->node_visits_count as $node_type_visits )
    {
      $total_visited_nodes += $node_type_visits;
    }
    return $total_visited_nodes;
  }

  /**
   * history_summary_FN
   * Outputs an html string with some statistics about $this->id_student user activity in
   * $this->id_course_instance course instance.
   *
   * @param int $id_course - optional
   * @return string $html_string
   */
  function history_summary_FN()
  {
    if ( !isset($this->course_data) )
    {
      $this->get_course_data();
    }

    $html_string  = '<p>';
    $html_string .= translateFN('Nodi diversi visitati:') . "<b> $this->visited_nodes_count </b>";
    $html_string .= translateFN('su un totale di:') . "<b> $this->nodes_count </b><br>";
    $html_string .= translateFN('Totale visite:') . "<b>". $this->get_total_visited_nodes()." </b><br>";
    $html_string .= translateFN('Visite per nodo:') . "<b> $this->node_visits_ratio </b><br>";
    return $html_string;
  }

  function history_nodes_visitedpercent_FN($node_types=null)
  {
    return number_format($this->history_nodes_visitedpercent_float_FN($node_types), 0, '.', '');
  }
  /**
   * history_nodes_visitedpercent_float_FN
   *
   * @param int|array $node_types - ADA node typeor array of node types, as defined in ada_config.inc.php
   * @return int - number of visited nodes
   */
  function history_nodes_visitedpercent_float_FN($node_types=null)
  {
    $nodes_percent = $visited = $total = 0;
    if ( !isset($this->course_data) )
    {
      $this->get_course_data();
    }
    if (!is_null($node_types)) {
      if(!is_array($node_types)) $node_types = [$node_types];
      // filter nodes of type LEAF and GROUP
      $filteredAr = array_filter($this->course_data, function($el) use($node_types) {
        return is_array($el) && array_key_exists('tipo', $el) && in_array($el['tipo'], $node_types);
      });
      // each node with a 'numero_visite' greater than zero tells that the node has been visited
      $visited = array_reduce($filteredAr, function($carry, $el) {
        if (array_key_exists('numero_visite', $el) && intval($el['numero_visite'])>0) $carry += 1;
        return $carry;
      }, 0);
      $total = count($filteredAr);
    } else {
      $visited = $this->visited_nodes_count;
      $total = $this->nodes_count;
    }
    if ( $total > 0 )
    {
      $nodes_percent = $visited / $total * 100;
    }
    return floatval($nodes_percent);
  }

  function get_last_nodes ( $num )
  {
    $dh = $GLOBALS['dh'];
    $result = $dh->get_last_visited_nodes( $this->id_student, $this->id_course_instance, $num);
    //verificare il controllo degli errori
    if ( AMA_DataHandler::isError($result) )
    {
      $errObj = new ADA_Error($result, translateFN('Errore nella lettura dei dati'));
    }
    return $result;
  }

  /**
   * history_last_nodes_FN
   *
   * @param int $nodes_num - the number of last accessed nodes for which display infos.
   * @return string $t->getTable() - an html string
   */

  /**
   * @author giorgio 15/mag/2013
   * added $returnHTML parameter
   */
  function history_last_nodes_FN($nodes_num, $returnHTML = true)
  {
    $result = $this->get_last_nodes($nodes_num);
    $formatted_data = $this->format_history_dataFN($result, $returnHTML);

    if ($returnHTML)
    {
	    $t = new Table();
      $t->initTable('0','center','1','1','90%','','','','','0','1');
      $caption = sprintf(translateFN("Ultime %d visite"), $nodes_num);
	    $t->setTable($formatted_data, $caption, $caption);
	    return $t->getTable();
    }
    else return $formatted_data;
  }

  /*
   * controllare bene i metodi time, c'è qualcosa che non quadra nel calcolo del tempo.
   */
  function history_nodes_time_FN()
  {
    if ( !isset($this->total_time) )
    {
      $this->get_visit_time();
    }

    // conversione del valore da secondi ad ore e formattazione
    $int_hours = floor($this->total_time/3600);
    $rest_sec = $this->total_time - ($int_hours * 3600);
    $int_mins = floor($rest_sec/60);
    $int_secs = floor($this->total_time - ($int_hours*3600) - ($int_mins*60));

    $res = sprintf("%02d:%02d:%02d", $int_hours, $int_mins, $int_secs);
    return $res;
  }

  function history_nodes_average_FN()
  {
    if ( !isset($this->course_data) )
    {
      $this->get_course_data();
    }
    if ( !isset($this->total_time) )
    {
      $this->get_visit_time();
    }
    $average = $this->total_time / $this->nodes_count;

    $int_hours = floor($average/3600);

    $rest_sec = $average - ($int_hours * 3600);

    $int_mins = floor($rest_sec/60);

    $int_secs = floor($average - ($int_hours*3600) - ($int_mins*60));

    $res = sprintf("%02d:%02d:%02d", $int_hours, $int_mins, $int_secs);

    return $res;
  }

  /**
   * history_nodes_visited_FN
   *
   * @return string - an html string for a table
   */

  /**
   * @author giorgio 15/mag/2013
   * added $returnHTML parameter
   */
  function history_nodes_visited_FN($returnHTML = true)
  {
    $http_root_dir = $GLOBALS['http_root_dir'];

    if ( !isset($this->course_data) )
    {
      $this->get_course_data();
    }

    // visualizzazione
    $data = array();
    foreach ($this->course_data as $visita )
    {
      if ( $visita['numero_visite'] != null )
      {
        $label1 = translateFN("Nodo:");
        $label2 = translateFN("n visite:");
        $id_node = $visita['id_nodo'];
        $name = $visita['nome'];
        $tot_visit = $visita['numero_visite'];

        $css_classname = $this->getClassNameForNodeType($visita['tipo']);
        if ($returnHTML)
        	$label1Value = "<span class=\"$css_classname\"><a href=\"$http_root_dir/browsing/view.php?id_node=$id_node\">$name</a></span>";
        else
        	$label1Value = $name;
        $histAr = array(
        $label1 => $label1Value,
        $label2 => $tot_visit
        );
        array_push($data,$histAr);
      }
    }
    if ($returnHTML)
    {
	    $t = new Table();
	    $t->initTable('0','center','1','1','90%','','','','','0','1');
	    $t->setTable($data,translateFN("Nodi ordinati per numero di visite"),translateFN("Nodi ordinati per numero di visite"));
	    $res = $t->getTable();
	    return $res;
    } else {
    	return $data;
    }
  }

  /**
   * history_nodes_list_filtered_FN
   *
   * @param int $period - number of days for which display user activity in $this->id_course_instance.
   * @return string $t->getTable() - an html string
   */
  /**
   * @author giorgio 16/mag/2013
   * added $returnHTML parameter
   */
  function history_nodes_list_filtered_FN( $period, $returnHTML = true )
  {
    $dh = $GLOBALS['dh'];

    $start = ( $period > 0 ) ? (time() - $period*86400) : 0;

    $result = $dh->get_last_visited_nodes_in_period( $this->id_student, $this->id_course_instance, $start );
    //verificare il controllo degli errori
    if ( AMA_DataHandler::isError($this->course_data) )
    {
      $errObj = new ADA_Error($this->course_data, translateFN("Errore nella lettura dei dati"));
    }

    if ($period!=0)
    	$caption = translateFN("Nodi visitati negli ultimi $period giorni");
    else
    	$caption = translateFN("Tutti i nodi visitati");

    $formatted_data = $this->format_history_dataFN($result);

    if ($returnHTML)
    {
    	$t = new Table();
    	$t->initTable('0','center','1','1','90%','','','','','0','1');

    	$t->setTable($formatted_data,$caption,$caption);
    	if (!empty($formatted_data)) return $t->getTable();
    	else return "Nessun nodo trovato";
    }
    else {
    	$formatted_data['caption'] = $caption;
    }
    return  $formatted_data;
  }

  /**
   * get_historyFN
   *
   * Returns an html string containing a table with all of the user activity in $this->id_course_instance.
   * @return string - an html string
   */
  /**
   * @author giorgio 16/mag/2013
   * added $returnHTML parameter
   */
  function get_historyFN($returnHTML = true)
  {
    return $this->history_nodes_list_filtered_FN(0,$returnHTML);
  }

  /**
   * PRIVATE METHODS
   */

  /**
   * get_course_data
   * Fetches an associative array containing id_node, node name, node type, visits number
   * for each node in the course instance $this->id_course_instance.
   * Visits number refers to visits made by student with id $this->id_student.
   *
   * @param int $id_course - optional
   * Sets $this->nodes_count, $this->node_visits_count, $this->node_visits_ratio.
   */
  function get_course_data ()
  {
    $dh = $GLOBALS['dh'];

    if ( !isset( $this->id_course) || (isset($this->id_course) && is_null($this->id_course)))
    {
      //print("<BR>query su id_corso<BR>");
      $this->id_course = $dh->get_course_id_for_course_instance( $this->id_course_instance );
      if ( AMA_DataHandler::isError($this->id_course) )
      {
        $errObj = new ADA_Error($this->id_course, translateFN("Errore nella lettura dei dati"));
      }
    }

    $this->course_data = $dh->get_student_visits_for_course_instance( $this->id_student, $this->id_course, $this->id_course_instance );
    //verificare il controllo degli errori
    if ( AMA_DataHandler::isError($this->course_data) )
    {
      $errObj = new ADA_Error($this->course_data, translateFN("Errore nella lettura dei dati"));
    }
    // in this case, for counting nodes we are taking in account notes too.
    $this->nodes_count = count($this->course_data);
    foreach ( $this->course_data as $course_node )
    {
      if ( $course_node['numero_visite'] != null )
      {
        $this->visited_nodes_count++;
        $this->node_visits_count[(int)$course_node['tipo']] += $course_node['numero_visite'];
      }
      // in this case we do not take in account notes
      //if ( $course_node['tipo'] < 2 )
      //{
      //    $this->nodes_count++;
      //}
    }
    if ($this->visited_nodes_count > 0) {
      $this->node_visits_ratio = round($this->get_total_visited_nodes() / $this->visited_nodes_count, 2);
    }
    else {
      $this->node_visits_ratio = 0;
    }
  }

  /**
   * get_visit_time
   * Fetches an associative array containing history information for nodes in $this->id_course_instance
   * visited by student $this->id_student.
   * Uses the fetched array to calculate $this->total_time time spent by student visiting
   * the course instance.
   */
  function get_visit_time ()
  {
    $dh = $GLOBALS['dh'];
    $visit_time = $dh->get_student_visit_time ( $this->id_student, $this->id_course_instance );
    //verificare il controllo degli errori
    if ( AMA_DataHandler::isError($visit_time) )
    {
      $errObj = new ADA_Error($visit_time, translateFN("Errore nella lettura dei dati"));
    }

    $nodes_time = 0 ;
    if (isset($visit_time[0])) {
	    $n_session = $visit_time[0]['session_id'] ;
	    $n_start = $visit_time[0]['data_visita'] ;
	    $n_time_prec = $visit_time[0]['data_visita'] ;
    } else {
    	$n_session = null ;
    	$n_start = null ;
    	$n_time_prec = null ;
    }
    $num_nodi = count($visit_time);
    foreach($visit_time as $key=>$val){
      // controlla se vi e' stato cambio del valore del session_id
      if($val['session_id'] != $n_session){
        $nodes_time  = $nodes_time + ($n_time_prec - $n_start); // + ADA_SESSION_TIME;
        $n_session   = $val['session_id'];
        $n_start     = $val['data_visita'];
        $n_time_prec = $val['data_visita'] ; //ora di entrata nel primo nodo visitato nella sessione
        // assegna il valore di data uscita del "nodo precedente"
      }
      else if ( $key == ($num_nodi-1) )
      {
        $nodes_time = $nodes_time + $val['data_visita'] - $n_start;
      }
      else
      {
        $n_time_prec = $val['data_uscita'];
      }
    }

    $this->total_time = $nodes_time;
    unset($visit_time);
  }

  /**
   * @author giorgio 15/mag/2013
   * added $returnHTML parameter
   */
  function format_history_dataFN($user_historyAr, $returnHTML = true){
    //global $dh, $http_root_dir;
    $dh = $GLOBALS['dh'];
    $error = $GLOBALS['error'];
    $http_root_dir = $GLOBALS['http_root_dir'];
    // $debug = $GLOBALS['debug'];

    $data = array();
    //if (!$user_historyAr) {
    //    $user_historyAr = $this->historyAr;
    //}
    foreach ($user_historyAr as $historyHa){
      $visit_date = $historyHa['data_visita'];
      $exit_date = $historyHa['data_uscita'];
      //$id_course = $historyHa[4];
      $id_visited_node = $historyHa['id_nodo'];
      // $id_visited_node = $id_course."_".$historyHa[0]; Id_node gia' completo
      $u_time_spent = ($exit_date - $visit_date);

      $int_hours = floor($u_time_spent/3600);
      $rest_sec = $u_time_spent - ($int_hours * 3600);
      $int_mins = floor($rest_sec/60);
      $int_secs = floor($u_time_spent - ($int_hours*3600) - ($int_mins*60));

      $time_spent = sprintf("%02d:%02d:%02d", $int_hours, $int_mins, $int_secs);

      if ($time_spent == '00:00:00') {
        $time_spent = '-';
      }

      $date = ts2dFN($visit_date);
      $time = ts2tmFN($visit_date);
      // $dh = new Node($id_visited_node);
      //$dataHa = $dh->get_node_info($id_visited_node);
      // $dataHa = Node::get_node_info($id_visited_node);


      $name = $historyHa['nome'];
      // vito, 16 feb 2009
      //            $icon = $this->getIcon($historyHa['tipo']);
      $css_classname = $this->getClassNameForNodeType($historyHa['tipo']);
      $label = translateFN('Nodo');
      $label2 = translateFN('Data');
      $label3 = translateFN('tempo trascorso');
      // vito, 16 feb 2009
      //            $histAr = array($label=>"<a href=" . $http_root_dir . "/browsing/view.php?id_node=$id_visited_node>".$icon." $name</a>",
      //                            $label2=>$date." ".$time,
      //                            $label3=>$time_spent);

      if ($returnHTML) $link_to_node = '<span class="'.$css_classname.'"><a href="'.$http_root_dir.'/browsing/view.php?id_node='.$id_visited_node.'">'.$name.'</a></span>';
      else $link_to_node = $name;
      $histAr = array($label=> $link_to_node,
      $label2=>$date." ".$time,
      $label3=>$time_spent);

      array_push($data,$histAr);
    }
    return $data;
  }

  /**
   * getIcon
   * Used to get the right icon based on node type
   * @param int $node_type
   * @return string - an html string containing <img> tag.
   */
  function getIcon( $node_type )
  {
    switch ($node_type ){
      case ADA_GROUP_TYPE:
        $icon = "<img src=\"img/group_ico.png\" border=0>";
        break;
      case ADA_PRIVATE_NOTE_TYPE:
        $icon = "<img src=\"img/p_nota_pers.png\" border=0>";
        break;
      case ADA_NOTE_TYPE:
        $icon = "<img src=\"img/note_ico.png\" border=0>";
        break;
      case ADA_LEAF_TYPE:
        $icon = "<img src=\"img/node_ico.png\" border=0>";
        break;
      case ADA_STANDARD_EXERCISE_TYPE:
      default:
        $icon = "<img src=\"img/exer_ico.png\" border=0>";
        break;
    }
    return $icon;
  }
  function getClassNameForNodeType($node_type) {
    switch($node_type) {
      case ADA_NOTE_TYPE:
        return "ADA_NOTE_TYPE";

      case ADA_PRIVATE_NOTE_TYPE:
        return "ADA_PRIVATE_NOTE_TYPE";

      case ADA_GROUP_TYPE:
        return "ADA_GROUP_TYPE";

      case ADA_LEAF_TYPE:
      default:
        return "ADA_LEAF_TYPE";
    }
  }
}
?>