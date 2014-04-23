<?php
/**
 * dataTables jQuery plugin dynamic translation file for ADA.
 *
 *
 *
 * PHP version >= 5.0
 *
 * @package		
 * @author		Giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2014, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		index
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../../../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 * $_SESSION was destroyed, so we do not need to clear data in session.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_SWITCHER, AMA_TYPE_ADMIN);
/**
 * Performs basic controls before entering this module
*/
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';

echo '{
    "sEmptyTable":     "'.translateFN("Nessun dato presente nella tabella").'",
    "sInfo":           "'.sprintf (translateFN("Vista da %s a %s di %s elementi"),'_START_','_END_','_TOTAL_').'",
    "sInfoEmpty":      "'.sprintf (translateFN("Vista da %s a %s di %s elementi"),'0','0','0').'",
    "sInfoFiltered":   "('.sprintf (translateFN("filtrati da %s elementi totali"),'_MAX_').')",
    "sInfoPostFix":    "'."".'",
    "sInfoThousands":  "'.",".'",
    "sLengthMenu":     "'.sprintf (translateFN("Visualizza %s elementi"),'_MENU_').'",
    "sLoadingRecords": "'.translateFN("Caricamento").'...",
    "sProcessing":     "'.translateFN("Elaborazione").'...",
    "sSearch":         "'.translateFN("filtra").':",
    "sZeroRecords":    "'.translateFN("La ricerca non ha portato alcun risultato").'.",
    "oPaginate": {
        "sFirst":      "'.translateFN("Inizio").'",
        "sPrevious":   "'.translateFN("Precedente").'",
        "sNext":       "'.translateFN("Successivo").'",
        "sLast":       "'.translateFN("Fine").'"
    },
    "oAria": {
        "sSortAscending":  ": '.translateFN("attiva per ordinare la colonna in ordine crescente").'",
        "sSortDescending": ": '.translateFN("attiva per ordinare la colonna in ordine decrescente").'"
    }
}';