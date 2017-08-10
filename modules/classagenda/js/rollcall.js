/**
 * CLASSAGENDA MODULE.
 *
 * @package        classagenda module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           classagenda
 * @version		   0.1
 */

function initDoc() {
	
	var commonDataTableOptions = {
	        "bFilter": true,
	        "bInfo": true,
	        "bSort": true,
	        "bAutoWidth": true,
	        "bPaginate" : true,	        
	        "oLanguage": {
	        	"sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
	        }
		};
	
	
	if ($j('#rollcallTable').length>0) {
		var tableOptions = $j.extend(commonDataTableOptions,{
			 "aoColumns": [
		                      { "bVisible": false },
		                      { "sWidth": "20%" },
		                      { "sWidth": "20%" },
		                      { "sWidth": "20%" },
		                      { "sWidth": "30%" },
		                      { "bSearchable": false, "bSortable": false, "sWidth": "10%" }
		        ],
		        "aaSorting": [[ 2, "asc" ]]
		});
		
		$j('#rollcallTable').dataTable(tableOptions).show();
		initButtons();
	} else if ($j('#rollcallHistoryTable').length>0) {
		var tableOptions = $j.extend(commonDataTableOptions,{
//			 "bSort" : false
		});
		$j('#rollcallHistoryTable').dataTable(tableOptions).show();
	}
}

function toggleStudentEnterExit (jQueryObj, id_student, classagenda_calendars_id, isEntering) {
	
	$j.ajax({
		type	:	'POST',
		url		:	'ajax/toggleStudentEnterExit.php',
		data	:	{ id_student: id_student,
					  classagenda_calendars_id: classagenda_calendars_id,
					  isEntering : isEntering ? 1 :0 },
		dataType:	'html'
	}).done (function(htmlcode){
		if (htmlcode && htmlcode.length>0) { 
			$j('#'+id_student+'_details').html($j('#'+id_student+'_details').html()+htmlcode);
		};
	}).always(function() {
		jQueryObj.hide();
		var className = (isEntering) ? '.exitbutton' : '.enterbutton' ; 
		var visibleButton = jQueryObj.parents('div').first().find(className).first();
		visibleButton.button( "option", "disabled", true );
		visibleButton.show();
		/**
		 * enable visible button after 30 seconds to avoid
		 * enter/exit toggle by accidental double click
		 */
		window.setTimeout (function(){ visibleButton.button( "option", "disabled", false ); }, 30*1000);
	});
}

function initButtons() {
	/**
	 * enter button
	 */
	$j('.enterbutton').button({
		icons : {
			primary : 'ui-icon-circle-arrow-e'
		}
	});
	/**
	 * exit button
	 */
	$j('.exitbutton').button({
		icons : {
			primary : 'ui-icon-circle-arrow-w'
		}
	});
}
