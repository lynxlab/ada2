function initDoc(options) {
    // init DataTable
    if ('datatables' in options && options.datatables.length > 0) {
        options.datatables.map((datatableID) => {
            $j('#' + datatableID).DataTable({
                'oLanguage':
                {
                    'sUrl': HTTP_ROOT_DIR + '/js/include/jquery/dataTables/dataTablesLang.php'
                }
            });
        });
    }

    $j('.subscribe-group').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if ('loadModuleJS' in options && options.loadModuleJS.length > 0) {
            options.loadModuleJS.map((params) => {
                const nsClass = params.className.split(".");
                const c = new window[nsClass[0]][nsClass[1]](params.baseUrl);
                c.setCourseId($j(this).data('courseid'));
                c.setInstanceId($j(this).data('instanceid'));
                c.subscribeGroup();
            });
        }

    });
}

/**
 * shows and after 500ms removes the div to give feedback to the user about
 * the status of the executed operation (if it's been saved, delete or who knows what..)
 *
 * @param  title title to be displayed
 * @param  message message to the user
 * @return jQuery promise
 */
function showHideDiv(title, message, isOK) {
	var errorClass = (!isOK) ? ' error' : '';
	var content = "<div id='ADAJAX' class='saveResults popup" + errorClass + "'>";
	if (title.length > 0) content += "<p class='title'>" + title + "</p>";
	if (message.length > 0) content += "<p class='message'>" + message + "</p>";
	content += "</div>";
	var theDiv = $j(content);
	theDiv.css("position", "fixed");
	theDiv.css("z-index", 9000);
	theDiv.css("width", "350px");
	theDiv.css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2));
	theDiv.css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2));
	theDiv.hide().appendTo('body').fadeIn(500).delay(2000);
	var thePromise = theDiv.fadeOut(500);
	$j.when(thePromise).done(function () { theDiv.remove(); });
	return thePromise;
}
