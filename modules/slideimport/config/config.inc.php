<?php
/**
 * SLIDEIMPORT MODULE.
 *
 * @package        slideimport module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           slideimport
 * @version		   0.1
 */

/**
 * session var name of the uploaded file
 */
define ('MODULES_SLIDEIMPORT_UPLOAD_SESSION_VAR','slideimportFile');

define ('IMPORT_IMAGE_HEIGHT', 800);
define ('IMPORT_PREVIEW_HEIGHT', 210);

define ('IMAGE_FORMAT','jpg');
define ('IMAGE_COMPRESSION_QUALITY',90);
define ('IMAGE_HEADER_PREVIEW','image/jpeg');

/**
 * Define IMPORT_MIME_TYPE as a subset of ADA_MIME_TYPE
 */
$IMPORT_MIME_TYPE["application/pdf"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/x-pdf"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/msword"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/mspowerpoint"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.ms-powerpoint"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.ms-excel"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.ms-office"]['permission'] = _GO;
// docx, xslx, pptx etc...
$IMPORT_MIME_TYPE["application/vnd.openxmlformats-officedocument.wordprocessingml.document"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.openxmlformats-officedocument.wordprocessingml.template"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.openxmlformats-officedocument.spreadsheetml.template"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.openxmlformats-officedocument.presentationml.presentation"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.openxmlformats-officedocument.presentationml.template"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.openxmlformats-officedocument.presentationml.slideshow"]['permission'] = _GO;
// odt, ods, odp etc...
$IMPORT_MIME_TYPE["application/vnd.oasis.opendocument.text"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.oasis.opendocument.spreadsheet"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.oasis.opendocument.presentation"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.oasis.opendocument.graphics"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.oasis.opendocument.chart"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.oasis.opendocument.image"]['permission'] = _GO;
$IMPORT_MIME_TYPE["application/vnd.oasis.opendocument.text-master"]['permission'] = _GO;
