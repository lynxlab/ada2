<?php
/**
 *
 *
 * @package
 * @author 		Stefano Penge <steve@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		utilities
 * @version		0.1
 */

function var_modify($proprieta,$valore){
  // interfaccia per la modifica delle proprieta' di un oggetto
  // (NON USATA)
  if (in_array($proprieta,$this->open_vars)){
    $this->$proprieta = $valore;
  }
}

function or_null($s){
  if (!$s || $s == "''")
  return "NULL";
  else
  return $s;
}

function mydebug($line,$file,$vars){
  $debug = $GLOBALS['debug'];
  if ($debug){
    print "<i>Debugging line $line of script $file</i>.<br>";
    $type = gettype($vars);
    switch ($type){
      case 'array':
        foreach  ($vars as $k=>$v)
        print "$k: $v<br>";
        break;
      case 'object':
        print_vars($vars);
        break;
      default:
        print ($vars);
    }

  }
}

//--------------------------------------------
// funzione per la creazione link
// passati il valore della label e il file di destinazione
function crea_link($label,$file_to_go){
  $str = "<a href=\"$file_to_go\">$label</a>";
  return $str ;
}


///////////////////////////////
/*
 time and date functions
 */

/**    Returns the offset from the origin timezone to the remote timezone, in seconds.
*    @param $remote_tz;
*    @param $origin_tz; If null the servers current timezone is used as the origin.
*    @return int;
*/
function get_timezone_offset($remote_tz, $origin_tz = null) {
/*
 *
	switch ($remote_tz) {
		case "Europe/Roma":
			$offset = 0;
			break;
		case "Europe/Madrid":
			$offset = 0;
			break;
		case "Europe/Bucharest":
			$offset = -3600;
			break;
		case "Europe/Sofia":
			$offset = -3600;
			break;
		case "Europe/London":
			$offset = 3600;
			break;
	}

/*
 * NECESSARIO PHP 5.2.X
 *
 */
    if($origin_tz === null) {
    	$origin_tz = SERVER_TIMEZONE;
    }
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $remote_dtz->getOffset($remote_dt) - $origin_dtz->getOffset($origin_dt);

    return $offset;
}

function mytimer ($op=""){
  $start_time =  $GLOBALS['start_time'];
  if (empty($op)){
    $start_time = time();
    $time_elapsed = 0;
  } else {
    $end_time = time();
    $time_elapsed = ($end_time-$start_time);
  }
  return " $op: ".$time_elapsed;
}

function today_dateFN(){
  $now = time();
  return ts2dFN($now);
}

function today_timeFN(){
  $now = time();
  return ts2tmFN($now);
}


function ts2dFN($timestamp=""){
  if (empty($timestamp))
  $timestamp = time();

  $dataformattata = strftime(ADA_DATE_FORMAT, $timestamp);   ; // AMA version
  /*
  $data = getdate($timestamp);
  $dataformattata = $data['mday']."/".$data['mon']."/".$data['year'];
  */
  return $dataformattata;

}


function ts2tmFN($timestamp=""){
  if (empty($timestamp))
  $timestamp = time();
  //$data = getdate($timestamp);
  $dataformattata = date("H:i:s",$timestamp);
  //$dataformattata = $data['hours'].":".$data['minutes'].":".$data['seconds'];
  return $dataformattata;
}



function sumDateTimeFN ($arraydate){
  // array date is dd/mm/yy,hh:mm:ss
  // $date = dt2tsFN($arraydate[0]);
  // $time = tm2tsFN($arraydate[1]);
  $date = $arraydate[0];
  $time = $arraydate[1];

  // Data
  $date_ar = explode("/",$date);
  if (count($date_ar)<3)
  return 0;
  $giorno =$date_ar[0];
  $mese = $date_ar[1];
  $anno =$date_ar[2];

  // ORA
  $time_ar = explode(":",$time);
  switch (count($time_ar)){
    case 0:
      $time_ar[]="00";
      $time_ar[]="00";
      $time_ar[]="00";
      break;
    case 1:
      $time_ar[]="00";
      $time_ar[]="00";
      break;
    case 2:
      $time_ar[]="00";
      break;
  }

  $ora =$time_ar[0];
  $min =$time_ar[1];
  $sec =$time_ar[2];


  $timestamp = mktime($ora,$min,$sec,$mese,$giorno,$anno);
  //$timestamp = ($date+$time);
  // return ($date+$time);
  return ($timestamp);
}




function dt2tsFN ($date){
  $date_ar = split ('[\\/.-]', $date);
  if (count($date_ar)<3)
  return 0;
  $format_ar = split ('[/.-]',ADA_DATE_FORMAT);
  if ($format_ar[0]=="%d"){
    $giorno = (int)$date_ar[0];
    $mese = (int)$date_ar[1];
  } else {   // english-like format
    $giorno = (int)$date_ar[1];
    $mese = (int)$date_ar[0];
  }

  $anno =(int)$date_ar[2];

  $unix_date = mktime(0,0,0,$mese,$giorno,$anno,-1);
  return $unix_date;

}


function tm2tsFN ($time){
  $time_ar = explode(":",$time);
  switch (count($time_ar)){
    case 0:
      $time_ar[]="00";
      $time_ar[]="00";
      $time_ar[]="00";
      break;
    case 1:
      $time_ar[]="00";
      $time_ar[]="00";
      break;
    case 2:
      $time_ar[]="00";
      break;
  }

  $ora =$time_ar[0];
  $min =$time_ar[1];
  $sec =$time_ar[2];
  $unix_time = mktime($ora,$min,$sec,1,1,1970);
  return $unix_time;
}



/* FUNZIONI DI INDICIZZAZIONE E ORDINAMENTO*/
// COMPARAZIONE NODI:

function compare_dateFN ($a,$b){
  $dh = $GLOBALS['dh'];
  $error= $GLOBALS['error'];
  $debug= $GLOBALS['debug'];
  $a_dataHa = $dh->get_node_info($a);
  $b_dataHa = $dh->get_node_info($b);

  $a_node_date = $a_dataHa['creation_date'];
  $b_node_date = $b_dataHa['creation_date'];
  if ($a_node_date == $b_node_date) return 0;

  $a_utime = dt2tsFN($a_node_date);
  $b_utime = dt2tsFN ($b_node_date);
  return ($a_utime < $b_utime) ? 1 : -1;
}


function compare_nameFN ($a,$b){
  //global $dh,$error,$debug;
  $dh = $GLOBALS['dh'];
  $error= $GLOBALS['error'];
  $debug= $GLOBALS['debug'];

  $a_dataHa = $dh->get_node_info($a);
  $b_dataHa = $dh->get_node_info($b);
  $a_node_name = $a_dataHa['name'];
  $b_node_name = $b_dataHa['name'];
  if ($a_node_name == $b_node_name) return 0;
  return ($a_node_name < $b_node_name) ? 1 : -1;
}

///////////////////////////////
/*
 array functions
 */

function aasort(&$array, $args) {
  /*
   Syntax: aasort($assoc_array, array("+first_key", "-second_key", etc..));
   */
  $args = array_reverse($args);
  if (count($array) > 0) {
    foreach($args as $arg) {
      $temp_array = $array;
      $array = array();
      $order_key = substr($arg, 1, strlen($arg));

      foreach($temp_array as $index => $nirvana) $sort_array[$index] = $temp_array[$index][$order_key];

      ($arg[0] == "+") ? (asort($sort_array)) : (arsort($sort_array));

      foreach($sort_array as $index => $nirvana) $array[$index] = $temp_array[$index];
    }
  }

} //end aasort


function masort ($array, $arg,$sort_order=1,$sort_method=SORT_STRING) {
  // multiple array sort

  /* works with typical AMA array, ie:
   array (
   array (asd,3f,asdf),
   array (5,asdf,34)
   )

   $arg: field to be used as key
   $sort_order : 1 (default) or -1 (reverse)
   */
  $temparray = array();
  $i=0;
  foreach ($array as $subarray){
    $key = ucfirst(strtolower(strip_tags($subarray[$arg])));
    if  (!isset($temparray[$key]))
    $temparray[$key]= $i;
    else
    $temparray[$key].=",$i";
    $i++;
    // echo$key.":".$temparray[$key]."<br>";
  }

  $arraycopy = array();
  $max = count ($array);
  $i=0;
  if ($sort_order==-1)
  krsort($temparray,$sort_method);
  else
  ksort($temparray,$sort_method);
  foreach ($temparray as $key=>$value){
    $keyAr = explode(",",$value);
    foreach ($keyAr as $keyElem){
      $arraycopy[$i] = $array[$keyElem];
      $i++;
    }
  }
  return $arraycopy;
}


function list_array ($array) {
  // CONVERTE UN ARRAY IN STRINGA
  $str='';
  while (list ($key, $value) = each ($array)) {
    $str .= "<b>$key:</b> $value<br>\n";
  }
  return $str;
}


// class utility functions

function print_vars($obj) {
  $arr = get_object_vars($obj);
  while (list($prop, $val) = each($arr))
  echo "\t$prop = $val<br>\n";
}

function print_methods($obj) {
  $arr = get_class_methods(get_class($obj));
  foreach ($arr as $method)
  echo "\tfunction $method()<br>\n";
}
/*
 function class_parentage($obj, $class) {
 global $$obj;
 if (is_subclass_of($$obj, $class)) {
 echo "Object $obj belongs to class ".get_class($$obj);
 echo " a subclass of $class\n";
 } else {
 echo "Object $obj does not belong to a subclass of $class\n";
 }
 }
 */
///////////////////////////////

function get_param_stringFN(){
  /* session id propagation  */

  switch (ADA_SESSION_MODE)  {
    case 'auto':          //  propagation by  URL, nothing to do
    case 'cookies':       // uses cookies
      $session_id_par = '';
      break;
    case 'manual':           //  we have to propagate it manually in all links
    default:
      $session_id_par = "SID&"; //this is the string to add to every link
  }
  return  $session_id_par;
}

///////////////////////////////

function check_javascriptFN($browser) {
  /* ********
   Check Browser version
   */
  $javascript_ok = 0;
  // $browser = HTTP_USER_AGENT;
  if (stristr($browser,"Mozilla") and ($browser[8] > 3)) $javascript_ok = 1;
  $debug = 1;
  return $javascript_ok;
  // *** End Check browser
}

///////////////////////////////

function whoami(){

  $PHP_SELF = $_SERVER['PHP_SELF'];
  $SCRIPT_NAME= $_SERVER['SCRIPT_NAME'];

  if (!isset($PHP_SELF)){      // not available in PHP>4.2.1
    if (isset($_SERVER['PHP_SELF'])){
      $parent = $_SERVER['PHP_SELF'];
    } else {
      // $parent = $SCRIPT_NAME; // not available in PHP>4.2.1
      $parent =  $_SERVER['SCRIPT_NAME'];
    }
  } else {
    $parent = $PHP_SELF; //register_globals = off AND   PHP<4.2.2
  }

  $self = array_shift(explode('.',basename($parent)));  // = es. view
  $GLOBALS['SELF'] = $self;
  return $self;
}

///////////////////////////////

/*
 * vito 14 nov 2008: added the $filename parameter, used to specify a
 * different file in which execute logging
 */
function log_this($msg, $level, $filename=NULL){

  // $log_treshold = 0; // no log  at all
  // $log_treshold = 5; // logging all db operations
  // $log_treshold = 4; // logging only the beginning of db operations

  // vito, 14 nov 2008
  if ($filename == NULL) {
    $log_filename = $GLOBALS['log_filename'];
  }
  else {
    $log_filename = $GLOBALS['root_dir'].DIRECTORY_SEPARATOR.$filename;
  }

  $log_treshold= $GLOBALS['log_treshold'];
  //$debug= $GLOBALS['debug'];
  if ($level <= $log_treshold){
    $log_message = date ("d/m H:i:s - ").$msg."\n";
    $fp = fopen($log_filename, "a+");
    if ($fp){
      fwrite($fp, $log_message);
      fclose($fp);
    }
  }
}
///////////////////////////////
// HTM Entities IN and OUT

function get_htmlspecialchars( $given, $quote_style = ENT_QUOTES ){
  return htmlentities( unhtmlentities(  $given ) , $quote_style, ADA_CHARSET );
}

function unhtmlentities ($string)  {
  $trans_tbl = get_html_translation_table (HTML_ENTITIES);
  $trans_tbl = array_flip ($trans_tbl);
  $ret = strtr ($string, $trans_tbl);
  return preg_replace('/&#(\d+);/me',
     "chr('\\1')",$ret);
}

function convertDoc2HTML($txt){
  $len = strlen($txt);
  $res = "";
  for($i = 0; $i < $len; ++$i) {
    $ord = ord($txt{$i});
    // check only non-standard chars
    if($ord >= 126){
      $res .= "&#".$ord.";";
    }
    else {
      // escape ", ' and \ chars
      switch($ord){
        case 34 :
          $res .= "\\\"";
          break;
        case 39 :
          $res .= "\'";
          break;
        case 92 :
          $res .= "\\\\";
          break;
        default : // the rest does not have to be modified
          $res .= $txt{$i};
      }
    }
  }
  return $res;
}

/**
* function substr_gentle
*
* Return a delimited string without truncate the last word.
*
* @author Valerio
*
* @param  string  $str          - a text string
* @param  int  $limit - an array with additional parameters
* @return string $new_str       - a substring with no truncated ending word
*/
function substr_gentle($str, $limit)
{
	$str = str_replace("\n",'',substr($str,0,$limit+50));
	$array = explode("\n",wordwrap($str,$limit));
	return $array[0];
}

/*
 / finds the last occurence of a string into a sting(text)
 */
function lastIndexOf($haystack,$needle) {
  $index = strpos(strrev($haystack), strrev($needle));
  $index = strlen($haystack) - strlen(index) - $index;
  return $index;
}


function in_multi_array($needle, $haystack)
{
  $in_multi_array = false;
  if(in_array($needle, $haystack)){
    $in_multi_array = true;
  }else{
    for($i = 0; $i < sizeof($haystack); $i++){
      if(is_array($haystack[$i])){
        if(in_multi_array($needle, $haystack[$i])) {
          $in_multi_array = true;
          break;
        }
      }
    }
  }
  return $in_multi_array;
}


function dirTree($path) {

  /*
   **** dato un percorso ritorna l'elenco delle directory ****
   */
  $dirlist = "";
  if ( $handle = opendir ( $path ) )  {
    while ( false !== ( $file = readdir ( $handle ) ) ) {
      if ( $file != '.' && $file != '..' ) {
        $dir_path = $path ."/". $file ;
        if (is_dir($dir_path)) $dirlist[] = $file;
      }
    }
    closedir ( $handle ) ;
  }
  return ( $dirlist ) ;
}


function leggidir($dir,$ext=""){
  return read_dir($dir);
}

function read_dir($dir,$ext=""){
  /*
   **** dato un percorso ritorna l'elenco dei file dei tipi consentiti ****
   */
  $nomedata = array();
  $elencofile = "";
  // vito, 31 ottobre 2008: aggiunto il check $ext!=""
  if (isset($ext) && $ext != "")
  $allowed_extAr = array($ext);
  else
  $allowed_extAr = array(
			'txt',
			'doc',
			'rtf',
			'ppt',
			'xls',
			'htm',
			'html',
			'pdf',
			'zip',
			'jpg',
			'jpeg',
			'gif',
			'png',
			'mp3',
			'avi',
			'sxw',
			'odt',
  			'xlsx',
  			'xltx',
  			'docx',
  			'dotx',
  			'pptx',
  		    'ppsx',
  		    'potx'
			);

			$dirid = @opendir($dir);
			if ($dirid){
			  $i = 0;
			  while (($file = readdir($dirid))!=false){

			    $fileAr = explode('.',$file);
			    $ext = strtolower(array_pop($fileAr));
			    if (in_array($ext,$allowed_extAr))
			    {
			      //$elencofile[$i]['file'] = $dir."/".$file;
			      // vito, 30 mar 2009
			      $elencofile[$i]['path_to_file'] = $dir."/".$file;
			      $filetime = date("d/m/y",filemtime($dir."/".$file));
			      $elencofile[$i]['data'] = $filetime;
			      $elencofile[$i]['file'] = $file;
			      $i++;
			    }
			  }
			  closedir ($dirid);
			  // va ordinato ora
			  if (is_array($elencofile)){
			    sort($elencofile);
			    reset($elencofile);
			  }
			}
			return $elencofile;
}


function import_session_variables(){
  // set global variables to session variables
  // deprecated !
  // use with care

  foreach ($_SESSION as $sess_key=>$sess_var)
  $GLOBALS[$sess_key] = $sess_var;
}
/**
 * convert mimeType of uploaded files to group type Ada
 *
 * @param 	string 	$mime tipe of uploaded files
 * @param 	hashes 	$mimetypeHa hashes with ada group type
 * @return 	int		ada_group_type
 */
function mimeToType($mime,$mimetypeHa)
{
  return $mimetypeHa[$mime]['type'];
}
function buildMediaXml($dataHa)
{
  return  $strXml ="<MEDIA TYPE=\"".$dataHa['tipo']."\" VALUE=\"".$dataHa['nome_file']."\" >";
}
function buildLinkXml($dataHa)
{
  return $strXml = "<LINK TYPE=INTERNAL VALUE=\"".$dataHa['id_nodo_to']."\">";
}
/*
 * Convert the letter of word in image
 * @param 	string 	$word word to translate
 * @param 	string 	$img_dir directory containg the letter image
 * @return 	string	string containig the images
 *
 */

function converti_dattiloFN($word, $img_dir){
	$dattilo_string = "";

	if ((isset($word)) and ($word!= null)){
		$end = strlen($word);
			$dattilo_string = $word;
		for ($l=0;$l<$end; $l++){
			$lettera = strtolower($word[$l]);
			$dattilo_string.="<img src='$img_dir/$lettera.jpg' alt=$lettera>";
		}

	} else {
		$letAr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		foreach ($letAr as $lettera){
			$dattilo_string.=strToUpper($lettera)."<img src='$lettera.jpg' alt=$lettera border=0>";
		}
	}

	return $dattilo_string;
}

/*
 * Send Location header to browser, causing redirect
 * @param	string	$url url to redirect the browser
 *
 */
function redirect($url) {
	header('Location: '.$url);
	exit();
}
/**
 * 
 * @param type $num
 * @param type $digits
 * @return number truncated at digits
 */
function truncate ($num, $digits = 0) {
   $shift = pow(10 , $digits);
   return ((floor($num * $shift)) / $shift);
}

?>
