/**
 * IMPORT MODULE
 *
 * @package		export/import course
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		impexport
 * @version		0.1
 */

function initDoc() {
    $j('#loader').fadeOut('fast', function(){
        $j(this).remove();
        $j('#pagecontainer > *, #footer').animate({ opacity:1 },'fast');
	});
    var dataTable = initDataTable();

	function initDataTable() {
		var data = {
			what: 'Repository::List'
		};

		// check if it's an author importing from repository
		if($j('#repositoryList').data('importCourseId')) {
			data.id_course = $j('#repositoryList').data('importCourseId');
		}
		if($j('#repositoryList').data('importNodeId')) {
			data.id_node = $j('#repositoryList').data('importNodeId');
		}

		dataTable = $j('#repositoryList')
		.DataTable({
			"stateSave": true,
			"stateDuration": -1,
			"deferRender": true,
	//		"serverSide" : true,
			"processing" : true,
			"autoWidth"  : false,
			"rowId": 'id',
			"searchDelay" : 500, //in millis
			"order": [[ 3, 'desc' ], [ 1, 'asc' ]],
			"retrieve": true,
			"language": {
				"url": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
			},
			"ajax" :  {
                'type': 'GET',
                'url' : "ajax/getTableData.php",
                "data": function (d) {
                    return $j.extend({}, d, data);
                }
			},
			"columns": [
                { "data": "title" },
                { "data": "courseTitle", "visible": false },
                { "data": "description" },
                {
                    "data": "exportDateTime",
                    "className": "dt-body-center",
                    "type": "date-euro",
                    "width" : "8em"
                },
                { "data": "courseProvider" },
                {
                    "data": "actions",
                    "orderable" : false,
                    "searchable": false,
                    "className": "dt-body-center actions",
                    "width": "13em"
                }
            ],
            "rowGroup": {
                "dataSrc": 'courseTitle'
            }
		});
		return dataTable;
	} // initDataTable

	this.deleteRepoItem = function(element, id) {
		var theModal = $j('#deleteConfirm')
		.modal('setting', {
		  closable  : false,
		  onDeny    : function(){
			return true;
		  },
		  onApprove : function() {
            //   alert('Implement');
            //   theModal.modal('hide');
			$j.ajax({
				type: 'POST',
				url: 'ajax/deleteExport.php',
				data: { id: id },
				dataType: 'json',
				beforeSend: function() {
					theModal.modal('hide');
				}
			})
			.done(function(JSONObj) {
				if (JSONObj.status.length > 0) {
					$j.when(showHideDiv('', JSONObj.msg, JSONObj.status == 'OK')).then(function () {
						if (JSONObj.status == 'OK') dataTable.ajax.reload(null, false);
					});
				}
			})
			.fail(function() {
				showHideDiv('', $j('#unknownErrorMSG').html(), false);
			});
			return false;
		  }
		})
		.modal('show');
	}

} // initDoc


