<?php
/**
 * test.php
 *
 * @package        API
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           API
 * @version		   0.1
 */
namespace  AdaApi;
require_once 'bootstrap.php';

$app = new \Slim\Slim ();

if (isset($_REQUEST['format'])) {
	if (in_array(trim($_REQUEST['format']),AdaApi::$supportedFormats)) {
		$format = trim($_REQUEST['format']);
	} else {
		$format = false;
	}
} else {
	$format = 'json';
}

/**
 * View class and filename must be named format with uppercase
 * first character followed by 'View' (with capital V).
 */
if ($format!==false) {
	$viewClassName = __NAMESPACE__.'\\'.ucfirst(strtolower($format)).'View';
	$app->view(new $viewClassName);
}

/**
 * set the not found string
 */
$app->notFound (function () use($app) {
	$app->halt(400, "URL is malformed");
});

/**
 * add a middleware class to check
 * if output format is supported
 */
$app->add(new FormatSupported($format!==false));

/**
 * add a middleware class to check if a valid
 * access token has been provided either in 
 * the Authorize HTTP Header or in the METHOD body
 * 
 * This object will hold the authorized user ID as well
 */
$oAuth2Obj = new OAuth2Auth();
$app->add($oAuth2Obj);

/**
 * add a middleware class to remove unwanted query string parameters
 * that can be passed either as a string or as an array
 */
$app->add(new CleanQueryString(array('format','access_token')));

/**
 * users endpoint route, responds to GET, POST  
 */
$app->map ('/users(.:format)',  function ($format=null) use($app) {

	$method = strtolower ($app->request->getMethod ());
	$usercontroller = new UserController ($app);

	if (method_exists ($usercontroller, $method)) {
		$userData = $usercontroller->$method ($app->request->params());
		if (\AMA_DB::isError($userData) || empty($userData)) {
			$app->halt(500, 'Server Error');
		} else {
			$app->render('users',array('output'=>$userData,'app'=>$app));
		}
	} else
		$app->notFound ();
})->via('GET', 'POST');

/**
 * testers endpoint route, responds to GET
 */
$app->map ('/testers(.:format)',  function ($format=null) use($app, $oAuth2Obj) {

	$method = strtolower ($app->request->getMethod ());
	$testercontroller = new TesterController($app,$oAuth2Obj->getAuthUserID());

	if (method_exists ($testercontroller, $method)) {
		$testerData = $testercontroller->$method ($app->request->params());
		if (\AMA_DB::isError($testerData) || empty($testerData)) {
			$app->halt(500, 'Server Error');
		} else {
			$app->render('testers',array('output'=>$testerData,'app'=>$app));
		}
	} else
		$app->notFound ();
})->via('GET');

$app->run ();
?>