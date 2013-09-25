<?php
/**
 *
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author      Vito Modena <vito@lynxlab.com>
 * @author
 * @author
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		index
 * @version		0.1
 */
/**
 *
 *
 *
 */
class DataValidator
{
  public static function validate_local_filename($filename) {
    if (self::validate_not_empty_string($filename)) {
      $pattern = '/^[a-zA-Z\_]+\.[a-zA-Z0-9\.]+$/';
      if(preg_match($pattern, $filename)) {
        return $filename;
      }
    }
    return FALSE;
  }

  public static function validate_string($string) {
    if(!isset($string) || empty($string)) {
      return '';
    }
    else {
      return $string;
    }
    return FALSE;
  }

  public static function validate_not_empty_string($string) {
    if(isset($string) && !empty($string)) {
      return $string;
    }
    return FALSE;
  }
  
  public static function validate_birthdate ($date) {
  	$ok = self::validate_date_format($date);
  	if ($ok)
  	{
  		list ($giorno, $mese, $anno) = explode ("/",$date);
  		
  		$check = mktime(0, 0, 0, $mese, $giorno, $anno);
  		$today = mktime(0, 0, 0, date("m"), date("d"), date("y"));
  		
  		if ($check > $today)  { $ok = false; }
  		else if ($anno < 1900){ $ok = false; } 
  		else if (!checkdate($mese, $giorno, $anno)) { $ok = false; }
  		
  	}
  	return $ok;
  }

  public static function validate_date_format($date) {
    if(isset($date) && !empty($date)) {
      $pattern = '/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/';
      if(preg_match($pattern,$date)) {
        return $date;
      }
    }
    return FALSE;
  }

  public static function validate_event_token($event_token) {
    if(isset($event_token) && !empty($event_token)) {
      $pattern = '/^[1-9][0-9]*_[1-9][0-9]*_[1-9][0-9]*_[1-9][0-9]+$/';
      if(preg_match($pattern, $event_token)) {
        return $event_token;
      }
    }
    return FALSE;
  }

  public static function validate_action_token($action_token) {
    if(isset($action_token) && !empty($action_token)) {
      $pattern = '/^[a-f0-9]{40}$/';
      if(preg_match($pattern, $action_token)) {
        return $action_token;
      }
    }
    return FALSE;
  }

  public static function is_uinteger($value) {
    if(isset($value) && !empty($value)) {
     if(is_int($value) && $value >= 0) {
       return $value;
     }

     if(is_string($value) && ctype_digit($value)) {
       return (int)$value;
     }

    }
    return false;
  }

  public static function validate_node_id($node_id) {
    if(isset($node_id) && !empty($node_id)) {
      $pattern = '/^[1-9][0-9]*\_[0-9]*$/';
      if(preg_match($pattern,$node_id)) {
        return $node_id;
      }
    }
    return false;
  }

  public static function validate_testername($testername, $multiprovider = true) {
    if(isset($testername) && !empty($testername)) {
    /**
	 * giorgio, set proper pattern validation depending on multiprovider environment
	 * modified 14/ago/2013 if the commented lines are kept, admin will not view
	 * testers whose name is NOT 'clientX' in singleprovider mode.
	 * Thought that this was not a desirable behaviour...
	 * anyway, i keep passing the multiprovider params for
	 * easy switching to whatsoever behaviour is desired.
     */
//     if ($multiprovider===true)
//       $pattern = '/^(?:client)[0-9]{1,2}$/';
//     else
     $pattern = '/^\w+$/';
      if(preg_match($pattern,$testername)) {
        return $testername;
      }
    }
    return false;
  }

  // TODO: definire minima e massima lunghezza per lo username
  public static function validate_firstname($firstname) {
    if(isset($firstname) && !empty($firstname)) {
    //  $pattern = '/^$/';
    //  if (preg_match($pattern, $firstname)) {
        return $firstname;
    //  }
    }
    return false;
  }

  // TODO: definire minima e massima lunghezza per lo username
  public static function validate_lastname($lastname) {
    if (isset($lastname) && !empty($lastname)) {
     // $pattern = '/^$/';
     // if (preg_match($pattern, $firstname)) {
        return $lastname;
     // }
    }
    return false;
  }

  // TODO: definire minima e massima lunghezza per lo username
  public static function validate_username($username) {
    /* username is the user's email
     * ->  return self::validate_email($username);
     * */

     if(isset($username) && !empty($username)) {
      $pattern = '/^[A-Za-z0-9_][A-Za-z0-9_@\-\.]{7,255}$/';
      if (preg_match($pattern, $username)) {
        return $username;
      }
    }
    return false;

  }

  // TODO: definire minima e massima lunghezza per la password
  public static function validate_password($password, $passwordcheck) {
    if(isset($password) && !empty($password) && isset($passwordcheck)
       && !empty($passwordcheck) && $password == $passwordcheck
    ) {
      $pattern = '/^[A-Za-z0-9_\.]{8,40}$/';
      if (preg_match($pattern, $password)) {
        return $password;
      }
    }
    return false;
  }

  public static function validate_phone($phone) {
    if(!isset($phone)) {
      return '';
    }
    return $phone;
  }

 public static function validate_age($age) {
    if(!isset($age)) {
      return '';
    }
    if (is_numeric($age)){
      if ((17<$age) && ($age<99)){
          return $age;
      }
    }
    return false;
 }

  public static function validate_email($email) {
    if(isset($email) && !empty($email)) {
      $email_pattern = '/(?:[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\'\[\]]+)@(?:(?:(?:[a-z0-9][a-z0-9\-_\[\]]*\.)+(?:aero|arpa|biz|com|cat|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel|mobi|[a-z]{2}))|(?:[0-9]{1,3}(?:\.[0-9]{1,3}){3})|(?:[0-9a-fA-F]{1,4}(?:\:[0-9a-fA-F]{1-4}){7}))$/';

      if(preg_match($email_pattern, $email)) {
        return $email;
      }
    }
    return false;
  }

  public static function validate_url($url) {
    if(isset($url) && !empty($url)) {

    /*
	 * regular expression for url matching
	 *
	 * allowed_protocols = (?:http|https|ftp)
	 * separator         = (?::\/\/)
     * authentication    = (?:[a-z0-9]+(?::[a-z0-9]+)?@)
	 * domain_name       = (?:(?:[a-z0-9][a-z0-9\-_\[\]]*\.)+(?:aero|arpa|biz|com|cat|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel|mobi|[a-z]{2}))
	 * ipv4_address      = (?:[0-9]{1,3}(?:\.[0-9]{1,3}){3})
	 * ipv6_address      = (?:[0-9a-fA-F]{1,4}(?:\:[0-9a-fA-F]{1-4}){7}))
	 * port              = (?::[0-9]{1,5})
	 * directory         = (?:\/[a-z0-9_\-\.~+%=&,$'():;*@\[\]]*)*?(?:\/?[?a-z0-9+_\-\.\/%=&,$'():;*@\[\]]*)
	 * query             = (?:\/[a-z0-9_\-\.~+%=&,$'():;*@\[\]]*)*?(?:\/?[?a-z0-9+_\-\.\/%=&,$'():;*@\[\]]*)
	 * anchor            = (?:#[a-z0-9_\-\.~+%=&,$'():;*@\[\]]*)
	 *
	 * url_pattern = allowed_protocols separator authentication? (?:domain_name|ipv4_address|ipv6_address) port? directory? query? anchor?
	 */
      // al momento non è presente la parte relativa ad authentication
      $url_pattern = '(?:http|https|ftp)(?::\/\/)(?:(?:(?:[a-z0-9][a-z0-9\-_\[\]]*\.)+(?:aero|arpa|biz|com|cat|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel|mobi|[a-z]{2}))|(?:[0-9]{1,3}(?:\.[0-9]{1,3}){3})|(?:[0-9a-fA-F]{1,4}(?:\:[0-9a-fA-F]{1-4}){7}))(?::[0-9]{1,5})?(?:\/[a-z0-9_\-\.~+%=&,$\'():;*@\[\]]*)*?(?:\/?[?a-z0-9+_\-\.\/%=&,$\'():;*@\[\]]*)?(?:#[a-z0-9_\-\.~+%=&,$\'():;*@\[\]]*)?$/i';

      if(preg_match($url_pattern, $url)) {
        return $url;
      }
      return false;
    }
    return false;
  }
}
?>