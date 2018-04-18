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
		"className": "dt-body-right",
		"width": "10%"
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

	$j('table#list_requests').on('click', 'button[data-requestuuid]', function() {
		return handleRequest(tableObj, $j(this), $j.extend({}, $j(this).data(), {debug: debug ? 1 :0}));
	});
}

function handleRequest(tableObj, buttonObj, objData) {
	if (!buttonObj.hasClass('disabled')) {
		var isclose = objData.isclose || false,
			debug = objData.debug || false,
	    	url = "ajax/handleRequest.php",
	    	showHidePromise, 
	    	reloadCallback = null,
	    	clickedIndex = tableObj.cell(buttonObj.parents('td').first()).index(),
	    	reloadData = true,
	    	confirmhandle = objData.confirmhandle || false,
	    	confirmModalID = '#confirmModal',
	    	requesttype = objData.requesttype || false;

		var ajaxHandleRequest = function() {
		    return $j.ajax({
		    	type: "POST",
		    	url: url,
		    	data: objData,
		    	beforeSend: function() {
		    		buttonObj.addClass('disabled');
		    	}
		    })
		    .done(function(response) {
		    	if (debug) console.log('done callback got ', response);
		    	reloadData = 'reloaddata' in response && response.reloaddata;
		    	if ('redirecturl' in response && response.redirecturl.trim().length>0) {
		    		// if response has a redirect, obey at once!
		    		document.location.href = response.redirecturl.trim();
		    	}
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
		    	if (reloadData) {
		    		$j.when(showHidePromise).then(function(){
		    			// reload data, will enable the disabled button
		    			tableObj.ajax.reload(reloadCallback, false);
		    		});
		    	}
		    });
		};

		if (confirmhandle) {
			$j('.confirmText', confirmModalID).hide();
			$j('.confirmText[data-requesttype='+requesttype+']', confirmModalID).show();
			semanticConfirm(confirmModalID, { onApprove: ajaxHandleRequest } );
		} else ajaxHandleRequest();

	}
}
