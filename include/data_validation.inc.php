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
     $pattern = '/^(\w|-)+$/';
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


  public static function validate_password($password, $passwordcheck) {
   /**
    	 * @author steve 28/mag/2020
    	 *
    	 * adding  variables for min and max length of password
       * @todo move in configuration
    	 */
    $minLen = 8;
    $maxLen = 40;

    if(isset($password) && !empty($password) && isset($passwordcheck)
       && !empty($passwordcheck) && $password == $passwordcheck
    ) {
      $pattern = '/^[A-Za-z0-9_\.]{'.$minLen.','.$maxLen.'}$/';
      if (preg_match($pattern, $password))  {
        return $password;
      }
    }
    return false;
  }

    public static function validate_password_modified($password, $passwordcheck) {
    if(isset($password) && !empty($password) && isset($passwordcheck)
       && !empty($passwordcheck) && $password == $passwordcheck
    ) {
      $pattern = '/^[A-Za-z0-9_\.]{8,40}$/';
      if (preg_match($pattern, $password)) {
        return $password;
      }
    }
    if(isset($password) && !empty($password) && !isset($passwordcheck))
    {
       return false;
    }
    if((isset($password) && empty($password)) && ( isset($passwordcheck) && empty($passwordcheck)))
    {
      return true;
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
      $email_pattern = '/(?:[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\'\[\]]+)@(?:(?:(?:[a-z0-9][a-z0-9\-_\[\]]*\.)+(?:aero|arpa|biz|com|cat|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel|mobi|media|[a-z]{2}))|(?:[0-9]{1,3}(?:\.[0-9]{1,3}){3})|(?:[0-9a-fA-F]{1,4}(?:\:[0-9a-fA-F]{1-4}){7}))$/';

      if(preg_match($email_pattern, $email)) {
        return $email;
      }
    }
    return false;
  }

  public static function validate_url($url) {
    if(isset($url) && !empty($url)) {
    	/**
    	 * Regular Expression for URL validation by Diego Perini
    	 * Pls refer to https://gist.github.com/dperini/729294
    	 * for details and upgrades
    	 */

      $url_pattern = '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$_iuS';

      if(preg_match($url_pattern, $url)) {
        return $url;
      }
      return false;
    }
    return false;
  }

  public static function validate_iban($iban) {
    if(isset($iban) && !empty($iban)) {
    	/**
    	 * Regular Expression for IBAN validation by Marketto
    	 * Pls refer to http://blog.marketto.it/2018/02/validazione-iban-di-ogni-paese/
    	 * for details and upgrades
    	 */

      $iban_pattern = '/^(?:(?:IT|SM)\d{2}[A-Z]\d{22}|CY\d{2}[A-Z]\d{23}|NL\d{2}[A-Z]{4}\d{10}|LV\d{2}[A-Z]{4}\d{13}|(?:BG|BH|GB|IE)\d{2}[A-Z]{4}\d{14}|GI\d{2}[A-Z]{4}\d{15}|RO\d{2}[A-Z]{4}\d{16}|KW\d{2}[A-Z]{4}\d{22}|MT\d{2}[A-Z]{4}\d{23}|NO\d{13}|(?:DK|FI|GL|FO)\d{16}|MK\d{17}|(?:AT|EE|KZ|LU|XK)\d{18}|(?:BA|HR|LI|CH|CR)\d{19}|(?:GE|DE|LT|ME|RS)\d{20}|IL\d{21}|(?:AD|CZ|ES|MD|SA)\d{22}|PT\d{23}|(?:BE|IS)\d{24}|(?:FR|MR|MC)\d{25}|(?:AL|DO|LB|PL)\d{26}|(?:AZ|HU)\d{27}|(?:GR|MU)\d{28})$/i';

      if(preg_match($iban_pattern, $iban)) {
        return $iban;
      }
      return false;
    }
    return false;
  }
}
?>