<?php

/**
 * jitsi Conference specific configuration file.
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
define('JITSI_PROTOCOL','https');
define('JITSI_CONNECT_HOST',  '');
// app id, app secret and iss are needed for the JWT authentication
define('JITSI_APP_ID', '');
define('JITSI_APP_SECRET', '');
define('JITSI_JWT_ISS', 'lynxlab');
define('JITSI_DOMAIN', 'meet.jitsi');
define('JITSI_HTML_PLACEHOLDER_ID', 'jitsi-meet-placeholder');

define('DEFAULT_ROOM_NAME','ada-jitsi');
define('MEETING_ROOM_DURATION',3650);

define('FRAME_WIDTH','90%');
define('FRAME_HEIGHT','700px');
