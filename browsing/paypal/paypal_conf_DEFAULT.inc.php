<?php

/*
 * Configuration file of PayPal payment
 *
 * @author Maurizio Graffio Mazzoneschi <graffio@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version 0.1
 */

// CONSTANTS:
// General value for PayPal account
if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider']) && is_readable(ROOT_DIR . '/clients/' . $GLOBALS['user_provider'] . '/paypal_conf.inc.php')) {
	require_once ROOT_DIR . '/clients/' . $GLOBALS['user_provider'] . '/paypal_conf.inc.php';
} else {
    define ( 'PAYPAL_SANDBOX', true);
    define ( 'PAYPAL_ACCOUNT', 'xxxxxxxxxxxxxxxxxxxxx@lynxlab.com' ); // business
    define ( 'PAYPAL_ACTION', 'https://'.(PAYPAL_SANDBOX ? 'www.sandbox.paypal.com' : 'www.paypal.com').'/cgi-bin/webscr' ); // form action

    // production account
    //define('PAYPAL_ACTION',  'https://www.paypal.com/cgi-bin/webscr'); // form action

    define ( 'PAYPAL_RETURN_PAGE', 'http://localhost/ada22/grazie.htm' ); // default return page. Overwrite by form
    define ( 'COURSE_AMOUNT', '10.00' ); // dovrà essere nel DB dell'istanza corso
    define ( 'CURRENCY_CODE', 'EUR' ); // currency_code
    define ( 'SUBMIT_VALUE', 'Completa l\'acquisto' ); // Value that appears in submit button
    define ( 'PAYPAL_CMD', '_cart' ); // cmd
    define ( 'NO_SHIPPING', 1 ); // no_shipping
    define ( 'RM', 2 ); // send to the user browser via POST
                    // define('NOTIFY_URL',); // notify_url

    /*
     * IPN process
     */
    define ( "PAYPAL_IPN_LOG", "paypal-ipn.log" );
    define ( "PAYPAL_IPN_URL", PAYPAL_SANDBOX ? "www.sandbox.paypal.com" : "www.paypal.com");

    /*
     * PDT process
     */
    define ( "PAYPAL_PDT_LOG", "paypal-pdt.log" );
    define ( 'IDENTITY_CHECK', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx' ); // sandbox

    /*
     * Internal constant to manage payments
     */

    define ( 'PAYPAL_ADMIN_MAIL', 'admin@yourdomain.xxx' );
    define ( 'PAYPAL_NAME_ACCOUNT', '*account-NAME' );
}
