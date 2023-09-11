<?php

/**
 * Zoom Conference specific configuration file.
 *
 * @author Giorgio
 * @version 0.1
 * @package videochat
 * @license GPL 2.0
 * @copyright (c) 2020 Lynx s.r.l.
 */

/**
 *
 * @name CONNECT DATA SERVER
 */
if (!defined('ZOOM_WEBSDK_VERSION')) {
    define('ZOOM_WEBSDK_VERSION', '2.16.0');
}

define('ZOOMCONF_ZOOMUSER', 'me');

// Meeting SDK for web meeting access
define('ZOOMCONF_APIKEY', '<YOUR MEETING SDK APIKEY HERE>');
define('ZOOMCONF_APISECRET', '<YOUR MEETING SDK API SECRET>');

// Server 2 Server for meeting creation and management
define('ZOOMCONF_S2S_ACCOUNTID', '<YOUR SERVER TO SERVER ACCOUNTID>');
define('ZOOMCONF_S2S_CLIENTID', '<YOUR SERVER TO SERVER CLIENTID>');
define('ZOOMCONF_S2S_CLIENTSECRET', '<YOUR SERVER TO SERVER CLIENTSECRET>');

define('FRAME_WIDTH', '90%');
define('FRAME_HEIGHT', '700px');
