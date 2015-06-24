/**
 * LOGIN MODULE - config page for ldap login provider
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


function initDoc() {
	initToolTips();
	initButtons();
	initDataTables();
}

function initDataTables() {
	$j('#completeLDAPList').dataTable( {
		 		"bJQueryUI": true,
                "bFilter": true,
                "bInfo": true,
                "bSort": false,
                "bAutoWidth": true,
                "bPaginate" : true,
                "aoColumns": [
                                { "sWidth": "30%"},
                                { "sWidth": "20%"},
                                { "sWidth": "5%", "sClass":ENABLEDCELLCLASS },
                                { "bSearchable": false, "bSortable": false, "sWidth": "8%"}
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

function editOptionSet(option_id, providerType) {
	if ('undefined' == typeof providerType) return false;
	
	// ask the server for the edit ldap form
	$j.ajax({
		type	:	'GET',
		url		:	'ajax/edit_'+providerType+'.php',
		data	:	{ option_id: option_id },
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
						ajaxSubmitOptionSetForm(theDialog.find('form').serialize(),providerType);
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

function ajaxSubmitOptionSetForm(data, providerType) {
	// ask the server to save the ldap config
	$j.ajax({
		type	:	'POST',
		url		:	'ajax/edit_'+providerType+'.php',
		data	:	data,
		dataType:	'json'
	})
	.done(function (JSONObj){
		if (JSONObj.status.length>0) {
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
							var pos = $j('#completeLDAPList').dataTable().fnGetPosition(this);
							$j('#completeLDAPList').dataTable().fnDeleteRow(pos);
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
			jqueryObj.button('disable');
			jqueryObj.removeAttr('title');
			jqueryObj.parents('tr').children('.'+ENABLEDCELLCLASS).toggleClass('disabled');
		},
		dataType:	'json'
	})
	.always (function() { jqueryObj.button('enable'); initToolTips(); })
	.done  (function (JSONObj) {
		if (JSONObj) {
				if (JSONObj.status=='OK') {
					// ajax must return button title and html text
					jqueryObj.attr('onclick','setEnabledOptionSet($j(this), '+option_id+', '+(newstatus ? 'false' : 'true')+');');
					jqueryObj.attr('title', JSONObj.buttonTitle);
					jqueryObj.toggleClass(ENABLEDBUTTONCLASS).toggleClass(DISABLEDBUTTONCLASS);
					initButtons();
					jqueryObj.parents('tr').children('.'+ENABLEDCELLCLASS).
						toggleClass('disabled').html(JSONObj.statusText).effect("highlight", {}, 2000);
				} else {
					showHideDiv('', JSONObj.msg, false);
				}
		}
	});
}