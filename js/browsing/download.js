var fileSharingTable;

function initDoc()
{
	initDataTables();
	// initButtons();
}

function initDataTables() {
	fileSharingTable = $j('#file_sharing_table').dataTable( {
		'bLengthChange': false,
        'bFilter': true,
        'bInfo': false,
        'bSort': true,
        'bAutoWidth': true,
		'bDeferRender': true,
		'bPaginate': false,
		"order": [[ 2, 'desc' ], [ 0, 'asc' ]],
		"aoColumnDefs": [
			{ "aTargets": [2], "sType": "date-eu" },
			{ "aTargets": [-1], "sortable": false },
		],
        "oLanguage": {
            "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
         }
	});
	fileSharingTable.show();
}

function initButtons() {
	$j('.deleteButton').button({
		icons : {
			primary : 'ui-icon-trash'
		},
		text : false
	});
}

function deleteFile(confirmQuestion, fileName, rowID) {
	if (confirm (decodeURIComponent(confirmQuestion)))
	{
		$j.ajax({
			type	:	'POST',
			url		:	HTTP_ROOT_DIR+ '/browsing/ajax/delete_uploadedFile.php',
			data	:	{ fileName : decodeURIComponent(fileName) },
			dataType:	'json'
		})
		.done  (function (JSONObj) {
			if (JSONObj)
				{
					if (JSONObj.status=='OK')
					{
						$j('#'+rowID).fadeOut(600, function () {
							// delete the row using dataTables methods
							var pos = fileSharingTable.fnGetPosition(this);
							fileSharingTable.fnDeleteRow(pos);
							showHideDiv(JSONObj.title ,JSONObj.msg); } );
					} else {
						showHideDiv(JSONObj.title ,JSONObj.msg);
					}
				}
		});
	}
}

/**
 * shows and after 500ms removes the div to give feedback to the user about
 * the status of the executed operation (if it's been saved, delete or who knows what..)
 *
 * @param title title to be displayed
 * @param message message to the user
 */
function showHideDiv ( title, message, reload )
{
	var theDiv = $j("<div id='ADAJAX' class='saveResults'><p class='title'>"+title+"</p><p class='message'>"+message+"</p></div>");
	theDiv.css("position","fixed");
	theDiv.css("width", "350px");
	theDiv.css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2));
	theDiv.css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2));
	theDiv.hide().appendTo('body').fadeIn(500).delay(2000).fadeOut(500, function() {
		theDiv.remove();
		if (typeof reload != 'undefined' && reload) self.location.reload(true); });
}

