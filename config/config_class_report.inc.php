<?php

define ('CONFIG_CLASS_REPORT', true);

// set to false to include column in report, false to exclude it
define ('REPORT_COLUMN_ID',               1);
define ('REPORT_COLUMN_STUDENT',          2);
define ('REPORT_COLUMN_HISTORY',          3);
define ('REPORT_COLUMN_LAST_ACCESS',      4);
define ('REPORT_COLUMN_TIME_IN_COURSE',  20);
define ('REPORT_COLUMN_EXERCISES',        5);
define ('REPORT_COLUMN_EXERCISES_TEST',   6);
define ('REPORT_COLUMN_EXERCISES_SURVEY', 7);
define ('REPORT_COLUMN_ADDED_NOTES',      8);
define ('REPORT_COLUMN_READ_NOTES',       9);
define ('REPORT_COLUMN_MESSAGE_COUNT_IN', 10);
define ('REPORT_COLUMN_MESSAGE_COUNT_OUT',11);
define ('REPORT_COLUMN_CHAT',             12);
define ('REPORT_COLUMN_BOOKMARKS',        13);
define ('REPORT_COLUMN_INDEX',            14);
define ('REPORT_COLUMN_STATUS',           18);
define ('REPORT_COLUMN_BADGES',           19);
define ('REPORT_COLUMN_LEVEL',            15);
// level plus and less are never put in the pdf, so their constants are just form HTML rendering
define ('REPORT_COLUMN_LEVEL_PLUS',       16);
define ('REPORT_COLUMN_LEVEL_LESS',       17);

// add to the relative array only unwanted cols constants

$GLOBALS['reportHTMLColArray'] = array ( 'REPORT_COLUMN_EXERCISES', 'REPORT_COLUMN_BOOKMARKS', 'REPORT_COLUMN_TIME_IN_COURSE' );
$GLOBALS['reportFILEColArray'] = array ( 'REPORT_COLUMN_EXERCISES', 'REPORT_COLUMN_BOOKMARKS', 'REPORT_COLUMN_TIME_IN_COURSE' );
