/**
 * LOGIN MODULE - config page for login provider
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

var ENABLEDCELLCLASS = 'enabledstate';
var ENABLEDBUTTONCLASS = 'enableButton';
var DISABLEDBUTTONCLASS = 'disableButton';
var configDataTable = null;
var configProvider = null;


function initDoc(providerClassName) {
	configProvider = providerClassName;
	initToolTips();
	initButtons();
	configDataTable = initDataTables();
}

function initDataTables() {
	return $j('#complete'+configProvider.toUpperCase()+'List').dataTable( {
		 		"bJQueryUI": true,
                "bFilter": true,
                "bInfo": true,
                "bSort": false,
                "bAutoWidth": true,
                "bPaginate" : true,
                "aoColumns": [
                                { "sWidth": "30%"},
                                { "sWidth": "20%"},
                                { "sWidth": "10%", "sClass":ENABLEDCELLCLASS },
                                { "bSearchable": false, "bSortable": false, "sClass":"actions", "sWidth": "11%"}
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
						// hide move up button from actions of first row
						$j(this).find('td.actions').first().find('button.upButton').hide();
						// hide move down button from actions of last row						
						$j(this).find('td.actions').last().find('button.downButton').hide();
					}
	}).show();
}

function editOptionSet(option_id) {
	// ask the server for the edit optionset form of the passed providerClassName
	$j.ajax({
		type	:	'GET',
		url		:	'ajax/edit_optionset.php',
		data	:	{ option_id: option_id, providerClassName: configProvider },
		dataType:	'json'		
	})
	.done(function (JSONObj){
		if (JSONObj.status=='OK') {
			if (JSONObj.html && JSONObj.html.length>0) {
				// build the dialog
				var theDialog = $j('<div />').html(JSONObj.html).dialog( {
					title: JSONObj.dialogTitle,
					autoOpen: false,
					modal:true,
					resizable: false,
					width: '80%',
					show: {
						effect: "fade",
						easing: "easeInSine", 
						duration: 250
			        },
			        hide: {
						effect: "fade",
						easing: "easeOutSine", 
						duration: 250
			        }				
				});
				
				// get and hide the submit button				
				var submitButton = theDialog.find('input[type="submit"]');
				submitButton.hide();
				
				// dialog buttons array
				var dialogButtons = {};

				// confirm dialog button
				dialogButtons[i18n['confirm']] = function() {
					// get form (previously hidden) submit button onclick code
					var onClickDefaultAction = submitButton.attr('onclick');
					// execute it, to hava ADA's own form validator
					var okToSubmit = (onClickDefaultAction.length > 0) ? new Function(onClickDefaultAction)() : false;						
					// and if ok ajax-submit the form
					if (okToSubmit) {
						 // append on the fly an hidden field form for the providerClassName
						theDialog.find('form').append("<input type='hidden' name='providerClassName' value='"+configProvider+"'>");
						ajaxSubmitOptionSetForm(theDialog.find('form').serialize());
						theDialog.dialog('close');
					}
				};
				
				// cancel dialog button
				dialogButtons[i18n['cancel']] = function() {
					theDialog.dialog('close');
				};
				
				// set the defined buttons
				theDialog.dialog( "option", "buttons", dialogButtons );
				
				// on dialog close, destroy it
				theDialog.on('dialogclose', function( event, ui){
					if ($j('.tooltip').length>0) $j('.tooltip').blur();
					$j(this).dialog('destroy').remove();
				});
				
				// on dialog enter keypress, call the confirm click
				theDialog.keypress(function(e) {
					if(e.which == 13) {
						e.preventDefault();
						theDialog.dialog("option","buttons")[i18n['confirm']]();
					}
				});
				
				// eventually open the dialog
				theDialog.dialog('open');
			}
		} else {
			if (JSONObj.msg) showHideDiv('', JSONObj.msg, false);
		}
	})
	.fail(function () { showHideDiv('', 'Server Error', false) } );
}

function ajaxSubmitOptionSetForm(data) {
	// ask the server to save the optionset
	$j.ajax({
		type	:	'POST',
		url		:	'ajax/edit_optionset.php',
		data	:	data,
		dataType:	'json'
	})
	.done(function (JSONObj){
		if (JSONObj.status.length>0) {
			if ('undefined' == typeof JSONObj.msg) JSONObj.msg  = JSONObj.status;
			$j.when (showHideDiv('', JSONObj.msg, JSONObj.status=='OK')).then(function() {
				 self.document.location.reload();
			});
		}
	});
}

function deleteOptionSet(jqueryObj, option_id, message) {
	if ($j('.tooltip').length>0) $j('.tooltip').blur();
	// the trick below should emulate php's urldecode behaviour
	if (confirm ( decodeURIComponent((message + '').replace(/\+/g, '%20')) ))
	{
		$j.ajax({
			type	:	'POST',
			url		:	'ajax/delete_optionset.php',
			data	:	{ option_id: option_id },
			dataType:	'json'
		})
		.done  (function (JSONObj) {
			if (JSONObj) {
					if (JSONObj.status=='OK') {
						// deletes the corresponding row from the DOM with a fadeout effect
						showHideDiv('', JSONObj.msg, true);
						jqueryObj.parents("tr").fadeOut("slow", function () {
							var pos = configDataTable.fnGetPosition(this);
							configDataTable.fnDeleteRow(pos);
						});							
					} else {
						showHideDiv('', JSONObj.msg, false);
					}
			}
		});
	}
}

function setEnabledOptionSet(jqueryObj, option_id, newstatus) {
	$j.ajax({
		type	:	'POST',
		url		:	'ajax/setenabled_optionset.php',
		data	:	{ option_id: option_id, status: (newstatus ? 1 :0) },
		beforeSend : function() {
			if ($j('.tooltip').length>0) {
				jqueryObj.tooltip('destroy');
			}
			jqueryObj.blur();
			jqueryObj.parents('td').find('button').button('disable');
			jqueryObj.parents('tr').children('.'+ENABLEDCELLCLASS).toggleClass('disabled');
		},
		dataType:	'json'
	})
	.fail (function() { jqueryObj.parents('td').find('button').button('enable'); initToolTips(); })
	.done  (function (JSONObj) {
		if (JSONObj) {
				if (JSONObj.status=='OK') {
					// get the cell where the statustext is, by the ENABLEDCELLCLASS
					var position = configDataTable.fnGetPosition( jqueryObj.parents('tr').children('.'+ENABLEDCELLCLASS)[0] );
					// update data with the statusText and no redraw yet
					configDataTable.fnUpdate (JSONObj.statusText, position[0], position[2], false);
					
					// get the cell where the buttons are: it contains the clicked button
					position = configDataTable.fnGetPosition( jqueryObj.parents('td')[0] );
					// get the contents
					var cellContent = configDataTable.fnGetData( jqueryObj.parents('td')[0] );
					// clone it around a div
					var newObj = $j('<div>').append($j(cellContent).clone());
					// search for the old button class
					var classToFind = newstatus ? ENABLEDBUTTONCLASS :  DISABLEDBUTTONCLASS ;
					/**
					 * on the cloned object
					 * 1. set its onclick to the new value
					 * 2. toggle enabled and disabled button class
					 * 3. set the new title
					 * 4. remove any old span needed by the jquery UI button
					 */
					newObj.find('.'+classToFind).					
						attr('onclick','setEnabledOptionSet($j(this), '+option_id+', '+(newstatus ? 'false' : 'true')+');').					
						toggleClass(ENABLEDBUTTONCLASS).
						toggleClass(DISABLEDBUTTONCLASS).					
						attr('title', JSONObj.buttonTitle).children('span').remove();
					// update new cell in the data of the table an redraw					
					configDataTable.fnUpdate (newObj.html(), position[0], position[2], true);
					
					// remove disabled class from cell with highlight effect
					var row = configDataTable.fnGetNodes(position[0]);
					$j(row).find('td.disabled.'+ENABLEDCELLCLASS).removeClass('disabled').effect("highlight", {}, 2000);
					
				} else {
					showHideDiv('', JSONObj.msg, false);
				}
				initButtons();
				initToolTips();
		}
	});
}

function moveOptionSet(jqueryObj, option_id, delta) {
	if ($j('.tooltip').length>0) $j('.tooltip').blur();
	
	$j.ajax({
		type	:	'POST',
		url		:	'ajax/move_optionset.php',
		data	:	{ option_id: option_id, delta: delta },
		beforeSend : function() {
			if ($j('.tooltip').length>0) {
				jqueryObj.tooltip('destroy');
			}
			jqueryObj.blur();
			jqueryObj.parents('td').find('button').button('disable');
		},
		dataType:	'json'
	})
	.fail (function() { jqueryObj.parents('td').find('button').button('enable'); initToolTips(); })
	.done  (function (JSONObj) {
		if (JSONObj) {
				if (JSONObj.status=='OK') {
				    var index = jqueryObj.parents('tr').index();

				    // moves the row up or down by updating the
					// data array of the table an then redraw it
				    if ('undefined' != typeof index && configDataTable!=null) {
				    	if ((index+delta) >= 0) {
				    		var data = configDataTable.fnGetData();
				    		configDataTable.fnClearTable();
				    		data.splice((index+delta), 0, data.splice(index,1)[0]);
				    		configDataTable.fnAddData(data);
				    	}    	    	
				    }
					$j(configDataTable.fnGetNodes(index+delta)).effect("highlight", {}, 1000);
					initButtons();
				} else {
					showHideDiv('', JSONObj.msg, false);
					jqueryObj.parents('td').find('button').button('enable');
				}
				initToolTips();
		}
	});   
}