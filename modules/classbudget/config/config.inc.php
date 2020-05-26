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

define('MODULES_CLASSBUDGET_EDIT',				1); // edit budget action code
define('MODULES_CLASSBUDGET_CSV_EXPORT',		2); // csv export budget action code
define('MODULES_CLASSBUDGET_EDIT_COST_ITEM',	3); // edit cost item action code

if (!defined('PDF_EXPORT_FOOTER'))
	define ('PDF_EXPORT_FOOTER','ADA è un software opensource rilasciato sotto licenza GPL © Lynx s.r.l. - Roma');

/**
 * array for class budget components.
 * costitem is the basic cost management class
 * the script using this module is responsible for
 * checking wich modules are installed and possibily
 * add the classroom and tutor cost management
 */
$GLOBALS['classBudgetComponents'] = array (
		array ('classname'=>'costitemBudgetManagement')
);

define ('MODULES_CLASSBUDGET_COST_ITEM_UNA_TANTUM',		10); // one-shot cost item
define ('MODULES_CLASSBUDGET_COST_ITEM_PER_STUDENT',	11); // per student cost item
define ('MODULES_CLASSBUDGET_COST_ITEM_PER_NODE',		12); // per node cost item

$GLOBALS['availableCostItems'] = array(
		MODULES_CLASSBUDGET_COST_ITEM_UNA_TANTUM => 'una tantum',
		MODULES_CLASSBUDGET_COST_ITEM_PER_STUDENT => 'ogni studente',
		MODULES_CLASSBUDGET_COST_ITEM_PER_NODE => 'ogni nodo'
);
