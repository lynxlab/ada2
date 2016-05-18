/**
 * SCORM MODULE.
 *
 * @package        scorm module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           scorm
 * @version        0.1
 */

function initDoc(isError, SCOObject, SCOid, SCOversion)
{
	if (isError) {
		$j('#errorMessage').show();
		$j('#SCORMWIN, #SCORMAPI').remove();
	} else {
		$j('#SCORMWIN').show();
		$j('#errorMessage').remove();

		if (SCOversion=='1.2') {
			window.API = new scorm_API_12(SCOObject, SCOid);
		} else {
			window.API_1484_11 = new scorm_API_13(SCOObject, SCOid);
		}
		// all setup done, actually load iframe src
		$j('#SCORMWIN').attr('src',$j('#SCORMWIN').data('src'));
	}
}

function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}
