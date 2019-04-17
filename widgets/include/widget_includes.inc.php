<?php
/*
 * windget includes.inc.php
 *
 * Copyright 2013 stefano <steve@lynxlab.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 */



/**
 * utility functions
 */
require_once ROOT_DIR.'/include/utilities.inc.php';

/**
 * Database and data validator classes
 */
require_once ROOT_DIR.'/include/database_includes.inc.php';

/**
 * Functions used  to check session objects
 */
require_once ROOT_DIR.'/include/module_init_functions.inc.php';

// old translateFN function
require_once ROOT_DIR.'/include/output_funcs.inc.php';

// new translator class
require_once ROOT_DIR.'/include/translator_class.inc.php';

//include_once ROOT_DIR.'browsing/include/browsing_functions.inc.php';

/**
 * Ada Rendering Engine, used to render module output data
 */
// require_once ROOT_DIR.'/include/layout_classes.inc.php';
// require_once ROOT_DIR.'/include/output_classes.inc.php';

require_once ROOT_DIR . '/include/HTML_element_classes.inc.php';
require_once ROOT_DIR . '/include/navigation_history.inc.php';


/* widgets */
include_once ROOT_DIR.'/widgets/include/ajax_remote_class.inc.php';
include_once ROOT_DIR.'/widgets/include/widgets_inc.php';
include_once ROOT_DIR.'/include/ArrayToXML/array2xml.inc.php';
