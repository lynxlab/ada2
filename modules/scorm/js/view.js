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

function initDoc(isError)
{
	if (isError) {
		$j('#errorMessage').show();
		$j('#SCOListcontainer').remove();
	} else {
		$j('#SCOListcontainer').show();
		$j('#errorMessage').remove();
	}

}
