<?php
/******************************************************
// Standard configuration file for ADA versione 1.8
// Copyright Lynx 2006
// Released under GPL GNU license
/* ****************************************************



/* ****************************************************
/  1.JSGRaph  interface constants
/  This section can be modified  by installer
/
/*****************************************************/

// Height and width of each color sample
define('WIDTH',30);
define('HEIGHT',30);
// First find out where we are executed from
//define('DIR_BASE',"/home/ljp/www/jpgraph/dev/");
define('DIR_BASE',ROOT_DIR.'include/graph/');

// Who is going to own the created cache files?
define('FILE_UID',"ljp");
define('FILE_PERM',"666");
define('FILE_GROUP',"ljp");

// If the color palette is full should JpGraph try to allocate
// the closest match? If you plan on using background image or
// gradient fills it might be a good idea to enable this.
// If not you will otherwise get an error saying that the color palette is 
// exhausted. The drawback of using approximations is that the colors 
// might not be exactly what you specified. 
define('USE_APPROX_COLORS',true);

// Should usage of deprecated functions and parameters give a fatal error?
// (Useful to check if code is future proof.)
define('ERR_DEPRECATED',false);

// Should the time taken to generate each picture be branded to the lower
// left in corner in each generated image? Useful for performace measurements
// generating graphs
define('BRAND_TIMING',false);
define('BRAND_TIME_FORMAT',"Generated in: %01.3fs");

// Should we try to read from the cache? Set to false to bypass the
// reading of the cache and always re-generate the image and save it in
// the cache. Useful for debugging.
define('READ_CACHE',false);

// The full name of directory to be used as a cache. This directory MUST
// be readable and writable for PHP. Must end with '/'
define('CACHE_DIR',DIR_BASE.'jpgraph_cache/');

// Directory for TTF fonts. Must end with '/'
define('TTF_DIR',DIR_BASE.'ttf/');

// Decide if we should use the bresenham circle algorithm or the
// built in Arc(). Bresenham gives better visual apperance of circles 
// but is more CPU intensive and slower then the built in Arc() function
// in GD. Turned off by default for speed
define('USE_BRESENHAM',false);

// Deafult graphic format set to "auto" which will automtically
// choose the best available format in the order png,gif,jpg
// (The supported format depends on what your PHP installation supports)
define('DEFAULT_GFORMAT','auto');

//------------------------------------------------------------------
// Constants which are used as parameters for the method calls
//------------------------------------------------------------------

// TTF Font families
define('FF_COURIER',10);
define('FF_VERDANA',11);
define('FF_TIMES',12);
define('FF_HANDWRT',13);
define('FF_COMIC',14);
define('FF_ARIAL',15);
define('FF_BOOK',16);

// TTF Font styles
define('FS_NORMAL',1);
define('FS_BOLD',2);
define('FS_ITALIC',3);
define('FS_BOLDIT',4);

//Definitions for internal font
define('FONT0',1);		// Deprecated from 1.2
define('FONT1',2);		// Deprecated from 1.2
define('FONT1_BOLD',3);	// Deprecated from 1.2
define('FONT2',4);		// Deprecated from 1.2
define('FONT2_BOLD',5); // Deprecated from 1.2

define('FF_FONT0',1);
define('FF_FONT1',2);
define('FF_FONT2',3);


// Tick density
define('TICKD_DENSE',1);
define('TICKD_NORMAL',2);
define('TICKD_SPARSE',3);
define('TICKD_VERYSPARSE',4);

// Side for ticks and labels
define('SIDE_LEFT',-1);
define('SIDE_RIGHT',1);

// Legend type stacked vertical or horizontal
define('LEGEND_VERT',0);
define('LEGEND_HOR',1);

// Mark types 
define('MARK_SQUARE',1);
define('MARK_UTRIANGLE',2);
define('MARK_DTRIANGLE',3);
define('MARK_DIAMOND',4);
define('MARK_CIRCLE',5);
define('MARK_FILLEDCIRCLE',6);
define('MARK_CROSS',7);
define('MARK_STAR',8);
define('MARK_X',9);

// Styles for gradient color fill
define('GRAD_VER',1);
define('GRAD_HOR',2);
define('GRAD_MIDHOR',3);
define('GRAD_MIDVER',4);
define('GRAD_CENTER',5);
define('GRAD_WIDE_MIDVER',6);
define('GRAD_WIDE_MIDHOR',7);