/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */
var clickHandlers = {};

function initDoc(tableID, options) {

	var debug = false;
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
		{ "data": "generatedDate",
			"className": "dt-body-center",
			"type": "date-euro",
			"width" : "10%"
		},
		{ "data": "closedDate",
			"className": "dt-body-center",
			"type": "date-euro",
			"width" : "10%",
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
	
	var tableObj = $j('#'+tableID)
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

	clickHandlers.closeRequest = function (buttonObj, requestUUID) {
		if (!buttonObj.hasClass('disabled')) {			
		    var url = "ajax/forceCloseRequest.php";
		    var showHidePromise;
		    var reloadCallback = null;
		    var clickedIndex = tableObj.cell(buttonObj.parents('td').first()).index();
		    return $j.ajax({
		    	type: "POST",
		    	url: url,
		    	data: { requestUUID: requestUUID, debug: debug ? 1 :0 },
		    	beforeSend: function() {
		    		buttonObj.addClass('disabled');
		    	}
		    })
		    .done(function(response) {
		    	if (debug) console.log('done callback got ', response);
		    	reloadCallback = function() {
		    		tableObj.row(clickedIndex.row).nodes().to$().addClass('yellow-highlight');
		    		setTimeout(function() {
		    			tableObj.row(clickedIndex.row).nodes().to$().removeClass('yellow-highlight');
		    		},5000);
		    	};
		    })
		    .fail(function(response) {
		    	if (debug) console.log('fail callback ', response);
		    	if ('responseJSON' in response) {

		    		if (debug) {
						console.groupCollapsed(url+' fail');
						if ('errorMessage' in response.responseJSON) {
							console.error('message: %s', response.responseJSON.errorMessage);
						}
						if ('errorTrace' in response.responseJSON) {
							console.error('stack trace %s', response.responseJSON.errorTrace);
						}
						console.groupEnd();
		    		}

		    		showHidePromise = showHideDiv(response.responseJSON.title, response.responseJSON.message, false);

		    	} else {
		    		var errorText = response.statusText;
		    		if ('responseText' in response && response.responseText.length>0) errorText += '<br/>'+response.responseText;
		    		showHidePromise = showHideDiv('Error ' + response.status, errorText, false);
		    	}	
		    })
		    .always(function(response) {
		    	if (debug) console.log('always callback');
		    	$j.when(showHidePromise).then(function(){
		    		// reload data, will enable the disabled button
		    		tableObj.ajax.reload(reloadCallback, false);
		    	});
		    });
		}
	};
}
