<?php
/**
 * NEWSLETTER MODULE.
 *
 * @package		newsletter module
 * @author			giorgio <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			newsletter
 * @version		0.1
 */

	define ('DEFAULT_FILTER_SENTENCE', 'Imposta i filtri per sapere a chi verr&agrave; inviata la newsletter');
	
	define ('MODULES_NEWSLETTER_HISTORY_STATUS_UNDEFINED',0);
	define ('MODULES_NEWSLETTER_HISTORY_STATUS_SENDING',1);
	define ('MODULES_NEWSLETTER_HISTORY_STATUS_SENT',2);
	
	define ('MODULES_NEWSLETTER_LOGDIR' , ROOT_DIR.'/log/newsletter/');
	
	define ('MODULES_NEWSLETTER_EMAILS_PER_HOUR' , 40); // numer of emails per hour to be sent out
?>