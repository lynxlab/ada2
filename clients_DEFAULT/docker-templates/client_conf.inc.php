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
	define('HTTP_ROOT_DIR', '${HTTP_ROOT_DIR}'); // *js_import*
	if (isset($_ENV['PORTAL_NAME'])) {
		$pname = $_ENV['PORTAL_NAME'];
	} else {
		$pname = isset($_ENV['ADA_OR_WISP']) ? translateFN('Benvenuto su') . ' ' . $_ENV['ADA_OR_WISP'] : 'ADA 2.2';
	}
	define('PORTAL_NAME', $pname . ' - ' . ucwords(strtolower('${PROVIDER}')));

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
}

/**
 *
 * @name ${PROVIDER}_DB_TYPE
 */
define('${PROVIDER}_DB_TYPE',  'mysql');

/**
 *
 * @name ${PROVIDER}_DB_NAME
 */
define('${PROVIDER}_DB_NAME',  '${PROVIDER}_provider');

/**
 *
 * @name ${PROVIDER}_DB_USER
 */
define('${PROVIDER}_DB_USER',  $_ENV['MYSQL_USER']);

/**
 *
 * @name ${PROVIDER}_DB_PASS
 */
define('${PROVIDER}_DB_PASS',  $_ENV['MYSQL_PASSWORD']);

/**
 *
 * @name ${PROVIDER}_DB_HOST
 */
define('${PROVIDER}_DB_HOST',  $_ENV['MYSQL_HOST']);

/**
 *
 * @name ${PROVIDER}_TIMEZONE
 */
define('${PROVIDER}_TIMEZONE',  'Europe/Rome');
