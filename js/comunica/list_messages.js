/**
 * 
 *
 * @package   comunica
 * @author    Giorgio <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2009-2014, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version   0.1
 */
function initDoc(displayedMsgs) {	
	$j(document).ready(function() {
		if (displayedMsgs=='sent' || displayedMsgs=='received')
		{
			var columns = null;
			var columnsDef = null;
			
			if (displayedMsgs=='received') {
				columns = [ null, { "sType": "date-euro" }, null, null, null, null, null ];
				columnsDef = [ { "bSortable": false , "aTargets": [ 4,5,6 ] } ];
			} else if (displayedMsgs=='sent') {
				columns = [ null, { "sType": "date-euro" }, null ];
			}
			
			$j('.default_table').dataTable( {
				"aaSorting": [[ 1, "desc" ]],
				"aoColumnDefs":  columnsDef,
				"aoColumns":     columns,
				"bLengthChange": false,
				"bFilter":       true,
				"bInfo":         true,
				"bPaginate":     true,
				"bSort":         true,
				"bAutoWidth":    true,
				"bDeferRender":  true,
				"iDisplayLength": 7,
				"oLanguage": {
	                "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
	            }
			}).show();
		}
	});
}
