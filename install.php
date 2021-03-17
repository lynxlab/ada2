<?php
/**
 * INSTALLATION SCRIPT.
 *
 * @package		main
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

//Use the Composer classes
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

function output_buffer_off() {
    if (!headers_sent()) {
        // Disable gzip in PHP.
        ini_set('zlib.output_compression', 0);
        // Turn off output buffering
        ini_set('output_buffering', 'off');
        // Implicitly flush the buffer(s)
        ini_set('implicit_flush', true);
        // Force disable compression in a header.
        // Required for flush in some cases (Apache + mod_proxy, nginx, php-fpm).
        header('Content-Encoding: none');
        //prevent apache from buffering it for deflate/gzip
        header("Content-type: text/html");
        header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
    }

    // Fill-up 5 kB buffer (should be enough in most cases).
    echo str_pad(' ', 5 * 1024);
    // Flush all buffers.
    do {
        $flushed = @ob_end_flush();
    } while ($flushed);
    ob_implicit_flush(1);
    @ob_flush();
    flush();
}

function sendOK() {
    return sendToBrowser('[  OK  ]');
}

function sendFail() {
    return sendToBrowser('[ FAIL ]');
}

function sendSkip() {
    return sendToBrowser('[ SKIP ]');
}

function sendToBrowser ($message) {

	$style = '';
	$color = 'lightgray';

	if (strpos($message, 'text/javascript')!==false) {
		echo $message;
	} else {
		if (strpos($message, '...')!==false) {
            $style = 'width:auto; float: left; margin-right: 1em;';
            $message = sprintf("%-75s", $message);
        }
		if (strpos($message, '[')!==false  || strpos($message, ' SKIP ')!==false) $color='yellow';
        if (strpos($message, '**')!==false || strpos($message, ' FAIL ')!==false) $color='red';
        if (strpos($message, ' OK ')!==false) $color='#37fd37';

		echo '<pre style=\'color:'.$color.'; margin:0; font-size:1.1em; font-family:monospace; '.$style.'\'>';
		echo $message;
		echo '</pre>';
		echo '<script type="text/javascript">window.scrollTo(0,document.body.scrollHeight);</script>';
	}
	echo str_pad(' ', 4 * 1024);
	flush();
}

function createDB($saveData, $dbname, $options=[]) {
    $pdo = new PDO(
        'mysql:host='.$saveData['HOST'].';dbname=INFORMATION_SCHEMA',
        $saveData['USER'],
        $saveData['PASSWORD'], $options
    );
    $stmt = $pdo->query("CREATE DATABASE `".$dbname."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if ($stmt) {
        return new PDO(
            'mysql:host='.$saveData['HOST'].';dbname='.$dbname,
            $saveData['USER'],
            $saveData['PASSWORD'], $options
        );
    } else {
        throw new Exception(translateFN("Errore creazione Database"), 1);
    }
}

function checkDB ($saveData, $dbname, $options=[]) {
    $pdo = new PDO(
        'mysql:host='.$saveData['HOST'].';dbname=INFORMATION_SCHEMA',
        $saveData['USER'],
        $saveData['PASSWORD'], $options
    );
    $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$dbname."'");
    if ((bool) $stmt->fetchColumn()) {
        return new PDO(
            'mysql:host='.$saveData['HOST'].';dbname='.$dbname,
            $saveData['USER'],
            $saveData['PASSWORD'], $options
        );
    }
    return false;
}

function isEmptyDB($pdoconn, $dbname) {
    $stmt = $pdoconn->query("SHOW TABLES FROM `$dbname`");
    return $stmt->rowCount() == 0;
}

function importSQL ($filename, $pdoconn) {
    if (is_file($filename) && is_readable($filename)) {
        $sqlScript = file($filename);
        $query = '';
        foreach ($sqlScript as $line)	{
            $startWith = substr(trim($line), 0 ,2);
            $endWith = substr(trim($line), -1 ,1);
            if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '*/' ||  trim($startWith) == '*' || $startWith == '//') {
                continue;
            }
            $query = $query . $line;
            if ($endWith == ';') {
                $buffer = $pdoconn->prepare($query);
                $buffer->execute();
                unset($buffer);
                $query= '';
            }
        }
    } else throw new Exception(translateFN('File non trovato').' '.$filename);
}

putenv('PORTAL_NAME=ADA Install');
putenv('HTTP_ROOT_DIR=.');

/**
 * Files that MUST exists and be copied before doing anything
 */
foreach ([
    __DIR__ . '/config_path_DEFAULT.inc.php',
    __DIR__ . '/config/config_install_DEFAULT.inc.php'] as $mustfile) {
    if (!is_file($mustfile)) {
        die ("NO $mustfile, aborting installation!");
    }
    $destfile = str_replace('_DEFAULT','',$mustfile);
    if (!is_file($destfile)) {
        if (false === copy ($mustfile, $destfile)) {
            die("Cannot copy to $destfile, aborting installation!");
        }
    }
}

require_once realpath(dirname(__FILE__)).'/config_path.inc.php';
require_once ROOT_DIR.'/include/layout_classes.inc.php';
require_once ROOT_DIR.'/include/output_classes.inc.php';
require_once ROOT_DIR.'/include/DB_read.inc.php';
require_once ROOT_DIR.'/include/user_class.inc.php';

/**
 * redirect to homepage if ADA is installed, either with install script or manually
 */
if (is_dir('clients')) redirect(HTTP_ROOT_DIR);

session_start();
$_SESSION['sess_userObj'] = new ADAGuest();
$self = whoami();

if (!function_exists('translateFN')) {
    function translateFN($msg) { return $msg; }
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    output_buffer_off();
    $postData = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    $postData = array_map(function($el) {
        if (is_string($el)) return trim($el);
        return $el;
    }, $postData);
    if (array_key_exists('HTTP_ROOT_DIR', $postData)) {
        $postData['HTTP_ROOT_DIR'] = rtrim($postData['HTTP_ROOT_DIR'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
    $disabledModules = [];
    $modulesSQL = [];

    if (array_key_exists('MODULES_DISABLE', $postData)) {
        $disabledModules = explode(',',$postData['MODULES_DISABLE']);
        $disabledModules = array_map('trim', $disabledModules);
    }

    $multiprovider = true;
    // put here filenames to be imported in the common db and each provider db if multiprovider eq 0
    // $inBothIfNonMulti=['ada_gdpr_policy.sql', 'ada_login_module.sql'];
    $inBothIfNonMulti=[];
    // put here filenames to be imported in the common db if multiprovider eq 1
    $inCommonIfMulti=['ada_gdpr_policy.sql', 'ada_login_module.sql'];
    // put here filenames to be ALWAYS imported in the common db
    $inCommon=['ada_apps_module.sql',  'ada_secretquestion_module.sql', 'ada_impexport_module.sql'];

    try {
        if (array_key_exists('MYSQL', $postData) && array_key_exists('COMMON', $postData['MYSQL']) && is_array($postData['MYSQL']['COMMON']) && count($postData['MYSQL']['COMMON']) == 3) {
            $providers = isset($postData['PROVIDER']) && is_array($postData['PROVIDER']) ? $postData['PROVIDER'] : [];
            foreach ($providers as $i=>$provider) {
                $providers[$i]['pointer'] = str_replace(' ', '_', trim($provider['NAME']));
            }
            $options = [
                // PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];
            $commonExisted = true;
            $commonpdo = checkDB($postData['MYSQL']['COMMON'], $postData['COMMONDB'], $options);
            if ($commonpdo === false) {
                sendToBrowser(sprintf(translateFN('Creazione Database %s').' ...', $postData['COMMONDB']));
                $commonExisted = false;
                $commonEmpty = true;
                $commonpdo = createDB($postData['MYSQL']['COMMON'], $postData['COMMONDB'], $options);
                sendOK();
            } else {
                sendToBrowser(sprintf(translateFN('Database %s esistente').' ...', $postData['COMMONDB']));
                $commonEmpty = isEmptyDB($commonpdo, $postData['COMMONDB']);
                sendOk();
            }
            sendToBrowser(translateFN("Importazione Database common").' ...');
            if ($commonEmpty) {
                importSQL(ROOT_DIR . '/db/install/ada-empty-common.sql', $commonpdo);
                sendOK();
            } else sendSkip();

            // SET THE PASSWORD PROVIDED IN ADMIN_PASSWORD FOR USER 'adminAda'
            sendToBrowser(translateFN('Impostazione password utente adminAda').' ...');
            $sql = "UPDATE utente SET password=SHA1(\"".$postData['ADMIN_PASSWORD']."\") WHERE id_utente=1 AND password=\"\";";
            $stmt = $commonpdo->prepare($sql);
            $stmt->execute();
            sendOK();

            foreach ($providers as $i=>$provider) {
                $providers[$i]['pdoexisted'] = true;
                $providers[$i]['pdo'] = checkDB($postData['MYSQL'][$i], $provider['DB'], $options);
                if ($providers[$i]['pdo'] === false) {
                    sendToBrowser(sprintf(translateFN('Creazione Database %s').' ...', $provider['DB']));
                    $providers[$i]['pdoexisted'] = false;
                    $providers[$i]['empty'] = true;
                    $providers[$i]['pdo'] = createDB($postData['MYSQL'][$i], $provider['DB'], $options);
                    sendOK();
                } else {
                    sendToBrowser(sprintf(translateFN('Database %s esistente').' ...', $provider['DB']));
                    $providers[$i]['empty'] = isEmptyDB($providers[$i]['pdo'], $provider['DB']);
                    sendOk();
                }
                sendToBrowser(sprintf(translateFN('Importazione Database %s').' ...', $provider['DB']));
                if ($providers[$i]['empty']) {
                    importSQL(ROOT_DIR . '/db/ada_provider_empty.sql', $providers[$i]['pdo']);

                    $adminRow = "SELECT * FROM ".$postData['COMMONDB'].".utente WHERE id_utente=1;";
                    $stmt = $commonpdo->prepare($adminRow);
                    $stmt->execute();
                    $adminData = $stmt->fetch(PDO::FETCH_ASSOC);

                    $fields = '`' . implode('`, `', array_keys($adminData)) . '`';
                    $fields_data = implode(', ', array_map(function() { return '?'; }, $adminData));
                    $sql =  "INSERT INTO `".$provider['DB']."`.`utente` (${fields}) VALUES (${fields_data});";
                    $stmt = $providers[$i]['pdo']->prepare($sql);
                    $stmt->execute(array_values($adminData));
                    unset($stmt);

                    $sql = "INSERT INTO ".$provider['DB'].".amministratore_sistema (id_utente_amministratore_sist) VALUES (1);";
                    $stmt = $providers[$i]['pdo']->prepare($sql);
                    $stmt->execute();
                    unset($stmt);
                    $sql = "INSERT INTO tester(nome,puntatore) VALUES ('".$provider['NAME']."', '".$providers[$i]['pointer']."'); INSERT INTO utente_tester(id_utente, id_tester) VALUES (1, LAST_INSERT_ID());";
                    $stmt = $commonpdo->prepare($sql);
                    $stmt->execute();
                    unset($stmt);
                    sendOK();
                } else sendSkip();

                sendToBrowser(sprintf(translateFN("Configurazione provider %s").'...', $provider['NAME']));
                if (!is_dir(ROOT_DIR . '/clients/'.$providers[$i]['pointer']) && !is_file(ROOT_DIR . '/clients/'.$providers[$i]['pointer'].'/client_conf.inc.php')) {
                    mkdir(ROOT_DIR . '/clients/'.$providers[$i]['pointer'], 0770, true);
                    $outfile = str_replace(
                        [ '${UPPERPROVIDER}', '${ASISPROVIDER}_provider', '${PROV_HTTP}', '${MYSQL_USER}', '${MYSQL_PASSWORD}', '${MYSQL_HOST}', ],
                        [ strtoupper($providers[$i]['pointer']), $provider['DB'], $postData['HTTP_ROOT_DIR'], $postData['MYSQL'][$i]['USER'], $postData['MYSQL'][$i]['PASSWORD'], $postData['MYSQL'][$i]['HOST'], ],
                        file_get_contents(ROOT_DIR . '/clients_DEFAULT/install-templates/client_conf.inc.php')
                    );
                    if (false === file_put_contents(ROOT_DIR . '/clients/'.$providers[$i]['pointer'].'/client_conf.inc.php', $outfile)) {
                        throw new Exception(translateFN('Impossibile scrivere il file di configurazione del provider'));
                    } else sendOK();
                } else sendSkip();
            }

            if (is_dir(MODULES_DIR)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(MODULES_DIR . DIRECTORY_SEPARATOR));

                $regIter = new RegexIterator($iterator, '/^.+\.sql$/i', RecursiveRegexIterator::GET_MATCH);
                foreach ($regIter as $x) {
                    $modulesSQL = array_merge($modulesSQL, $x);
                }
                usort($modulesSQL, function($a, $b) {
                    // dirty hack to order by filename, having files that starts with a number as last elements
                    return strnatcmp('1'.basename($a). DIRECTORY_SEPARATOR . $a, '1'.basename($b) . DIRECTORY_SEPARATOR . $b );
                });

                // import modules sql in the databases
                if (is_array($modulesSQL) && count($modulesSQL)>0) {
                    foreach($modulesSQL as $sqlFile) {
                        if (stristr($sqlFile, "menu") !== false ||
                            in_array(basename($sqlFile), $inCommon) ||
                            (!$multiprovider && in_array(basename($sqlFile), $inBothIfNonMulti)) ||
                            ( $multiprovider && in_array(basename($sqlFile), $inCommonIfMulti))) {
                                sendToBrowser(translateFN("Importazione").' '.str_replace(ROOT_DIR, '', $sqlFile).' in '.$postData['COMMONDB'].' ...');
                                if ($commonEmpty) {
                                    importSQL($sqlFile, $commonpdo);
                                    sendOK();
                                } else sendSkip();
                        }
                    }
                    // done with the common db, now the providers
                    foreach ($providers as $i=>$provider) {
                        foreach($modulesSQL as $sqlFile) {
                            if (stristr($sqlFile, "menu") === false &&
                                !in_array(basename($sqlFile), $inCommon) &&
                                !( $multiprovider && in_array(basename($sqlFile), $inCommonIfMulti))
                                ) {
                                    sendToBrowser(translateFN("Importazione").' '.str_replace(ROOT_DIR, '', $sqlFile).' in '.$provider['DB'].' ...');
                                    if ($providers[$i]['empty']) {
                                        importSQL($sqlFile, $provider['pdo']);
                                        sendOK();
                                    } else sendSkip();
                            }
                        }
                    }
                }

                // modules config files setup
                $regIter = new RegexIterator($iterator, '/\/.+\/config\_DEFAULT\.inc\.php$/', RecursiveRegexIterator::GET_MATCH);
                $configFiles = [];
                foreach ($regIter as $x) {
                    $configFiles = array_merge($configFiles, $x);
                }
                if (is_array($configFiles) && count($configFiles)>0) {
                    foreach($configFiles as $configFile) {
                        $dirname = dirname($configFile);
                        $modulename = basename(str_replace('config','',$dirname));
                        sendToBrowser(translateFN("Configurazione modulo").' '.$modulename.' ...');
                        if (!in_array($modulename, $disabledModules)) {
                            if (is_dir($dirname) && is_writable($dirname)) {
                                $destFile = $dirname. DIRECTORY_SEPARATOR . str_replace('_DEFAULT','',basename($configFile));
                                if (!is_file($destFile)) {
                                    if (copy($configFile, $destFile)) sendOK();
                                    else sendFail();
                                } else sendSkip();
                            } else {
                                sendFail();
                                sendToBrowser('** '.translateFN('Impossibile scrivere nella directory del modulo'));
                            }
                        } else sendSkip();
                    }
                }

                // modules composer dependencies download
                $regIter = new RegexIterator($iterator, '/\/.+\/composer\.json$/', RecursiveRegexIterator::GET_MATCH);
                $composerFiles = [];
                foreach ($regIter as $x) {
                    $composerFiles = array_merge($composerFiles, $x);
                }
                if (is_array($composerFiles) && count($composerFiles)>0) {
                    // Composer in php code, thanks to https://stackoverflow.com/a/17244866
                    define('COMPOSER_DIRECTORY', ADA_UPLOAD_PATH . 'composer');
                    if (!is_dir(COMPOSER_DIRECTORY)) mkdir(COMPOSER_DIRECTORY);
                    if (file_exists(COMPOSER_DIRECTORY.'/vendor/autoload.php') !== true) {
                        sendToBrowser(translateFN('Download composer').'...');
                        copy('https://getcomposer.org/composer-stable.phar', COMPOSER_DIRECTORY . DIRECTORY_SEPARATOR . 'Composer.phar');
                        sendOK();
                        sendToBrowser(translateFN('Estrazione composer').'...');
                        $composerPhar = new Phar(COMPOSER_DIRECTORY . DIRECTORY_SEPARATOR . 'Composer.phar');
                        $composerPhar->extractTo(COMPOSER_DIRECTORY);
                        sendOK();
                        unset($composerPhar);
                    }
                    ini_set('memory_limit', '512M');
                    // Composer\Factory::getHomeDir() method needs COMPOSER_HOME environment variable set
                    putenv('COMPOSER_HOME=' . COMPOSER_DIRECTORY);
                    //This requires the phar to have been extracted successfully.
                    require_once (COMPOSER_DIRECTORY.'/vendor/autoload.php');

                    foreach($composerFiles as $composerFile) {
                        $dirname = dirname($composerFile);
                        $modulename = basename($dirname);
                        if (stristr($composerFile,'vendor') === false) {
                            sendToBrowser(translateFN('Installazione dipendenze per il modulo').' '.$modulename.' ...');
                            // if (!in_array($modulename, $disabledModules)) {
                                if (is_dir($dirname) && is_writable($dirname)) {
                                    chdir($dirname);
                                    // Create the commands
                                    $input = new ArrayInput(array('command' => 'update'));
                                    // Create the application and run it with the commands
                                    $application = new Application();
                                    $application->setAutoExit(false); // prevent `$application->run` method from exitting the script
                                    $output = $application->run($input);
                                    if ($output == 0) {
                                        sendOK();
                                    } else {
                                        sendFail();
                                        sendToBrowser('** '.translateFN('Problemi con composer'));
                                    }
                                    chdir(__DIR__);
                                } else {
                                    sendFail();
                                    sendToBrowser('** '.translateFN('Impossibile scrivere nella directory del modulo'));
                                }
                            // } else sendSkip();
                        }
                    }
                }
            }

            // create file with environment vars, this MUST BE the last step and if the ENV_FILENAME
            // is written without errors, it should be safe to consider ADA as installed
            sendToBrowser(translateFN('Generazione file configurazione').' ...');
            if (!is_file(ENV_FILENAME)) {
                // form variable to environment variable name mappings
                $formtoenv = [
                    'PORTAL_NAME' => 'PORTAL_NAME',
                    'COMMONDB' => 'MYSQL_DATABASE',
                    'HTTP_ROOT_DIR' => 'HTTP_ROOT_DIR',
                    'ADA_ADMIN_MAIL_ADDRESS' => 'ADA_ADMIN_MAIL_ADDRESS',
                    'ADA_NOREPLY_MAIL_ADDRESS' => 'ADA_NOREPLY_MAIL_ADDRESS'
                ];
                $defaultProvider = array_key_exists('DEFAULT_PROVIDER', $postData) && intval($postData['DEFAULT_PROVIDER'])>0 ? intval($postData['DEFAULT_PROVIDER']) : 0;
                $envlines = [
                    'ADA_OR_WISP' => "putenv('ADA_OR_WISP=ADA')",
                    'MULTIPROVIDER' => "putenv('MULTIPROVIDER=".intval($multiprovider)."')",
                    'MYSQL_USER' => "putenv('MYSQL_USER=".$postData['MYSQL']['COMMON']['USER']."')",
                    'MYSQL_PASSWORD' => "putenv('MYSQL_PASSWORD=".$postData['MYSQL']['COMMON']['PASSWORD']."')" ,
                    'MYSQL_HOST' => "putenv('MYSQL_HOST=".$postData['MYSQL']['COMMON']['HOST']."')",
                    'DEFAULT_PROVIDER_POINTER' => "putenv('DEFAULT_PROVIDER_POINTER=".$providers[$defaultProvider]['pointer']."')",
                    'DEFAULT_PROVIDER_DB' => "putenv('DEFAULT_PROVIDER_DB=".$providers[$defaultProvider]['DB']."')",
                    'DEFAULT_PROVIDER_DB_USER' => "putenv('DEFAULT_PROVIDER_DB_USER=".$postData['MYSQL'][$defaultProvider]['USER']."')",
                    'DEFAULT_PROVIDER_DB_PASS' => "putenv('DEFAULT_PROVIDER_DB_PASS=".$postData['MYSQL'][$defaultProvider]['PASSWORD']."')",
                    'DEFAULT_PROVIDER_DB_HOST' => "putenv('DEFAULT_PROVIDER_DB_HOST=".$postData['MYSQL'][$defaultProvider]['HOST']."')",
                ];
                foreach($formtoenv as $formkey => $envvar) {
                    if (array_key_exists($formkey, $postData) && strlen($postData[$formkey])>0) {
                        if ($formkey == 'HTTP_ROOT_DIR') {
                            $postData[$formkey] = rtrim($postData[$formkey], DIRECTORY_SEPARATOR);
                        }
                        $envlines[$formkey] = "putenv('$envvar=".$postData[$formkey]."')";
                    }
                }
                if (false === file_put_contents(ENV_FILENAME, "<?php".PHP_EOL.implode(';'.PHP_EOL, array_values($envlines)).";".PHP_EOL)) {
                    throw new Exception(translateFN('Impossibile scrivere il file di configurazione principale'));
                } else {
                    chmod(ENV_FILENAME, 0440);
                    sendOK();
                }
            } else sendSkip();

            sendToBrowser(translateFN('Rimozione file temopranei').' ...');
            delTree(COMPOSER_DIRECTORY) ? sendOK() : sendFail();

            sendToBrowser('&nbsp;');
            sendToBrowser(PHP_EOL."<strong>".translateFN("ADA è installata, naviga su:") . " <a style='color:lime;' href='".
            $postData['HTTP_ROOT_DIR']."' target='_top'>".$postData['HTTP_ROOT_DIR']."</a></strong>");
            sendToBrowser('<script type="text/javascript">window.parent.postMessage("doneOK", "*");</script>');

        } else throw new Exception(translateFN('Parametri MySQL/MariaDB non validi'), 1);
    } catch (\Exception $e) {
        sendFail();
        sendToBrowser('** '.$e->getMessage().' ('.$e->getCode().')');
        sendToBrowser('<script type="text/javascript">window.parent.postMessage("doneException", "*");</script>');
        die();
    }
} else {
    $modulesAv = [];
    $modulesDIS = [ 'secretquestion','code_man' ];
    if (is_dir(MODULES_DIR)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(MODULES_DIR . DIRECTORY_SEPARATOR));
        $regIter = new RegexIterator($iterator, '/\/.+\/config\_DEFAULT\.inc\.php$/', RecursiveRegexIterator::GET_MATCH);
        $configFiles = [];
        foreach ($regIter as $x) {
            $configFiles = array_merge($configFiles, $x);
        }
        if (is_array($configFiles) && count($configFiles)>0) {
            foreach($configFiles as $configFile) {
                $dirname = dirname($configFile);
                $modulesAv[] = basename(str_replace('config','',$dirname));
            }
        }
    }
    sort($modulesAv);
    sort($modulesDIS);
    $modulesDIS = array_intersect($modulesDIS, $modulesAv);

    /**
     * Sends data to the rendering engine
     */
    ARE::render(
        [
            'node_type' => null,
            'family' => 'ada_blu',
            'node_author_id' => null,
            'node_course_id' => null,
            'module_dir' => null
        ],
        [
            'modsavailable' => count($modulesAv)>0 ? translateFN('Moduli disabilitabili').': '.implode(', ', $modulesAv) : null,
            'modsdisabled' => implode(', ',$modulesDIS)
        ],
        null,
        [
            'onload_func' => 'initDoc();'
        ]
    );
}
