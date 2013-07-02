<?php

/*
 * 
 * @package		OpenLabor Web Service
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright           Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU Public License v.3
 * @version		0.1
 * 
 * Based on a article of Lorna Jane Mitchell:
 * http://www.lornajane.net/posts/2012/building-a-restful-php-server-understanding-the-request
 *
 * URI (openlabor.lynxlab.com/api/v1)
 * /jobs(trainig or both)/search/keywords/comune/titolo_studio --> search jobs with keywords
 * /jobs(trainig or both)/job/JobID
 * 
 */
ini_set('display_errors',0);

/*
 * Standard OpenLabor inclusion
 */
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');
require_once(ROOT_DIR.'/include/utilities.inc.php');
require_once(ROOT_DIR.'/include/ama.inc.php');
require_once ROOT_DIR.'/include/multiport.inc.php';
require_once ROOT_DIR.'/include/logger_class.inc.php';
require_once ROOT_DIR.'/include/error_class.inc.php';
/*
 * needed in order to have the initialization script phase working
 */
require_once ROOT_DIR.'/include/data_validation.inc.php';

//require_once ROOT_DIR.'/include/user_classes.inc.php';

/*


/*
 * specific API inclusion
 */
require_once('./include/config.inc.php');
require_once('./controllers/OpenLaborController.inc.php');
require_once(__DIR__ . '/include/extract_class.inc.php');
require_once('./include/restRequest.inc.php');
require_once('include/AMAOpenLaborDataHandler.inc.php');



//print_r(__DIR__);

spl_autoload_register('apiAutoload');
function apiAutoload($classname)
{
//        print_r($classname);
    if (preg_match('/[a-zA-Z]+Controller$/', $classname)) {
        //.'/controllers/' . $classname . '.inc.php');
        include __DIR__ .'/controllers/' . $classname . '.inc.php';
        return true;
    } elseif (preg_match('/[a-zA-Z]+Model$/', $classname)) {
        include __DIR__ . '/models/' . $classname . '.php';
        return true;
    } elseif (preg_match('/[a-zA-Z]+View$/', $classname)) {
        include __DIR__ . '/views/' . $classname . '.php';
        return true;
    }
}

$requestObj = new Request;
$url_elements = $requestObj->url_elements;
$verb = $requestObj->verb; //get, put, post, delete
if (isset($requestObj->parameters['service_code'])) {
    $service_code = $requestObj->parameters['service_code']; // 001, 002, 003, 
}elseif (isset($url_elements[2])) {
    $service_code = $url_elements[2]; // 001, 002, 003, 
}

/*
 *  route the request to the right place
 */

$controller_name = ucfirst($url_elements[1]).'Controller';
//$action = ucfirst($url_elements[2]);

//$controller_name = ucfirst($op) . 'Controller';

if (class_exists($controller_name)) {
    $controller = new $controller_name();
    if (ucfirst($url_elements[1]) == 'Requests') {
        $action_name = strtolower($verb) . $service_code.'Action';
    }
    else {
        $action_name = strtolower($verb) .'Action';
    }
    $controller->$action_name($requestObj->parameters,$requestObj->format,$url_elements);
}

class Request {
    public $url_elements;
    public $verb;
    public $parameters;
 
    public function __construct() {
        $this->verb = $_SERVER['REQUEST_METHOD'];
        $this->url_elements = explode('/', $_SERVER['PATH_INFO']);
        $this->parseIncomingParams();

        // initialise json as default format
        /*
        if(isset($this->parameters['format'])) {
            $this->format = $this->parameters['format'];
        }
         * 
         */
        return true;
    }
 
    public function parseIncomingParams() {
        $parameters = array();
//        print_r($_SERVER);
        // first of all, pull the GET varsa
//        print_r($this->verb);
//        print_r($this->url_elements[2]);
        $num_el = count($this->url_elements);
        if ($num_el>2) {
            $controller_name_tmp = explode('.',$this->url_elements[2]);
            $lenght = count($controller_name_tmp);
            $format = $controller_name_tmp[($lenght-1)];
            unset($controller_name_tmp[($lenght-1)]);
            $this->url_elements[2] = implode('.',$controller_name_tmp);
        } else {
            $controller_name_tmp = explode('.',$this->url_elements[1]);
            $lenght = count($controller_name_tmp);
            $format = $controller_name_tmp[($lenght-1)];
            unset($controller_name_tmp[($lenght-1)]);
            $this->url_elements[1] = implode('.',$controller_name_tmp);
            
        }
        $this->format = $format;
       switch ($this->verb) {
           case 'GET':
//               print_r($_SERVER['QUERY_STRING']);
                if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') {
                    parse_str($_SERVER['QUERY_STRING'], $parameters);
                }
                /*
                else {
                    $action = $this->url_elements[2];
                    if ($this->url_elements[2] == 'search') {
                        $parameters['keywords'] = $this->url_elements[3];
                        $parameters['city'] = $this->url_elements[4];
                        $parameters['qualification'] = $this->url_elements[5];
                    } elseif (is_numeric($this->url_elements[2])) {
                        $parameters['jobID'] = $this->url_elements[2];
                    }
                }
                 * 
                 */
                break;
           case 'POST':
           case 'PUT':
                // now how about PUT/POST bodies? These override what we got from GET
                $body = file_get_contents("php://input");
                $content_type = false;
                if(isset($_SERVER['CONTENT_TYPE'])) {
                    $content_type = $_SERVER['CONTENT_TYPE'];
                }
        //        print_r(array('type',$content_type));
                switch($content_type) {
                    case "application/json":
                        $body_params = json_decode($body);
                        if($body_params) {
                            foreach($body_params as $param_name => $param_value) {
                                $parameters[$param_name] = $param_value;
                            }
                        }
                        $this->format = "json";
                        break;
                    case "application/x-www-form-urlencoded":
                        print_r($body);
                        parse_str($body, $postvars);
                        foreach($postvars as $field => $value) {
                            $parameters[$field] = $value;

                        }
                        $this->format = "html";
                        break;
                    default:
                        // we could parse other supported formats here
                        break;
                }
       }
        $this->parameters = $parameters;
//        print_r(array('parametri '.$this->parameters));
//        print_r('parametri');
//        print_r($this->parameters);
    }
}    