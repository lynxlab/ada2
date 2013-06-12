<?php
/*
 * Created on 06/Jan/2010
 * FORGET_FUNCTIONS
 * @package	browsing
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		    forget_functions
 * @version		0.1
 */

// Queste funzioni dovrebbero stare in AMA

	function get_request_from_token($token,$type = 0){
	  // returns user_id, date  from table "requests"
	   
	  $delta_time = 1000;
	  $date_limit  = AMA_DataHandler::date_to_ts("now") - $delta_time;
	  // select * from table requests where token = $token and date < $date_limit;
	  // if ok:
	  $requestInfo = array();
	  return $requestInfo;
	  
	}
	
	function add_request($user_id, $type = 0){
	  // insert user_id, date, type  into table "requests"
	   
	  $date = AMA_DataHandler::date_to_ts("now") ;
	  $token = sha1($date); // o altro algoritmo
	  $status = ADA_REQUEST_STATUS_SET;
	  // insert  $date, $userid, $token, $type, $status
	  // if ok:
	  return $token;
	  
	}
	
	function update_request($user_id, $token,  $type = 0){
	  // cancel a request
	  $status = ADA_REQUEST_STATUS_CANCELLED;
	  // update requests set  status = $status where user_id =  $userid AND  token =  $token
	  // if ok:
	  return $token;
	          
	}
	
     function remove_request($user_id, $token,  $type = 0){
	  // permanently remove a request
	  $status = ADA_REQUEST_STATUS_CANCELLED;
	  // delete from requests where user_id =  $userid AND  token =  $token and $status  = ADA_REQUEST_STATUS_CANCELLED;
	  // if ok:
	  return $token;
	  
	}
	

?>