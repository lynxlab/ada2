<?php

/**
 * Client specific configuration file.
 *
 * PHP version >= 5.0
 *
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright   (c) 2009-2010 Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */

if (!MULTIPROVIDER) {
	/**
	 * ID of the public course to get the latest news
	 */
	define('PUBLIC_COURSE_ID_FOR_NEWS', 1);
	/**
	 * How many news to get from the above mentioned course
	 */
	define('NEWS_COUNT', 3);
	/**
	 * Provider default language
	 */
	define('PROVIDER_LANGUAGE', 'it');
	/**
	 * URL
	 * DO NOT REMOVE the trailing // *js_import*
	 */
	define('HTTP_ROOT_DIR', '${PROV_HTTP}'); // *js_import*
	if (getenv('PORTAL_NAME')!==false) {
		$pname = getenv('PORTAL_NAME');
	} else {
		$pname = getenv('ADA_OR_WISP') ? strtoupper(getenv('ADA_OR_WISP')) : 'ADA 2.2';
	}
	define('PORTAL_NAME', $pname . ' - ' . ucwords(strtolower('${UPPERPROVIDER}')));

	/**
	 * set to true to always display the maintenance page
	 * and set the template to be used if you wish
	 */
	if (!defined('MAINTENANCE_MODE')) define('MAINTENANCE_MODE', false);
	if (!defined('MAINTENANCE_TPL') && MAINTENANCE_MODE === true) define('MAINTENANCE_TPL', 'maintenancemode');

	/**
	 * Currency symbol
	 */
	define('ADA_CURRENCY_SYMBOL', '&euro;');

	/**
	 * How many decimal when formatting as currency
	 */
	define('ADA_CURRENCY_DECIMALS', 2);

	/**
	 * Thousands separator when formatting as currency
	 */
	define('ADA_CURRENCY_THOUSANDS_SEP', '.');

	/**
	 * Decimal point when formatting as currency
	 */
	define('ADA_CURRENCY_DECIMAL_POINT', ',');

    /**
     * true if instance subscription must be done by login_required.php
     * submitting to register.php
     *
     * NOTE: If subscription requires a payment, this will have the side-effect
     * of requesting a payment before the user is confirmed (i.e. status set to ADA_STATUS_REGISTERED)
     */
	define ('ADA_SUBSCRIBE_FROM_LOGINREQUIRED', true);

    /**
     * true if instance subscription email must be send
     *
     * NOTE: will send an email only if the subscribing user has a non empty email address
     */
    define ('ADA_SEND_INSTANCE_SUBSCRIPTION_EMAIL', true);

	/**
     * set here the url to redirect the student after login
     */
    define ('FORCE_STUDENT_LOGIN_REDIRECT', '');
}

/**
 *
 * @name ${UPPERPROVIDER}_DB_TYPE
 */
define('${UPPERPROVIDER}_DB_TYPE',  'mysql');

/**
 *
 * @name ${UPPERPROVIDER}_DB_NAME
 */
define('${UPPERPROVIDER}_DB_NAME',  '${ASISPROVIDER}_provider');

/**
 *
 * @name ${UPPERPROVIDER}_DB_USER
 */
define('${UPPERPROVIDER}_DB_USER',  getenv('MYSQL_USER')?:'root');

/**
 *
 * @name ${UPPERPROVIDER}_DB_PASS
 */
define('${UPPERPROVIDER}_DB_PASS',  getenv('MYSQL_PASSWORD')?:'password');

/**
 *
 * @name ${UPPERPROVIDER}_DB_HOST
 */
define('${UPPERPROVIDER}_DB_HOST',  getenv('MYSQL_HOST')?:'localhost');

/**
 *
 * @name ${UPPERPROVIDER}_TIMEZONE
 */
define('${UPPERPROVIDER}_TIMEZONE',  'Europe/Rome');
