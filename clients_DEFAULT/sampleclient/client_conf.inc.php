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

if (!MULTIPROVIDER)
{
	/**
	 * ID of the public course to get the latest news
	 */
	define ('PUBLIC_COURSE_ID_FOR_NEWS', 1);
	/**
	 * How many news to get from the above mentioned course
	*/
	define ('NEWS_COUNT', 3);
	/**
	 * Provider default language
	 */
	define ('PROVIDER_LANGUAGE','it');
	/**
	 * URL
	 * DO NOT REMOVE the trailing // *js_import*
	 */
	define('HTTP_ROOT_DIR','http://sampleprovider.localhost/ada22'); // *js_import*
	define('PORTAL_NAME','ADA 2.2 SAMPLE PROVIDER');

	/**
	 * set to true to always display the maintenance page
	 * and set the template to be used if you wish
	 */
	if (!defined('MAINTENANCE_MODE')) define('MAINTENANCE_MODE', false);
	if (!defined('MAINTENANCE_TPL') && MAINTENANCE_MODE === true) define ('MAINTENANCE_TPL', 'maintenancemode');

	/**
	 * Currency symbol
	 */
	define ('ADA_CURRENCY_SYMBOL' , '&euro;');

	/**
	 * How many decimal when formatting as currency
	 */
	define ('ADA_CURRENCY_DECIMALS', 2);

	/**
	 * Thousands separator when formatting as currency
	 */
	define ('ADA_CURRENCY_THOUSANDS_SEP', '.');

	/**
	 * Decimal point when formatting as currency
	 */
	define ('ADA_CURRENCY_DECIMAL_POINT', ',');

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
}

/**
 *
 * @name SAMPLE_DB_TYPE
 */
define('SAMPLE_DB_TYPE',  'mysql');

/**
 *
 * @name SAMPLE_DB_NAME
 */
define('SAMPLE_DB_NAME',  'ada_provider_SAMPLE');

/**
 *
 * @name SAMPLE_DB_USER
 */
define('SAMPLE_DB_USER',  'ada_db_SAMPLE');

/**
 *
 * @name SAMPLE_DB_PASS
 */
define('SAMPLE_DB_PASS',  'SAMPLE');

/**
 *
 * @name SAMPLE_DB_HOST
 */
define('SAMPLE_DB_HOST',  'localhost');

/**
 *
 * @name SAMPLE_TIMEZONE
 */
define('SAMPLE_TIMEZONE',  'Europe/Rome');
