<?php

    /**
     * @abstract: config file
     * @package openlabor
     * @copyright (c) 2013, Lynx
     * @author Maurizio Graffio Mazzoneschi <graffio@lynxlab.com>
     * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU Public License v.3
     */

     define('API_VERSION','v1');
     /**
      * URL api 
      */
    define('URL_API','http://localhost/openlabor/api/'.API_VERSION.'/');
    define('DIR_API',ROOT_DIR.'/api/'.API_VERSION.'/');
    define('URL_API_REQUESTS',URL_API.'requests');

    /*
     * SERVICE CODE API OPEN 311
     */
    define('SEARCH_JOBS','001');
    define('JOB_REPORT','002');
    define('JOB_COMMENT','003');
    define('JOB_VOTE','004');
    define('SEARCH_TRAINING','005');
    define('TRAINING_REPORT','006');
    define('TRAINING_COMMENT','007');
    define('TRAINING_VOTE','008');
    