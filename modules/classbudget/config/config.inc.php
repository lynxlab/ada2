<?php
/**
 * CLASSBUDGET MODULE.
 *
 * @package        classbudget module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2015, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           classbudget
 * @version		   0.1
 */

define('MODULES_CLASSBUDGET_EDIT',			1); // edit budget action code
define('MODULES_CLASSBUDGET_CSV_EXPORT',	2); // csv export budget action code

if (!defined('PDF_EXPORT_FOOTER'))
	define ('PDF_EXPORT_FOOTER','ADA è un software opensource rilasciato sotto licenza GPL © Lynx s.r.l. - Roma');

/**
 * array for class budget components.
 * NOTE: title are to be translated in the script using the array 
 */
$classBudgetComponents = array (
		array ('classname'=>'classroomBudgetManagement'),
		array ('classname'=>'tutorBudgetManagement')
);
?>
