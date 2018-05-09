/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/*
 * If included from outside this module, it's includer duty to prepare 
 * a MODULES_GDPR_HTTP var holding the complete base url to the gdpr module itself
 * before including this file
 */
var MODULES_GDPR_HTTP = ('undefined' !== typeof MODULES_GDPR_HTTP && null!==MODULES_GDPR_HTTP) ? MODULES_GDPR_HTTP + '/' : '';

function loadCaptcha (imgID) {
	$j.ajax({
		type : "GET",
		url : MODULES_GDPR_HTTP + 'ajax/getCaptcha.php',
		beforeSend: function() {
			$j('#'+imgID).fadeOut('fast');
			$j('#'+imgID+'_error, #'+imgID+'reload').hide();
		}
	})
	.success(function(imageData) {
		$j('#'+imgID).attr('src', imageData);
		$j('#'+imgID+', #'+imgID+'reload').fadeIn('slow');
	})
	.fail(function(response) {
		$j('#'+imgID).attr('src','');
		$j('#'+imgID+'_error').show();
	});
}

function semanticConfirm(selector, callbacks) {
	$j(selector)
	  .modal('setting', $j.extend({}, {closable: false}, callbacks || {}))
	  .modal('show');
}
