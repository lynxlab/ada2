/**
 * CLASSROOM MODULE.
 *
 * @package			classroom module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classroom
 * @version			0.1
 */

function initDoc() {
	initToolTips();
	initButtons();
	initDataTables();
}

function initDataTables() {
	$j('#completeVenuesList').dataTable( {
		 		"bJQueryUI": true,
                "bFilter": true,
                "bInfo": true,
                "bSort": true,
                "bAutoWidth": true,
                "bPaginate" : true,
                "aoColumns": [
                                { "sWidth": "30%"},
                                { "sWidth": "20%"},
                                { "sWidth": "20%"},
                                { "sWidth": "20%"},
                                { "bSearchable": false, "bSortable": false, "sWidth": "10%"}
                ],
                "aaSorting": [[ 0, "asc" ]],
                "oLanguage": {
                	"sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
                },
				"fnDrawCallback":
					function () {
						// put the sort icon outside of the DataTables_sort_wrapper div
						// for better display styling with CSS
						$j(this).find("thead th div.DataTables_sort_wrapper").each(function(){
							sortIcon = $j(this).find('span').clone();
							$j(this).find('span').remove();
							$j(this).parents('th').append(sortIcon);
						});
					}
	}).show();
}

function deleteVenue(jqueryObj, id_venue, message) {
	// the trick below should emulate php's urldecode behaviour
	if (confirm ( decodeURIComponent((message + '').replace(/\+/g, '%20')) ))
	{
		$j.ajax({
			type	:	'POST',
			url		:	'ajax/delete_venue.php',
			data	:	{ id_venue: id_venue },
			dataType:	'json'
		})
		.done  (function (JSONObj) {
			if (JSONObj) {
					if (JSONObj.status=='OK') {
						// deletes the corresponding row from the DOM with a fadeout effect
						showHideDiv('', JSONObj.msg, true);
						jqueryObj.parents("tr").fadeOut("slow", function () {
							var pos = $j('#completeVenuesList').dataTable().fnGetPosition(this);
							$j('#completeVenuesList').dataTable().fnDeleteRow(pos);
						});							
					} else {
						showHideDiv('', JSONObj.msg, false);
					}
			}
		});
	}
}
