var tree;
/**
 * Init document
 */
function initDoc() {
	tree = $j('#courseTree');
	$j('.step2buttons').css('display','none');
	tree.css ('display','none');
}

/**
 * Peforms export step 2, preventing the displayed form to be submitted
 * @returns {Boolean} false
 */
function goToExportStepTwo() {

	var courseSelect = document.getElementById('course');
	var courseID = courseSelect.options[courseSelect.selectedIndex].value;

	if (courseID > 0) {

		$j('.exportFormStep1').effect('drop', function() {
			$j('.exportFormStep2').effect('slide');
		});

		$j('#selCourse').text(courseID);
		$j('#selNode').text(courseID + '_0');

		tree.tree({
			data : [],
			useContextMenu : false,
			autoOpen : 0
		});
		
		tree.tree('loadDataFromUrl', HTTP_ROOT_DIR
				+ '/modules/impexport/getNodeList.php?courseID=' + courseID,
				null, function() {					
					var rootNode = tree.tree('getNodeById', courseID + "_0");
					tree.tree('selectNode', rootNode);	
					tree.slideDown ('slow', function () {
						$j('#courseTreeLoading').hide( function() { $j('.step2buttons').effect('fade'); } );
							
					});
				});

		// bind 'tree.click' event
		tree.bind('tree.click', function(event) {
			// The clicked node is 'event.node'
			var node = event.node;
			$j('#selNode').text(node.id);
		});
	}

	return false;
}

/**
 * does the export by redirecting to the proper php file
 */
function doExport() {
	$j('.exportFormStep2').effect('drop', function() {
		$j('.exportFormStep3').effect('slide');
	});
	
	var selCourse = $j('#selCourse').text();
	var selNode = $j('#selNode').text();
	
	self.document.location.href = HTTP_ROOT_DIR + '/modules/impexport/doExport.php?selCourse='+selCourse+'&selNode='+selNode;
}