<?php


/**
 * Cache management
 * Static mode should  be active by default only for guest users ($id_profile == AMA_TYPE_VISITOR)
 * 
 * Constant ADA_CACHEMODE in config_install could have one of these values (cro config_main):
 *  ADA_NO_CACHE   	//always dynamically read from DB 
 *  ADA_READONLY_CACHE	//read only: the file is always loaded but never rewritten
 *  ADA_UPDATE_CACHE; 	//static rw: the node content is read from file only if lifetime is > $ic_lifetime
 *				// otherwise it is read from DB and then written back to file
 *  ADA_FORCE_UPDATE_CACHE 	//static rw: the node content  is read from DB and then written back to file
 *
 * But we should have a way to clear cached version or simply to force to dyanamic mode (e.g. when a node is edited)
 * So possible modes (cachemode GET param) are: 
 * 	cache (default: read from file only if it is up to date;  otherwise read from DB and then write to file)
 * 	nocache (read from db, don't write file)
 * 	updatecache (forced to read from db, write file)
 * 	readcache (read always from file if exists)
 * 
 * TODO: we should use the flag "static_mode" in the "modello_corso" table...
 */
/**
 * 
 *
 * @author stefano
 * 
 * 
 */
class CacheManager {
    var $static_mode = ADA_NO_CACHE;
    var $cache_mode = 'nocache';
    var $static_dir;
    
    
    
    public function __construct($id_profile) {
    
        $this->checkCache($id_profile);
    
    }    
    
    function checkCache($id_profile)   {
    // verify constants and GET parameter cachemode
    // sets the properties static_mode and cache_mode    
    switch ($id_profile){
        case AMA_TYPE_VISITOR:
        default:
           if (isset($_GET['cachemode'])){ // superseed constants
                $cache_mode = $_GET['cachemode'];
                switch ($cache_mode ){
                        case 'cache':
                        default:    
                               $static_mode = ADA_UPDATE_CACHE; //rw the node content is read from file only if lifetime is > $ic_lifetime
                                                       // otherwise it is read from DB and then written back to file
                                break;
                        case 'updatecache':
                                $static_mode = ADA_FORCE_UPDATE_CACHE; //rw: the node content  is read from DB and then written back to file
                                break;	
                        case 'readcache':
                                $static_mode = ADA_READONLY_CACHE; //read only: the file is always loaded but never rewritten
                                break;
                        case 'nocache':
                                $static_mode = ADA_NO_CACHE; //always dynamically read from DB, do not write to file
                                break;	

                        }
            } else {
                // default: 
                $static_mode = ADA_CACHEMODE; // from config_install
                switch ($static_mode){
                        case ADA_UPDATE_CACHE:
                                $cache_mode = 'cache'; //rw the node content is read from file only if lifetime is > $ic_lifetime
                                                       // otherwise it is read from DB and then written back to file
                                break;
                        case ADA_FORCE_UPDATE_CACHE:
                                $cache_mode = 'udatecache'; //rw: the node content  is read from DB and then written back to file
                                break;
                        case ADA_READONLY_CACHE:
                                $cache_mode = 'readcache'; //read only: the file is always loaded but never rewritten
                                break;
                        case ADA_NO_CACHE:
                        default:    
                                $cache_mode = 'nocache'; //always dynamically read from DB, do not write to file
                                break;	

                        }		

                }
                break;
        }

       $this->static_mode = $static_mode;
       $this->cache_mode = $cache_mode;

    } // end checkCache


    function getCachedData(){
        
        // read cache file 
        
            $media_path = $GLOBALS['media_path'];

            $file_static_time_ok = FALSE; 
            $file_static_version_ok =  FALSE;
            $this->static_dir = ROOT_DIR.$media_path."cache/";
            $static_http_dir = HTTP_ROOT_DIR.$media_path."cache/";;
            $this->static_filename = md5($_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
            $cached_file = $this->static_dir.$this->static_filename; 

            if ($this->static_mode > ADA_NO_CACHE){
                    $file_static_version_ok = @file_exists($cached_file);
                    if ($this->static_mode > ADA_READONLY_CACHE){
                        if ($this->static_mode < ADA_FORCE_UPDATE_CACHE){
                            $file_static_time_ok = (@filemtime($cached_file) > (time()-IC_LIFE_TIME));
                        } else {
                            $file_static_time_ok = TRUE;
                        }    
                    } else {
                            $file_static_time_ok = TRUE;
                    }	
            }	
            if ($file_static_version_ok AND $file_static_time_ok) {
                    readfile($cached_file);
                    // returns the file
             } else {
                    return NULL;
                    // if file doesn't exist, or is too old, etc we have to read node from DB
             }
    } //end getCachedData


    function writeCachedData ($id_profile,$layout_dataAR,$content_dataAr){
        // write contents & interface to file
        // uses ARE
        switch ($id_profile){
        case AMA_TYPE_VISITOR:
         if (
                 ($this->static_mode > ADA_READONLY_CACHE) OR 
                 ($this->cache_mode == 'cache') OR 
                 ($this->cache_mode == 'updatecache')
                 ){ // we have to (re)write the cache file
                            $static_optionsAr = array('static_dir' => $this->static_dir);
                            ARE::render($layout_dataAR,$content_dataAr,ARE_FILE_RENDER,$static_optionsAr);

                }
                break;
        }
     } //end writeCachedData
 
} //end Cache Manager

?>
