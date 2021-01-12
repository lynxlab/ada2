<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");          // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
header("Content-type: application/x-javascript");
//header("Content-Disposition: attachment; filename=javascript_conf.js");

require_once '../../config_path.inc.php';
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR); //, AMA_TYPE_AUTHOR, AMA_TYPE_ADMIN, AMA_TYPE_SWITCHER);
/**
 * Get needed objects
 */
$neededObjAr = array(
    // AMA_TYPE_VISITOR => array('node', 'layout', 'course'),
    AMA_TYPE_STUDENT => array('layout', 'tutor', 'course', 'course_instance', 'videoroom'),
    AMA_TYPE_TUTOR => array('layout', 'course', 'course_instance', 'videoroom'),
    // AMA_TYPE_AUTHOR => array('node', 'layout', 'course'),
	// AMA_TYPE_SWITCHER => array('node', 'layout', 'course')
);
$trackPageToNavigationHistory = false;

if (!defined('CONFERENCE_TO_INCLUDE')) {
    define('CONFERENCE_TO_INCLUDE', 'Jitsi'); // Jitsi conference
}
if (!defined('DATE_CONTROL')) {
    define('DATE_CONTROL', FALSE);
}

require_once ROOT_DIR . '/include/module_init.inc.php';
require_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';
require_once ROOT_DIR . '/comunica/include/videoroom.classes.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
BrowsingHelper::init($neededObjAr);

$roomHash = $videoroomObj->getMeetingID();
$jwt = null;
$isView = isset($_REQUEST['isView']) && intval($_REQUEST['isView']) == 1;

if ($userObj->getType() == AMA_TYPE_STUDENT) {
	$result = $GLOBALS['dh']->course_instance_tutor_info_get($courseInstanceObj->getId());
	if (is_array($result)) $result = reset($result);
	if (is_array($result) && isset($result['e_mail']) && strlen($result['e_mail'])>0) {
		$userInfo['email'] = $result['e_mail'];
	}

	// jitsi toolbarbuttons for student
	$TOOLBAR_BUTTONS = [
	'microphone', 'camera', 'closedcaptions', /*'desktop', */ 'fullscreen',
	'fodeviceselection', 'hangup', 'chat',
	'etherpad', 'settings', 'raisehand',
	'videoquality', 'filmstrip','feedback', 'stats', 'shortcuts',
	'tileview', 'videobackgroundblur', 'download', 'help'
	];
	// jitsi settings
	$SETTINGS_SECTIONS = [ 'devices', 'language' ];

} else if ($userObj->getType() == AMA_TYPE_TUTOR) {
	if (strlen($userObj->getEmail())) {
		$userInfo['email'] = $userObj->getEmail();
	}
	// generate the jwt
	$header = json_encode([
		// "kid" => "jitsi/custom_key_name",
		"typ" => "JWT",
		"alg" => "HS256"
	  ], JSON_UNESCAPED_SLASHES);
	  $base64urlheader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

	  $payload  = json_encode([
		"context" => [
		"user" => [
			"avatar" => $userObj->getAvatar(),
			"name" => $userObj->getFullName(),
			"email" => $userObj->getEmail(),
			"id" => $userObj->getId(),
		  ],
		  "group" => "",
		  "features" => [
			  "screen-sharing" => true,
			  "livestreaming" => false,
			  "recording" => false,
			],
		],
		"aud" => JITSI_APP_ID,
		"iss" => JITSI_JWT_ISS,
		"sub" => JITSI_DOMAIN,
		"room" => $roomHash,
		"exp" => time() + 30, // token last 30 seconds

	  ], JSON_UNESCAPED_SLASHES);
	  $base64urlpayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

	  $signature = hash_hmac('sha256', $base64urlheader . "." . $base64urlpayload, JITSI_APP_SECRET, true);
	  $base64urlsignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

	  $jwt = $base64urlheader . "." . $base64urlpayload . "." . $base64urlsignature;

	  // jitsi toolbarbuttons for tutor
	  $TOOLBAR_BUTTONS = [
        'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
        'fodeviceselection', 'hangup', 'profile', 'info', 'chat', /* 'recording', */
        'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
        'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
        'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone',
        'e2ee'
	  ];
	  // jitsi settings
	  $SETTINGS_SECTIONS = [ 'devices', 'language', 'moderator', 'profile', 'calendar' ];
}
?>
var dbgJitsiapi = null;
var domain = '<?php echo JITSI_CONNECT_HOST; ?>';  //'beta.meet.jit.si'; // 'meet.jit.si';
var ifwidth = '100%';
var ifheight = '700px';
var parentElement = document.querySelector('#<?php echo trim($_REQUEST['parentId']) ?>');
if (parentElement != null) {
	if (parentElement.dataset.domain != null) {
		domain = parentElement.dataset.domain;
	}
	if (parentElement.dataset.width != null) {
		ifwidth = parentElement.dataset.width;
	}
	if (parentElement.dataset.height != null) {
		ifheight = parentElement.dataset.height;
	}
	parentElement = null;
	delete parentElement;
}

$j.getScript(
	'<?php echo JITSI_PROTOCOL; ?>://'+domain+'/external_api.js',
	function() {
		var debug = true;
		var jitsiAPI = null;
		var options = {
			roomName: '<?php echo $roomHash; ?>',
<?php
	if (strlen($jwt)>0) {
?>
			jwt: '<?php echo $jwt; ?>',
<?php
	}
?>
			width: ifwidth,
			height: ifheight,
				// hosts: {
				//	domain: domain,

					// muc: 'conference.'+domain, // FIXME: use XEP-0030
					// focus: 'focus.'+domain,
				// },
				// bosh:'https://'+domain+'/http-bind', // FIXME: use xep-0156 for that
				// The name of client node advertised in XEP-0115 'c' stanza
				// clientNode: 'http://jitsi.org/jitsimeet',
			configOverwrite: {
				startWithVideoMuted : <?php echo ($userObj->getType() == AMA_TYPE_TUTOR ? 'false' : 'true');  ?>,
				startWithAudioMuted : <?php echo ($userObj->getType() == AMA_TYPE_TUTOR ? 'false' : 'true');  ?>,
				defaultLanguage: '<?php echo strtolower($_SESSION['sess_user_language']) ?>',
			},
			interfaceConfigOverwrite: {
				TOOLBAR_BUTTONS: [ <?php echo '\''.implode('\', \'',$TOOLBAR_BUTTONS). '\''; ?> ],
				SETTINGS_SECTIONS: [ <?php echo '\''.implode('\', \'',$SETTINGS_SECTIONS). '\''; ?> ],
			}
		};
<?php
			if (isset($_REQUEST['parentId']) && strlen($_REQUEST['parentId'])>0) {
		?>
		if (null !== document.querySelector('#<?php echo trim($_REQUEST['parentId']) ?>')) {
			document.querySelector('#<?php echo trim($_REQUEST['parentId']) ?>').className += 'ada-videochat-embed jitsi-meet';
			document.querySelector('#<?php echo trim($_REQUEST['parentId']) ?>').setAttribute('data-logout','<?php echo urlencode($videoroomObj->getLogoutUrlParams()); ?>');
			options.parentNode = document.querySelector('#<?php echo trim($_REQUEST['parentId']) ?>');
		}
<?php
			}
			if (isset($userInfo) && is_array($userInfo)) {
		?>
		options.userInfo = <?php echo json_encode($userInfo, JSON_FORCE_OBJECT); ?>;
<?php
			}
		?>

		if (debug) {
			console.groupCollapsed('ADA-JITSI');
			console.log('JITSI External API loaded');
			console.log({ domain: domain, options: options });
		}

		// var
		jitsiAPI = new JitsiMeetExternalAPI(domain, options);
		jitsiAPI.executeCommand('subject', '<?php echo $courseInstanceObj->getTitle(); ?>');
		jitsiAPI.executeCommand('displayName', '<?php echo $userObj->getFullName(); ?>');
		jitsiAPI.executeCommand('email', '<?php echo $userObj->getemail(); ?>');
		jitsiAPI.on('readyToClose',() => {
			jitsiAPI.dispose();
			$j('#<?php echo trim($_REQUEST['parentId']) ?>').load('<?php echo $videoroomObj->getLogoutUrl(); ?>', function (response, status, xhr) {
			});
		});
<?php
    if ($userObj->getType() == AMA_TYPE_TUTOR) {
		?>
        // jitsiAPI.executeCommand('password', 'lynx#2020');
        // console.log('password set!');
<?php
    }
?>
		if (debug) {
			console.groupEnd();
			dbgJitsiapi = jitsiAPI;
		}
	}
);
