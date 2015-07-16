<?php

/**
 * OpenMeeting Conference specific configuration file.
 * 
 * @author Maurizio Graffio Mazzoneschi
 * @version 0.1
 * @package videochat
 * @license GPL 2.0
 * @copyright (c) 2015 Lynx s.r.l.
 */

/**
 * 
 * @name OPENMEETINGS DATA SERVER 
 */

define('OPENMEETINGS_HOST',  'URL');
define('OPENMEETINGS_PORT',  ':5080');
define('OPENMEETINGS_ADMIN',  'user');
define('OPENMEETINGS_PASSWD',  '');
define('OPENMEETINGS_DIR',  'openmeetings');

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

// ***********
define('CONFERENCE_TYPE','1');
define('AUDIENCE_TYPE','3'); 

define('OM_ROOM_TYPE',AUDIENCE_TYPE);

//*******
define('FRAME_WIDTH','90%');
define('FRAME_HEIGHT','600');

define('VIDEOCHAT_LANGUAGE_BG', '30');
define('VIDEOCHAT_LANGUAGE_EN', '1');
define('VIDEOCHAT_LANGUAGE_ES', '8');
//define('VIDEOCHAT_LANGUAGE_ES', '6'); // versione 0
define('VIDEOCHAT_LANGUAGE_IS', '1');
define('VIDEOCHAT_LANGUAGE_IT', '5');
define('VIDEOCHAT_LANGUAGE_RO', '1');
define('VIDEOCHAT_LANGUAGE_FR', '4');
define('VIDEOCHAT_LANGUAGE_DE', '2');

define('OPENMEETINGS_VERSION','1');
