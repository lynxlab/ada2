<?php
/**
 * APPS MODULE.
 *
 * @package        apps module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           oauth2
 * @version		   0.1
 */

/**
 * This is from
 * https://thomashunter.name/blog/generate-oauth-consumer-key-and-shared-secrets-using-php/
 * 
 * Looks like it's oauth1 but probably it's ok for 2
 * 
 * @return multitype:string
 */

function generateConsumerIdAndSecret () {
	// Get a whole bunch of random characters from the OS
	$fp = fopen('/dev/urandom','rb');
	$entropy = fread($fp, 32);
	fclose($fp);
	
	// Takes our binary entropy, and concatenates a string which represents the current time to the microsecond
	$entropy .= uniqid(mt_rand(), true);
	
	// Hash the binary entropy
	$hash = hash('sha512', $entropy);

	return array(
			'client_id' => substr($hash, 0, 15),
			'client_secret' => substr($hash, 15, 48)
	);			
}
?>
