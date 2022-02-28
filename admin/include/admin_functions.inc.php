<?php

/**
 * ADMIN FUNCTIONS
 *
 * @package
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
require_once ROOT_DIR . '/include/HtmlLibrary/AdminModuleHtmlLib.inc.php';
require_once ROOT_DIR . '/include/ViewBaseHelper.php';

/**
 * Admin helper class
 */
class AdminHelper extends ViewBaseHelper
{
  /**
   * Builds array keys for the admin directory scripts
   *
   * @param array $neededObjAr
   *
   * @return array
   */
  public static function init(array $neededObjAr = array())
  {
    if (count(self::$helperData) === 0) {
      self::$helperData = parent::init($neededObjAr);
      self::$helperData = array_merge(
        self::$helperData,
        [
          'user_level' => ADA_MAX_USER_LEVEL,
          'user_score' => '',
          'user_status' => '',
          'user_uname' => self::$helperData['userObj']->getUserName(),
          'user_surname' => self::$helperData['userObj']->getLastName(),
          'user_mail' => self::$helperData['userObj']->getEmail(),
          'user_messages' => self::getUserMessages(self::$helperData['userObj']),
        ],
        self::buildGlobals()
      );
      self::extract();
    }
    return self::getHelperData();
  }

  public static function createProvider($providerData)
  {
    $retarray = [
      'status' => false,
      'message' => translateFN('Errore sconosciuto'),
    ];
    $DBoptions = [
      // PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
    $modulesSQL = [];
    $inCommon = ModuleLoaderHelper::inCommon();
    $inCommonIfMulti = ModuleLoaderHelper::inCommonIfMulti();
    try {
      // create and import DB
      $providerPDO = self::checkDB([
        'HOST' => $providerData['dbhost'],
        'USER' => $providerData['username'],
        'PASSWORD' => $providerData['password']], $providerData['dbname'], $DBoptions);
      if ($providerPDO !== false) {
        if (self::isEmptyDB($providerPDO, $providerData['dbname'])) {
          if (is_readable(ROOT_DIR . '/db/ada_provider_empty.sql')) {
            self::importSQL(ROOT_DIR . '/db/ada_provider_empty.sql', $providerPDO);
            if (is_dir(MODULES_DIR)) {
              $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(MODULES_DIR . DIRECTORY_SEPARATOR));

              $regIter = new RegexIterator($iterator, '/^.+\.sql$/i', RecursiveRegexIterator::GET_MATCH);
              foreach ($regIter as $x) {
                  $modulesSQL = array_merge($modulesSQL, $x);
              }
              $modulesSQL = array_filter($modulesSQL, function($sqlFile) use ($inCommon, $inCommonIfMulti){
                return stristr($sqlFile, "vendor") === false && stristr($sqlFile, "menu") === false && !in_array(basename($sqlFile), $inCommon) && !(MULTIPROVIDER && in_array(basename($sqlFile), $inCommonIfMulti));
              });
              usort($modulesSQL, function($a, $b) {
                  // dirty hack to order by filename, having files that starts with a number as last elements
                  return strnatcmp('1'.basename($a). DIRECTORY_SEPARATOR . $a, '1'.basename($b) . DIRECTORY_SEPARATOR . $b );
              });
              // import modules sql in the databases
              if (is_array($modulesSQL) && count($modulesSQL)>0) {
                foreach($modulesSQL as $sqlFile) {
                  set_time_limit(300);
                  self::importSQL($sqlFile, $providerPDO);
                }
              }
            }
          } else {
            throw new \Exception(translateFN("Errore nell'importazione delle tabelle."));
          }
        } else {
          throw new \Exception(translateFN("Il database specificato deve essere vuoto."));
        }
      } else {
        throw new \Exception(translateFN("Errore nella connessione al database specificato."));
      }

      if (!is_file(ROOT_DIR . '/clients/' . $providerData['pointer'] . '/client_conf.inc.php')) {
        if (!is_dir(ROOT_DIR . '/clients/' . $providerData['pointer'])) {
          mkdir(ROOT_DIR . '/clients/' . $providerData['pointer'], 0770, true);
        }
        $outfile = str_replace(
          ['${UPPERPROVIDER}', '${ASISPROVIDER}_provider', '${PROV_HTTP}', '${MYSQL_USER}', '${MYSQL_PASSWORD}', '${MYSQL_HOST}',],
          [
            strtoupper($providerData['pointer']),
            $providerData['dbname'],
            HTTP_ROOT_DIR,
            $providerData['username'],
            $providerData['password'],
            $providerData['host'],
          ],
          file_get_contents(ROOT_DIR . '/clients_DEFAULT/install-templates/client_conf.inc.php')
        );
        if (false === file_put_contents(ROOT_DIR . '/clients/' . $providerData['pointer'] . '/client_conf.inc.php', $outfile)) {
          $retarray['message'] = translateFN('Impossibile scrivere il file di configurazione del provider');
        } else {
          $retarray['status'] = true;
          unset($retarray['message']);
        }
      } else {
        $retarray['message'] = translateFN('La configurazione del provider già esiste, provare con un altro nome');
      }
    } catch (\Exception $e) {
      $retarray['message'] = $e->getMessage();
    }

    return $retarray;
  }

  public static function createDB($saveData, $dbname, $options = [])
  {
    $pdo = new PDO(
      'mysql:host=' . $saveData['HOST'] . ';dbname=INFORMATION_SCHEMA',
      $saveData['USER'],
      $saveData['PASSWORD'],
      $options
    );
    $stmt = $pdo->query("CREATE DATABASE `" . $dbname . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if ($stmt) {
      return new PDO(
        'mysql:host=' . $saveData['HOST'] . ';dbname=' . $dbname,
        $saveData['USER'],
        $saveData['PASSWORD'],
        $options
      );
    } else {
      throw new Exception(translateFN("Errore creazione Database"), 1);
    }
  }

  public static function checkDB($saveData, $dbname, $options = [])
  {
    $pdo = new PDO(
      'mysql:host=' . $saveData['HOST'] . ';dbname=INFORMATION_SCHEMA',
      $saveData['USER'],
      $saveData['PASSWORD'],
      $options
    );
    $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $dbname . "'");
    if ((bool) $stmt->fetchColumn()) {
      return new PDO(
        'mysql:host=' . $saveData['HOST'] . ';dbname=' . $dbname,
        $saveData['USER'],
        $saveData['PASSWORD'],
        $options
      );
    }
    return false;
  }

  public static function isEmptyDB($pdoconn, $dbname)
  {
    $stmt = $pdoconn->query("SHOW TABLES FROM `$dbname`");
    return $stmt->rowCount() == 0;
  }

  public static function importSQL($filename, $pdoconn)
  {
    if (is_file($filename) && is_readable($filename)) {
      $sqlScript = file($filename);
      $query = '';
      foreach ($sqlScript as $line) {
        $startWith = substr(trim($line), 0, 2);
        $endWith = substr(trim($line), -1, 1);
        if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '*/' ||  trim($startWith) == '*' || $startWith == '//') {
          continue;
        }
        $query = $query . $line;
        if ($endWith == ';') {
          $buffer = $pdoconn->prepare($query);
          $buffer->execute();
          unset($buffer);
          $query = '';
        }
      }
    } else throw new Exception(translateFN('File non trovato') . ' ' . $filename);
  }
}
