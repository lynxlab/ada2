var tree;
var exportMedia = false;
var exportSurvey = true;
/**
 * Init document
 */
function initDoc() {
	tree = $j('#courseTree');
	$j('.step2buttons').css('display','none');
	tree.css ('display','none');
	$j('div.inithidden',$j('#contentcontent .first')).hide();
	$j('div.initshown', $j('#contentcontent .first')).effect('slide');
}

/**
 * Peforms export step 2, preventing the displayed form to be submitted
 * @returns {Boolean} false
 */
function goToExportStepTwo(visibleStep) {

	visibleStep = visibleStep || '';

	var courseSelect = document.getElementById('course');
	var courseID = courseSelect.options[courseSelect.selectedIndex].value;
	var courseDescr = courseSelect.options[courseSelect.selectedIndex].innerText.split(' ').slice(1).join(' ');

	var mediaCheck = document.getElementsByName('nomedia');
	var surveyCheck = document.getElementsByName('nosurvey');

	for (var index in mediaCheck) if (typeof mediaCheck[index] === 'object') exportMedia = exportMedia || mediaCheck[index].checked ;
	for (var index in surveyCheck) if (typeof surveyCheck[index] === 'object') exportSurvey = exportSurvey && surveyCheck[index].checked ;

	if (courseID > 0) {

		if (visibleStep.length >0 && $j('.'+visibleStep).length>0) {
			$j('.'+visibleStep).effect('drop', function() {
				$j('.exportFormStep2').effect('slide');
			});
		} else {
			$j('.exportFormStep2').effect('slide');
		}

		$j('#selCourse').text(courseID);
		$j('#selNode').text(courseID + '_0');
		$j('#selCourseDescr').text(courseDescr);

		tree.tree('destroy');
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
 * Peforms export step 3, preventing the displayed form to be submitted
 * @returns {Boolean} false
 */
function goToExportStepThree() {
	var selNode = tree.tree('getSelectedNode');
	if (false !== selNode) {
		var title = $j('#exportoToRepoNodelbl').text();
		// ucfirst
		title = title[0].toUpperCase() + title.slice(1);
		title +=  ' ' + selNode.id.replace('_','-') + ': '+ selNode.name;
		$j('#repotitle', $j('.exportFormStep3')).val(title);

		var descr = $j('#exportoToRepoBaseDescr').text();
		// ucfirst
		descr = descr[0].toUpperCase() + descr.slice(1);
		descr += ' ' + $j('#selCourseDescr').text();
		$j('#repodescr', $j('.exportFormStep3')).val(descr);

		$j('.exportFormStep2').effect('drop', function() {
			$j('.exportFormStep3').effect('slide');
		});
	} else {
		alert ($j('#exportoToRepoMustSelect').text());
	}
	return false;
}

/**
 * does the export by redirecting to the proper php file
 */
function doExport(visibleStep) {

	var selCourse = $j('#selCourse').text();
	var selNode = $j('#selNode').text();
	var exportToRepo = $j('#exporttorepo').length>0 ? ($j('#exporttorepo').val()==='1') : false;

	if (exportToRepo) {
		var data = {
			selCourse: selCourse,
			selNode: selNode,
			repotitle: $j('#repotitle').val(),
			repodescr: $j('#repodescr').val(),
			exporttorepo: 1
		};
		if (exportMedia) data.exportMedia = 1;
		if (exportSurvey) data.exportSurvey = 1;
		$j.ajax({
			'type': 'POST',
			'url': HTTP_ROOT_DIR + '/modules/impexport/doExport.php',
			'cache' : false,
			'data': data,
			'beforeSend' : function() {
				$j('button, input[type="submit"], input[type="button"]').attr('disabled', 'disabled');
			}
		})
		.fail(function(response) {
			if ('responseJSON' in response) {
				var title = response.responseJSON.title;
				var message = response.responseJSON.message;
			} else {
				var title = 'Error';
				var message = response.status + ' - '+response.statusText;
			}
			$j.when(showHideDiv(title, message, false)).done(function(){
				$j('button, input[type="submit"], input[type="button"]').removeAttr('disabled');
			});
		})
		.done(function(response) {
			$j.when(showHideDiv(response.title, response.message, true)).done(function(){
				history.back();
			});
		});
	} else {
		$j('.'+visibleStep).effect('drop', function() {
			$j('.exportFormStepExport').effect('slide');
		});
		self.document.location.href = HTTP_ROOT_DIR + '/modules/impexport/doExport.php?selCourse='+selCourse+'&selNode='+selNode+
			(exportMedia ? '&exportMedia=1' : '') + (exportSurvey ? '&exportSurvey=1' : '');
	}
	return false;
}