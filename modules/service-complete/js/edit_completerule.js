/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2013, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version		   0.1
 */

var rowTemplate;

var OPERATIONROWID = 'operationRow_';
var OPERATIONSTABLEID = 'operationsTable';
var ROWTEMPLATEID = 'rowTemplate';



function initDoc()
{
	initButtons();
	initToolTips();
	initSelectConditionChangeHandler();
	
	// get the row template for 'addOperationRow' function
	if ($j('#'+ROWTEMPLATEID).length > 0) {
		// the rowTemplate was htmlentities encode, must decode it
		// with this trick:
		rowTemplate = $j('<div/>').html($j('#'+ROWTEMPLATEID).html()).text();
		// can now remove #rowTemplate from the DOM
		$j('#'+ROWTEMPLATEID).remove();
	} else {
		 rowTemplate = null;
	}
	
	$j('#completerules').on('submit', function(event) 
			{
				// do standard submit if we don't want ajax call
				// else proceed with ajax
				if (!isAjax)
				{
					return true;
				}
				else {
					event.preventDefault();
					var postData = $j(this).serialize();
					postData += '&requestType=ajax';
					
					$j.ajax({
						type	: 'POST',
						url		: HTTP_ROOT_DIR+ '/modules/service-complete/edit_completerule.php',
						data	: postData,
						dataType:'json',
					})
					.done(function (JSONObj) {
	                    if (JSONObj)
                        {
	                    	// note: the saved id is in the OK var
	                    	if (parseInt(JSONObj.OK)!=0) $j('#conditionSetId').val(JSONObj.OK);
	                    	showHideDiv(JSONObj.title ,JSONObj.msg, (parseInt(JSONObj.OK)!=0));
                        }
					} );
					return false;					
				}
			}
	);
}

function addOperationRow() {
	// if there's no row template, do nothing :(
	if (rowTemplate!=null)
	{
		// the max id is the last table row	
		var lastID = $j("#"+OPERATIONSTABLEID+" > tbody > tr[id^='"+OPERATIONROWID+"']").last().attr('id');
		var intLastID = parseInt(lastID.replace(/^\D+/g, ''));
	
		var newHTML = rowTemplate.replace (/#NEWID#/g, (intLastID+1));
		
		$j('#'+OPERATIONSTABLEID+' > tbody > tr').eq(intLastID).after(newHTML);
		initSelectConditionChangeHandler();
		$j('#'+OPERATIONROWID+(intLastID+1)).effect("highlight", { color: "#4ca456" }, 1000);
	}
}

function initSelectConditionChangeHandler()
{
	// reset the handler, if any
	$j('.selectCondition').off ('change');
	// set the (..sort of..) new handler
	$j('.selectCondition').on ('change', function() {
		if ($j(this).val().indexOf('null') != -1) {
			// the name of the input field to reset is the same
			// as the select element with 'condition' replaced with 'param'
			var targetInputName = $j(this).closest('select').attr('name').replace(/condition/g,'param');
			$j("input[name='"+targetInputName+"']").val('');
		}
	});	
}

function initButtons()
{
	/**
	 * submit button
	 */
	$j('#submitButton').button({
		icons : {
			primary : 'ui-icon-disk'
		}
	});
	
	/**
	 * add row button
	 */
	$j('#addRowButton').button({
		icons : {
			primary : 'ui-icon-plusthick'
		}
	});
}