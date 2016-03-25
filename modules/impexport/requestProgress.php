<?php
/**
 * IMPORT MODULE
 *
 * @package		export/import course
 * @author			giorgio <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			impexport
 * @version		0.1
 */

/**
 * This files request and echo in json format the importProgress session vars
 * to be displayed to the user using jquery calls
 */
ini_set('display_errors', '0'); error_reporting(E_ALL);

session_start();


header('Content-Type: application/json');
if (isset($_SESSION['importProgress']))
{
	echo json_encode($_SESSION['importProgress']);
}
?>