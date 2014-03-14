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

/**
 * Define here all the endpoints (in the array keys) with the
 * associated controller classes and methods to respond to
 */
$endpoints = array (
	'users'   =>       array ('controllerclass'=>'UserController',         'methods'=>array('GET','POST')),
	'testers' =>       array ('controllerclass'=>'TesterController',       'methods'=>array('GET')),
	'subscriptions' => array ('controllerclass'=>'SubscriptionController', 'methods'=>array('POST')),
);

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
	/**
	 * Set the app Viewer to the requested output format
	 */
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
 * add a middleware to handle and
 * convert input content types 
 */
$app->add(new \Slim\Middleware\ContentTypes());

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
$app->add(new CleanQueryString(array('format')));

/**
 * Cycle the endpoints array and map the declared methods
 * to the corresponding controllerclass.
 * 
 * NOTE: the use of the $template parameter in the render call
 * that is used to set the root node element in XML and
 * ignored in JSON and PHP-serialized output formats.
 */
foreach ($endpoints as $endpoint=>$config) {
	/**
	 * Cycle the endpoint config to map proper methods to controllerclass
	 */
	foreach ($config['methods'] as $method) {
		$method = strtolower($method);
		/**
		 * Use SLIM object to map method to controller
		 */
		$app->$method ('/'.$endpoint.'(.:format)',
				function ($format=null)
				use ($app, $oAuth2Obj, $endpoint, $config) {			
					try {
						$method = strtolower ($app->request->getMethod ());
						$controllerClass = __NAMESPACE__.'\\'.$config['controllerclass'];
						$controller = new $controllerClass ($app,$oAuth2Obj->getAuthUserID());
						if (method_exists ($controller, $method)) {
							$data = $controller->$method ($app->request->params());
							$app->render($endpoint ,array('output'=>$data,'app'=>$app));
						} else {
							$app->notFound ();
						}
					} catch (APIException $e) {
						$controller->handleException ($e);
					}
				});		
	}
}
	
$app->run ();
?>