<?php

/**
 * @package     comunica
 * @author      Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2020, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version     0.1
 */

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

$isValid = false;
if (@session_start() && session_status() === PHP_SESSION_ACTIVE) {
    $isValid = isset($_SESSION['sess_id_user']) ? intval($_SESSION['sess_id_user']) > 0 : false;
}
if ($isValid !== true) {
    header('HTTP/1.0 401 Unauthorized');
}
die();
