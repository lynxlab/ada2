<?php
/**
 * Openmeetings specific configuration file.
 * 
 * @author
 * @version
 * @package
 * @license
 * @copyright (c) 2009 Lynx s.r.l.
 */

/**
 * 
 * @name OPENMEETINGS DATA SERVER 

define('OPENMEETINGS_HOST',  'localhost');
define('OPENMEETINGS_PORT',  ':5080');
define('OPENMEETINGS_ADMIN',  'admin');
define('OPENMEETINGS_PASSWD',  'admin');
define('OPENMEETINGS_DIR',  'openmeetings');
 */
/*
 * 
define('OPENMEETINGS_HOST',  'lynx.comunicyou.it');
define('OPENMEETINGS_PORT',  '');
define('OPENMEETINGS_ADMIN',  'lynx');
define('OPENMEETINGS_PASSWD',  'lynx.egos');
define('OPENMEETINGS_DIR',  'lynx');
 */
define('OPENMEETINGS_HOST',  '77.72.193.243');
define('OPENMEETINGS_PORT',  '');
define('OPENMEETINGS_ADMIN',  'lynx');
define('OPENMEETINGS_PASSWD',  'ViaOstiense60');
define('OPENMEETINGS_DIR',  'demo');


/**
 * 
 * @name OPENMEETINGS DEFAULT DATA ROOM 
 */

define('ROOM_DEFAULT_LANGUAGE',  '4');
define('ROOM_IS_PUBLIC',  'true');
define('VIDEO_POD_WIDTH',  '355');
define('VIDEO_POD_HEIGHT',  '560');
define('VIDEO_POD_X_POSITION',  '2');
define('VIDEO_POD_y_POSITION',  '2');
define('MODERATION_PANEL_X_POSITION',  '400');
define('SHOW_WHITE_BOARD',  'true');
define('WHITE_BOARD_PANEL_X_POSITION',  '360');
define('WHITE_BOARD_PANEL_Y_POSITION',  '2');
define('WHITE_BOARD_PANEL_HEIGHT',  '560');
define('WHITE_BOARD_PANEL_WIDTH',  '600');
define('SHOW_FILES_PANEL',  'false');
define('FILES_PANEL_X_POSITION',  '2');
define('FILES_PANEL_Y_POSITION',  '284');
define('FILES_PANEL_HEIGHT',  '310');
define('FILES_PANEL_WIDTH',  '270');

//*******
define('FRAME_WIDTH','1000');
define('FRAME_HEIGHT','600');

define('VIDEOCHAT_LANGUAGE_BG', '1');
define('VIDEOCHAT_LANGUAGE_EN', '1');
define('VIDEOCHAT_LANGUAGE_ES', '7');
//define('VIDEOCHAT_LANGUAGE_ES', '6'); // versione 0
define('VIDEOCHAT_LANGUAGE_IS', '1');
define('VIDEOCHAT_LANGUAGE_IT', '4');
define('VIDEOCHAT_LANGUAGE_RO', '1');
define('VIDEOCHAT_LANGUAGE_FR', '3');
define('VIDEOCHAT_LANGUAGE_DE', '2');

define('DATE_CONTROL',FALSE);

define('OPENMEETINGS_VERSION','1');

?>
