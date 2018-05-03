/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

function initDoc(tableID, options) {

	var debug = false;
	$j.fn.dataTable.ext.errMode = 'throw';
	var canEdit = ('canEdit' in options) ? options.canEdit : false;

	var columns = [
		{ "data": "id",
		  "width" : "10%"
		},
		{ "data": "title",
		  "width" : "50%"
		},
		{ "data": "mandatory",
			"className": "dt-body-center",
			"width" : "5%",
		    "render": function ( data, type, row, meta ) {
			  var retStr = (true === data) ? '<i class="green large checked checkbox icon"></i>' : '<i class="red large empty checkbox icon"></i>' ;
			  return retStr + '<span style="display:none;">'+(true === data ? '1' : '0')+'</span>';
			}
		},
		{ "data": "lastEditTS",
		  "className": "dt-body-center",
		  "type": "date-euro",
		  "width" : "10%",
		  "render": function ( data, type, row, meta ) {
		    return (null === data) ? '' : data ;
		  }
		}
	];
	
	if (canEdit) {
		columns.push(
			{ "data": "actions",
			  "orderable" : false,
			  "searchable": false,
			  "className": "dt-body-right",
			  "width": "10%"
			}
		);
	}

	var tableObj = $j('#'+tableID)
		.DataTable({
			"deferRender": true,
			"processing" : true,
			"autoWidth"  : false,
			"rowId": 'id',
			"searchDelay" : 500, //in millis
			"order": [[ 3, 'desc' ]],
			"language": {
				"url": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
			},
			"ajax" : function(data, callback, settings) {
				$j.ajax({
					'type': 'GET',
					'url' : "ajax/getPolicies.php",
					'data': options
				})
				.done(function(response) {
					callback(response);
				})
				.fail(function(response) {
					if (debug && 'debug' in response.responseJSON) console.debug('dataTable ajax fail ', response.responseJSON);
						$j.when(showHideDiv("("+response.status+") " + response.statusText,
								('error' in response.responseJSON) ? response.responseJSON.error : 'unkown error',false))
						.then(function() { callback(response.responseJSON); });
					});
			},
			"columns": columns,
			"initComplete": function(settings, json) {
				if (canEdit) {
					$j('#newPolicyBTN').detach().insertBefore('.row.dt-table');
				} else {
					$j('#newPolicyBTN').remove();
				}
			}
		});
}
