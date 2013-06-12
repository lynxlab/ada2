<?php
/**
 * Media Manager
 *
 * @package		Services / Node
 * stamos
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2011, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */

$variableToClearAR = array('layout','user','course','course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
 */
$neededObjAr = array();

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/comunica/include/adaChatUtilities.inc.php';
$self = whoami();

/*
 * YOUR CODE HERE
 */

if ($op == 'read') {
    if (!isset($_POST['nome_file']) || !isset($_POST['id_utente'])) {
        exitWith_JSON_Error(translateFN('Errore: parametri passati allo script PHP non corretti'));
    }
    $filename = $_POST['nome_file'];
    $author_id = $_POST['id_utente'];
    $media_found = $dh->get_risorsa_esterna_info_autore($filename, $author_id);
    if (AMA_DataHandler::isError($media_found) AND $media_found->code == AMA_ERR_GET) {
        exitWith_JSON_Error(translateFN('Errore: Media non trovato'));
    }
   print(json_encode($media_found));


} elseif (!isset($op) || $op = 'insert' || $op == 'update') {
    /*
     * Check that this script was called with the right arguments.
     * If not, stop script execution and report an error to the caller.
     */
    if (!isset($_POST['nome_file']) || !isset($_POST['tipo']) || !isset($_POST['id_utente'])) {
      exitWith_JSON_Error(translateFN('Errore: parametri passati allo script PHP non corretti'));
    }

    $filename  = $_POST['nome_file'];
    $tipo_file  = $_POST['tipo'];
    $author_id = $_POST['id_utente'];
    $copyright = $_POST['copyright'];
    $keywords = $_POST['keywords'];
    $titolo = $_POST['titolo'];
    $descrizione = $_POST['descrizione'];
    $pubblicato = $_POST['pubblicato'];
    $lingua = $_POST['lingua'];

    $res_ha['nome_file'] = $nome_file;
    $res_ha['tipo'] = $tipo_file;
    $res_ha['id_utente'] = $id_utente_autore;
    $res_ha['copyright'] = $copyright;
    $res_ha['keywords']= $keywords;
    $res_ha['titolo'] = $titolo;
    $res_ha['descrizione'] = $descrizione;
    if ($pubblicato == 'on') {
        $pubblicato = 1;
    }else {
        $pubblicato = 0;
    }
    $res_ha['pubblicato'] = $pubblicato;
    $res_ha['lingua'] = $lingua;

    $media_found = $dh->get_risorsa_esterna_info_autore($filename, $author_id);
    if (AMA_DataHandler::isError($media_found) AND $media_found->code == AMA_ERR_NOT_FOUND) {
        $op = 'insert';
        $id_res_ext = add_only_in_risorsa_esterna($res_ha);
        if (AMA_DataHandler::isError($id_res_ext)) {
            exitWith_JSON_Error(translateFN("Errore nell'inserimento del media"));
        }
        $response = array();
        $response['result'] = 'Inserimento media riuscito';
    } elseif (isset($media_found['id_risorsa_ext'])) {
        $op = 'update';
        $id_res_ext = $media_found['id_risorsa_ext'];
        $update_media = $dh->set_risorsa_esterna ($id_res_ext, $res_ha);
        if (AMA_DataHandler::isError($update_media)) {
            exitWith_JSON_Error(translateFN("Errore nell'aggiornamento del media"));
        }
        $response = array();
        $response['result'] = 'Aggiornamento media riuscito';
    }else {
        if (AMA_DataHandler::isError($media_found) AND $media_found->code == AMA_ERR_GET) {
            $response = array();
            $response['result'] = 'Errore AMA ';
        }

    }

    print(json_encode($response));
}

