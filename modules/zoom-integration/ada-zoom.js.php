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
    AMA_TYPE_STUDENT => array('node', 'layout', 'tutor', 'course', 'course_instance', 'videoroom'),
    AMA_TYPE_TUTOR => array('node', 'layout', 'course', 'course_instance', 'videoroom'),
    // AMA_TYPE_AUTHOR => array('node', 'layout', 'course'),
    // AMA_TYPE_SWITCHER => array('node', 'layout', 'course')
);
$trackPageToNavigationHistory = false;

if (!defined('CONFERENCE_TO_INCLUDE')) {
    define('CONFERENCE_TO_INCLUDE', 'ZoomConf'); // Zoom conference
}
if (!defined('DATE_CONTROL')) {
    define('DATE_CONTROL', FALSE);
}

require_once ROOT_DIR . '/include/module_init.inc.php';
require_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';
require_once ROOT_DIR . '/comunica/include/videoroom.classes.inc.php';

if (!isset($_SESSION['ada-zoom-bridge']) || (isset($_SESSION['ada-zoom-bridge']) && true !== $_SESSION['ada-zoom-bridge'])) {
    die("only running from ada zoom is allowed!");
}

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

unset($_SESSION['ada-zoom-bridge']);
$role = ($userObj->getType() == AMA_TYPE_TUTOR ? '1' : '0');
?>
const debug = false;
if (debug) {
    console.log("checkFeatureRequirements");
    console.log(JSON.stringify(ZoomMtg.checkFeatureRequirements()));
}

ZoomMtg.preLoadWasm();
if ('function' == typeof ZoomMtg.prepareWebSDK) {
    typeof ZoomMtg.prepareWebSDK();
} else {
    ZoomMtg.prepareJssdk();
}

//Add the language code to the internationalization.reload method.
ZoomMtg.i18n.load("<?php echo strtolower($_SESSION['sess_user_language']).'-'.strtoupper($_SESSION['sess_user_language']); ?>");
ZoomMtg.i18n.reload("<?php echo strtolower($_SESSION['sess_user_language']).'-'.strtoupper($_SESSION['sess_user_language']); ?>");
//Add the language code to the ZoomMtg.reRender method.
ZoomMtg.reRender({lang: "<?php echo strtolower($_SESSION['sess_user_language']).'-'.strtoupper($_SESSION['sess_user_language']); ?>"});
ZoomMtg.setZoomJSLib('https://source.zoom.us/<?php echo ZOOM_WEBSDK_VERSION; ?>/lib', '/av');

const API_KEY = "<?php echo ZOOMCONF_APIKEY; ?>";

/**
 * https://marketplace.zoom.us/docs/sdk/native-sdks/web/build/signature
 * https://marketplace.zoom.us/docs/sdk/native-sdks/web/build/meetings/join
 * https://zoom.github.io/sample-app-web/ZoomMtg.html#init
 */

const meetingConfig = {
    apiKey: API_KEY,
    meetingNumber: '<?php echo $videoroomObj->getMeetingID(); ?>',
    leaveUrl: '<?php echo $videoroomObj->getLogoutUrl(); ?>',
    userName: '<?php echo $userObj->getFullName(); ?>',
    userEmail: '<?php echo $userObj->getEmail(); ?>',
    passWord: '<?php echo $videoroomObj->getMeetingPWD(); ?>', // if required
    role: '<?php echo $role; ?>'
};

ZoomMtg.init({
    debug: debug,
    leaveUrl: meetingConfig.leaveUrl,
    isSupportAV: true,
    showMeetingHeader: true, //option
    disableInvite: true, //optional
    meetingInfo: [
        // 'topic',
        // 'participant',
        // 'host',
        // 'mn',
        // 'dc',
    ],
    success: function(initResp) {
        if (debug) {
            console.log("intResponse is ", initResp);
        }
        ZoomMtg.join({
            signature: '<?php echo $videoroomObj->generateSignature($role); ?>',
            sdkKey: meetingConfig.apiKey,
            meetingNumber: meetingConfig.meetingNumber,
            userName: meetingConfig.userName,
            // password optional; set by Host
            passWord: meetingConfig.passWord,
            success: function(joinResp) {
                if (debug) {
                    console.log("joinResp is ", joinResp);
                }
            },
            error: function(joinResp) {
                if (debug) {
                    console.error("joinResp is ", joinResp);
                }
            }
        })
    }
});
