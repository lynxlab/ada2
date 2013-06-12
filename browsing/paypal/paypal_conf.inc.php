<?php

/* 
 * Configuration file of PayPal payment
 *
 * @author		Maurizio Graffio Mazzoneschi <graffio@lynxlab.com>
 * @copyright           Copyright (c) 2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

  // CONSTANTS:
  // General value for PayPal account

  define('PAYPAL_ACCOUNT',  'mazzon_1294835790_biz@lynxlab.com'); // business
  define('PAYPAL_ACTION',  'https://www.sandbox.paypal.com/cgi-bin/webscr'); // form action

  define('PAYPAL_RETURN_PAGE',  'http://concorsi.altrascuola.it/grazie.htm'); // default return page. Overwrite by form
  define('COURSE_AMOUNT',  '10.00'); // dovrà essere nel DB dell'istanza corso
  define('CURRENCY_CODE', 'EUR'); // currency_code
  define('SUBMIT_VALUE',  'Completa l\'acquisto'); // Value that appears in submit button
  define('PAYPAL_CMD',  '_cart'); // cmd
  define('NO_SHIPPING',1); // no_shipping
  define('RM',2); // send to the user browser via POST
//  define('NOTIFY_URL',); // notify_url

/*
 * IPN process
 */
  define("PAYPAL_IPN_LOG", "paypal-ipn.log");
  define("PAYPAL_IPN_URL", "www.sandbox.paypal.com");

  /*
   * PDT process
   */
  define('IDENTITY_CHECK','VitVtcT1xT7CMs98sSex7Ltg68y1xRGHe_fSMKsbMMGFLxnNNeBiIyNJ9U8'); // sandbox mazzon_biz

/*
 * Internal constant to manage payments 
 */

  define('PAYPAL_ADMIN_MAIL',  'amministrazione@italicon.it');
  define('PAYPAL_NAME_ACCOUNT',  '*Consorzio ICoN');

?>
