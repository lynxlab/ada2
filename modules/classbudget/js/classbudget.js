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
		var timeInMillis = $j('#'+theID).find('td.totaltime').data('totaltime-millis');
		var newTotal = (timeInMillis / 3600) * newVal;
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
	$j('#'+tableID).find('th.grandtotal').data('grandtotal',grandTotal);
	$j('#'+tableID).find('th.grandtotal').html(grandTotal.format(ADA_CURRENCY_DECIMALS,3,ADA_CURRENCY_THOUSANDS_SEP,ADA_CURRENCY_DECIMAL_POINT));
	
}

/**
 * saves all kinds of budgets
 */
function saveBudgets() {
	
	var budgetsToSave = [ 'classroom', 'tutor' ];
	
	for (var i=0; i<budgetsToSave.length; i++) {
		if ($j('table#'+budgetsToSave[i]+'BudgetTable').length>0) {
			ajaxSaveBudget(budgetsToSave[i], budgetsToSave.length==(i+1));
		}
	}
}

function ajaxSaveBudget (type, isLast) {
	var elementID = 'table#'+type+'BudgetTable';
	var idCourseInstance = $j(elementID).data('instance-id');
	var sendData = [];
	
	$j(elementID).find('tbody tr').each(function() {
		var ID = parseInt($j(this).attr('id').replace( /^\D+/g, ''));
		var hourly_rate = parseFloat($j(this).find('#'+type+'_hourly_rate\\['+ID+'\\]').val());
		var costTypeID = ('undefined' != typeof $j(this).data('cost-'+type+'-id')) ? $j(this).data('cost-'+type+'-id') : null;
		sendData.push ({
			cost_type_id: costTypeID,
			id_type: ID,
			id_istanza_corso: idCourseInstance,
			hourly_rate: hourly_rate
		});
	});
	
	return $j.ajax({
		type	:	'POST',
		url		:	'ajax/saveBudget.php',
		data	:	{ data :sendData, type: type },
		dataType:	'json'
	}).done (function(JSONObj){
		if (JSONObj && 'undefined' != typeof JSONObj.status) {
			$j.when(showHideDiv('', JSONObj.msg, JSONObj.status=='OK')).then(function(){
				if (isLast && 'undefined' != JSONObj.callback) {
					var callback = new Function (JSONObj.callback);
					callback();
				}
			});
		}
	});
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
}
