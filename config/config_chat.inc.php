<?php
/**
 * Chat configuration file
 *
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/*
 * Mood messages
 */
define('ADA_CHAT_MOOD_MESSAGE_CODES_START' , 1);
define('ADA_CHAT_MAX_MOOD_MESSAGES'        , 100);

define('ADA_CHAT_MOOD_TYPE_APPLAUSE'         , 1);
define('ADA_CHAT_MOOD_TYPE_DISAGREE'         , 2);
define('ADA_CHAT_MOOD_TYPE_ASK_FOR_ATTENTION', 3);

/*
 * Operator actions
 */
define('ADA_CHAT_OPERATOR_ACTION_CODES_START' , 101);
define('ADA_CHAT_MAX_OPERATOR_ACTIONS'        , 100);
define('ADA_CHAT_OPERATOR_ACTION_SET_OPERATOR'  , 101);
define('ADA_CHAT_OPERATOR_ACTION_UNSET_OPERATOR', 102);
define('ADA_CHAT_OPERATOR_ACTION_MUTE_USER'     , 103);
define('ADA_CHAT_OPERATOR_ACTION_UNMUTE_USER'   , 104);
define('ADA_CHAT_OPERATOR_ACTION_BAN_USER'      , 105);
define('ADA_CHAT_OPERATOR_ACTION_UNBAN_USER'    , 106);
define('ADA_CHAT_OPERATOR_ACTION_KICK_USER'     , 107);

/*
 * User status
 */
define('STATUS_OPERATOR', 'O'); //chatroom operator
define('STATUS_ACTIVE',   'A'); // user is active
define('STATUS_BAN',      'B'); // user is banned
define('STATUS_MUTE',     'M'); // user is mute
define('STATUS_EXIT',     'E'); // user left/kicked the room
define('STATUS_INVITED',  'I'); // user invited into the chatroom

/*
 * Possible actions
 */
define('ACTION_ENTER',          'EN'); //joins the room
define('ACTION_EXIT',           'EX'); //leaves the room
define('ACTION_SET_OPERATOR',   'OP'); //becomes operator
define('ACTION_UNSET_OPERATOR', 'UO'); //no more operator, becomes normal user
define('ACTION_MUTE',           'MU'); //users has no 'voice', only read
define('ACTION_UNMUTE',         'UM'); //gives voice back to user, read & write
define('ACTION_BAN',         	'BN'); //user is banned
define('ACTION_UNBAN',       	'UB'); //tolges ban
define('ACTION_KICK',          	'KC'); //user is been kicked
define('ACTION_INVITE',         'IN'); //user is been kicked

/*
 * Chat expiration time 
 */
define('SHUTDOWN_CHAT_TIME', 86400);

/*
 * Maximum number of users in a chatroom
 */
define('DEFAULT_MAX_USERS', 70);

/*
 * 
 */
define('ID_PUBLIC_CHATROOM', 1); //the ID of the public chatroom

/*
 * Chatroom types
 */
define('PUBLIC_CHAT',     'P'); //public chatroom
define('CLASS_CHAT',      'C'); //course instance chatroom
define('INVITATION_CHAT', 'I'); //chatroom with invitation

/*
 * Chatroom ui refresh internal.
 */
//define('REFRESH_HIGH_LEVEL', 5); // often reloading
//define('REFRESH_MEDIUM_LEVEL', 23); // less often reloading

/*
 *  reason of the exit
 */
define('NO_EXIT_REASON',         -1);
define('EXIT_REASON_QUIT',        0); //user quits chatroom    
define('EXIT_REASON_KICKED',      1); //user kicked from chatroom
define('EXIT_REASON_BANNED',      2); //user banned from chatroom
define('EXIT_REASON_NOT_STARTED', 3); //chatroom not started yet
define('EXIT_REASON_EXPIRED',     4); //chatroom expired
define('EXIT_REASON_NOT_EXIST',   5); //chatroom not exist
define('EXIT_REASON_WRONG_ROOM',  6); //chatroom not for this user
define('EXIT_REASON_FULL_ROOM',   7); //chatroom is full

/*
 * time that users remain inactive before get remoned 
*/
define('MAX_INACTIVE_TIME', 1200); //user banned from chatroom in seconds
/*
 *  constants used to extend the chat session
 */
define('TIME_BEFORE_EXPIRATION', 300); //time that remains before chat session expires
define('TIME_TO_EXTEND', 3600); //the extension time that will be assigned into the chatroom
define('USERS_REQUESTED_TO_EXTEND', 1); // users requested in order to extend
// define('WINDOW_SCROLL', 6500); //window scroll