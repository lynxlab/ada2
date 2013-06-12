/**
 * FormValidator file
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
/**
 * Description of validateContent
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
function validateContent(elements, regexps) {
	var error_found = false;
	for (i in elements) {
		var label = 'l_' + elements[i];
		var element = elements[i];
		var regexp = regexps[i];
		var value = null;
		if ($(element) != null && $(element).getValue) {
			value = $(element).getValue();
		}

		if (value != null) {
			if(!value.match(regexp)) {
				if($(label)) {
					$(label).addClassName('error');
				}
				error_found = true;
			}
			else {
				if($(label)) {
					$(label).removeClassName('error');
				}
			}
		}
	}

	if (error_found) {
		if($('error_form')) {
			$('error_form').addClassName('show_error');
			$('error_form').removeClassName('hide_error');
		}
	}
	else {
		if($('error_form')) {
			$('error_form').addClassName('hide_error');
			$('error_form').removeClassName('show_error');
		}
	}

	return !error_found;
}