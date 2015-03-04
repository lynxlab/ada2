/**
 * CLASSBUDGET MODULE.
 *
 * @package        classbudget module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2015, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           classbudget
 * @version		   0.1
 */

function initDoc() {
	initButtons();
}

/**
 * update total cell on hourly_cost input element change
 * 
 * @param inputEl
 */
function updateTotal(inputEl) {
	var newVal = parseFloat(trim($j(inputEl).val()));
	if (newVal<=0 || isNaN(newVal)) newVal = 0;
	$j(inputEl).val(newVal.toFixed(2));
	
	var theID = $j(inputEl).parents('tr').attr('id');
	if (theID.length>0) {
		var quantity = $j('#'+theID).find('td.totalqty').data('totalqty');
		var newTotal = parseFloat(quantity) * newVal;
		$j('#'+theID).find('td.total').data('total',newTotal);
		$j('#'+theID).find('td.total').html(newTotal.format(ADA_CURRENCY_DECIMALS,3,ADA_CURRENCY_THOUSANDS_SEP,ADA_CURRENCY_DECIMAL_POINT));
	}
	updateGrandTotal($j(inputEl).parents('table').attr('id'));
}

/**
 * update grandtotal cell into the passed tableID
 * 
 * @param tableID
 */
function updateGrandTotal(tableID) {
	var grandTotal = 0;
	$j('#'+tableID).find('tbody td.total').each(function() {
		grandTotal += parseFloat($j(this).data('total'));
	});	
	var oldTotal = parseFloat($j('#'+tableID).find('th.price.grandtotal').data('grandtotal'));
	$j('#'+tableID).find('th.price.grandtotal').data('grandtotal',grandTotal);
	$j('#'+tableID).find('th.price.grandtotal').html(grandTotal.format(ADA_CURRENCY_DECIMALS,3,ADA_CURRENCY_THOUSANDS_SEP,ADA_CURRENCY_DECIMAL_POINT));
	updateInstanceTotalCost(grandTotal-oldTotal);
}

function updateInstanceTotalCost(delta) {
	var instanceTotal = parseFloat($j('#instance-cost').data('instance-totalcost'));
	var instanceBudget = parseFloat($j('#instance-budget').data('instance-budget'));
	// update instance total cost
	instanceTotal += delta;
	$j('#instance-cost').data('instance-totalcost',instanceTotal);
	$j('#instance-cost').html(instanceTotal.format(ADA_CURRENCY_DECIMALS,3,ADA_CURRENCY_THOUSANDS_SEP,ADA_CURRENCY_DECIMAL_POINT));	
	// update instance balance
	var balance = instanceBudget - instanceTotal;
	var classbalance = (balance>=0) ? 'balancegreen' : 'balancered';
	$j('#instance-balance').data('instance-balance',balance);
	$j('#instance-balance').html(balance.format(ADA_CURRENCY_DECIMALS,3,ADA_CURRENCY_THOUSANDS_SEP,ADA_CURRENCY_DECIMAL_POINT));
	$j('#instance-balance').removeClass().addClass(classbalance);	
}

/**
 * saves all kinds of budgets
 */
function saveBudgets() {

	var budgetsToSave = [];
	var doCallBack = true;
	
	$j('table.classbudgettable').each(function(){
		  budgetsToSave.push ($j(this).attr('id').replace('BudgetTable',''));
		});
	
	for (var i=0; i<budgetsToSave.length; i++) {
		if ($j('table#'+budgetsToSave[i]+'BudgetTable').length>0) {
			ajaxSaveBudget(budgetsToSave[i], doCallBack && budgetsToSave.length==(i+1));
		}
	}
}

function ajaxSaveBudget (type, doCallBack) {
	var elementID = 'table#'+type+'BudgetTable';
	var idCourseInstance = $j(elementID).data('instance-id');
	var sendData = [];
	
	$j(elementID).find('tbody tr').each(function() {
		var ID = parseInt($j(this).attr('id').replace( /^\D+/g, '')); // id_classroom, id_tutor...
		var unitprice = parseFloat($j(this).find('#'+type+'_unitprice\\['+ID+'\\]').val());
		var costTypeID = ('undefined' != typeof $j(this).data('cost-'+type+'-id')) ? $j(this).data('cost-'+type+'-id') : null;
		if (type!='item') {
			sendData.push ({
				cost_type_id: costTypeID,
				id_type: ID,
				id_istanza_corso: idCourseInstance,
				hourly_rate: unitprice
			});			
		} else {
			var appliedTo = ('undefined' != typeof $j(this).data('applied-to-id')) ? $j(this).data('applied-to-id') : null;
			sendData.push ({
				cost_type_id: costTypeID,
				id_istanza_corso: idCourseInstance,
				price: unitprice,
				applied_to: appliedTo,
				description: $j(this).find('td.displayname').text()
			});
		}
	});
	
	return $j.ajax({
		type	:	'POST',
		url		:	'ajax/saveBudget.php',
		data	:	{ data :sendData, type: type },
		dataType:	'json'
	}).done (function(JSONObj){
		if (JSONObj && 'undefined' != typeof JSONObj.status) {
			if (doCallBack && 'undefined' != JSONObj.callback) {
				$j.when(showHideDiv('', JSONObj.msg, JSONObj.status=='OK')).then(function(){
					var callback = new Function (JSONObj.callback);
					callback();
				});
			}
		}
	});
}

function editCostItem(cost_item_id) {
	/*
	 * if displaying a new item form, get the course instance id
	 * and pass it to the ajax call endpoint for form valorization
	 */
	var idCourseInstance = null;
	if (cost_item_id == null && 'undefined' != typeof arguments[1]) {
		idCourseInstance = $j('table#'+arguments[1]+'BudgetTable').data('instance-id');
	}
	
	// ask the server for the edit cost item form
	$j.ajax({
		type	:	'GET',
		url		:	'ajax/edit_costitem.php',
		data	:	{ cost_item_id: cost_item_id,
					  course_instance_id: idCourseInstance },
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
						$j.when(ajaxSaveBudget('item', false)).then(function() {
							ajaxSubmitCostItemForm(theDialog.find('form').serialize());
						});
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
					$j(this).dialog('destroy').remove();
				});
				
				// on dialog enter keypress, call the confirm click
				theDialog.keypress(function(e) {
					if(e.which == 13) {
						e.preventDefault();
						theDialog.dialog("option","buttons")[i18n['confirm']]();
					}
				});
				
				// set autocomplete field behaviour with caching				
				theDialog.find('#description').autocomplete({
					minLength: 2,
					source: function( request, response ) {
						
						// term is already in the request,
						// add tableName, fieldName
						request = $j.extend ({
							tableName  : 'cost_item',
							fieldName  : 'description',
						}, request);

						$j.getJSON( 'ajax/autocomplete.php', request, function( data, status, xhr ) {
							response(data);
							});
					},
					focus: function(event,ui) { return false; },
					select: function(event, ui) {
						$j('#description').val(ui.item.label);
						return false;
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

function ajaxSubmitCostItemForm(data) {
	// ask the server to save the cost item
	$j.ajax({
		type	:	'POST',
		url		:	'ajax/edit_costitem.php',
		data	:	data,
		dataType:	'json'
	})
	.done(function (JSONObj){
		if (JSONObj.status.length>0) {
			$j.when (showHideDiv('', JSONObj.msg, JSONObj.status=='OK')).then(function() {
				if ('undefined' != JSONObj.html) {
					updateCostItemTable(JSONObj.html);
				} else {
					self.document.location.reload();
				}
			});
		}
	});
}

function deleteCostItem(jqueryObj, cost_item_id, message) {
	// the trick below should emulate php's urldecode behaviour
	if (confirm ( decodeURIComponent((message + '').replace(/\+/g, '%20')) ))
	{
		$j.ajax({
			type	:	'POST',
			url		:	'ajax/delete_costitem.php',
			data	:	{ cost_item_id: cost_item_id },
			dataType:	'json'
		})
		.done  (function (JSONObj) {
			if (JSONObj.status.length>0) {
				$j.when (showHideDiv('', JSONObj.msg, JSONObj.status=='OK')).then(function() {
					if ('undefined' != JSONObj.html) {
						updateCostItemTable(JSONObj.html);
					} else {
						self.document.location.reload();
					}
				});
			}
		});
	}
}

function updateCostItemTable(newTable) {
	var tableID = 'itemBudgetTable';
	var oldTotal = parseFloat($j('#'+tableID).find('th.price.grandtotal').data('grandtotal'));
	$j('#itemBudgetContainer').replaceWith(newTable);
	var newTotal = parseFloat($j('#'+tableID).find('th.price.grandtotal').data('grandtotal'));
	updateInstanceTotalCost(newTotal-oldTotal);
	initButtons();
}

/**
 * inits jquery buttons
 */
function initButtons()
{
	/**
	 * save button
	 */
	if ($j('.budgetsave').length>0) {
		$j('.budgetsave').button({
			icons : {
				primary : 'ui-icon-disk'
			}
		});
	}
	
	/**
	 * cancel button
	 */
	if ($j('.budgetcancel').length>0) {
		$j('.budgetcancel').button({
			icons : {
				primary : 'ui-icon-closethick'
			}
		});
	}
	
	/**
	 * new cost item button
	 */
	if ($j('.newCostItemButton')) {
		$j('.newCostItemButton').button({
			icons : {
				primary : 'ui-icon-document'
			}
		});
	}
	
	/**
	 * actions column / edit button
	 */
	if ($j('.editButton').length>0) {
		$j('.editButton').button({
			icons : {
				primary : 'ui-icon-pencil'
			},
			text : false
		});	
	}
	
	/**
	 * actions column / delete button
	 */
	if ($j('.deleteButton').length>0) {
		$j('.deleteButton').button({
			icons : {
				primary : 'ui-icon-trash'
			},
			text : false
		});	
	}
}
