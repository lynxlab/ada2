/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

function initDoc(tableID, options) {

	$j.fn.dataTable.ext.errMode = 'throw';
	var showall = ('showall' in options) ? options.showall : false;
	var columns = [
		{ "data": "uuid",
			"width" : "20%",
			"className" : "uuid",
		    "createdCell": function (td, cellData, rowData, row, col) {
		        if (cellData.trim().length >0 && 'generatedBy' in rowData) {
		          $j(td).attr('title', rowData.generatedBy);
		        }
		      }
		},
		{ "data": "type.description",
			"width" : "10%",
			"createdCell" : function (td, cellData, rowData, row, col) {
				if ('undefined' !== typeof requestTypeClassNames[rowData.type.type]) {
					$j(td).addClass(requestTypeClassNames[rowData.type.type]);
				}
			}
		},
		{ "data": "generateDate",
			"className": "dt-body-center",
			"type": "date-euro",
			"width" : "15%"
		},
		{ "data": "closedDate",
			"className": "dt-body-center",
			"type": "date-euro",
			"width" : "15%",
		    "render": function ( data, type, row, meta ) {
		    	return (null === data) ? '' : data ;
		    }
		},
		{ "data": "content",
			"className" : "nl2br"
		}		
	];
	
	if (showall) columns.push({ "data": "actions",
		"orderable" : false,
		"searchable": false,
		"className": "dt-body-center",
		"width": "8%"
	});
	
	var requestTypeClassNames = [ '', 'access', 'edit', 'onhold', 'delete' ];
	
	$j('#'+tableID)
		.DataTable({
			"deferRender": true,
			"processing" : true,
			"autoWidth"  : false,
			"rowId": 'uuid',
			"searchDelay" : 500, //in millis
			"order": [[ 2, 'desc' ]],
			"language": {
				"url": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
			},
			"ajax" : {
				'type': 'GET',
				'url' : "ajax/getRequests.php",
				'data': options
			},
			"columns": columns
		});
}
