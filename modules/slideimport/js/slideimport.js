/**
 * SLIDEIMPORT MODULE.
 *
 * @package        slideimport module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           slideimport
 * @version		   0.1
 */
var tree;
var previewSettings = null;

function initDoc(userId, uploadSessionVar)
{
	tree = $j('#courseTree');
	tree.css ('display','none');

	Dropzone.autoDiscover = false;
	$j('#slideImportDZ').addClass('dropzone');

	// import checkbox onchange event
	$j('#previewContainer').on('change', 'input[type="checkbox"]', function() {
		$j(this).parents('.column').first().find('img.preview').toggleClass("selected");

		// enable or disable the proceed button
		if ($j('input:checkbox:checked','#previewContainer').length>0) {
			$j('button.proceed','#previewBox').removeClass('disabled');
		} else {
			$j('button.proceed','#previewBox').addClass('disabled');
		}
	});

	// preview image container click
	$j('#previewContainer').on('click', '.column .ui.segment', function() {
		$j(this).parents('.column').first().find('input[type="checkbox"]').trigger('click');
	});

	// selectall and deselect all buttons
	$j('button.selectall, button.deselectall','#previewBox').click(function() {
		var selectAll = $j(this).hasClass('selectall');
		$j('input[type="checkbox"]','#previewContainer').each(function() {
			if ((selectAll && !$j(this).is(':checked')) || (!selectAll && $j(this).is(':checked'))) {
				$j(this).trigger('click');
			}
		});
	});

	// proceed button
	$j('button.proceed','#previewBox').click(function() {
		if (!$j(this).hasClass('disabled')) {
			$j('#previewBox').fadeOut('slow',function() {
				activateStep(3);
				$j('#selectCourseContainer').fadeIn('slow');
			});
		}
	});

	// select course change
	$j('#courseSelect').change(function() {
		var courseID = $j(this).val();

		if ('undefined' != typeof courseID && parseInt(courseID)>0) {
			courseID = parseInt(courseID);
			$j('#selCourse').text(courseID);
			$j('#selNode').text(courseID + '_0');

			$j('#whereImport').dimmer('toggle');
			// loads the treeview..
			tree.tree('loadDataFromUrl', 'ajax/getNodeList.php?courseID=' + courseID,
					null, function() {
				var rootNode = tree.tree('getNodeById', courseID + "_0");
				tree.tree('selectNode', rootNode);
				$j('#whereImport').dimmer('toggle');
				tree.show();
			});
		}
	});

	new Dropzone('#slideImportDZ', {
		paramName : 'uploaded_file',
		maxFiles: 1,
		url : 'ajax/upload.php' + '?userId='+userId +'&sessionVar='+uploadSessionVar,
		init: function() {
			var that = this;
			this.on("error", function(file, message) {
				$j.when(showHideDiv("Error", message, false)).then(function() {
					that.removeFile(file);
				});
			});

			this.on("success", function(file, responseObject) {
				var that = this;
				$j('#slideImportContainer').fadeOut('slow', function() {

					$j.ajax({
						type	:	'GET',
						url		:	'ajax/toPDF.php',
						data	:	{ userId : userId, sessionVar: uploadSessionVar, isPdf : (responseObject.isPdf ? 1 : 0) },
						dataType:	'json',
						beforeSend: function() {
							$j('#importLoader').fadeIn('slow');
						}
					})
					.done(function(JSONObj){
						if (JSONObj) {
							if (JSONObj.status=='OK') {
								$j('#importLoader').fadeOut('slow', function() {
									$j('#importLoader').remove();
									$j('#slideImportContainer').remove();
									previewSettings = JSONObj.data;
									displayPreview();
								});
							} else {
								$j.when(showHideDiv(JSONObj.title, JSONObj.msg, false)).then(function() {
									$j('#importLoader').fadeOut('slow',function() {
										$j('#slideImportDZ').fadeIn();
									})
									that.removeFile(file);
								});
							}
						} else {
							alert("Unknown error");
							location.reload();
						}
					})
					.fail(function() {
						alert("Unknown error");
						location.reload();
					});
				});
			});
		}
	});

	// objects initialization
	$j('#courseSelect').dropdown();
	$j('.ui.radio.checkbox','#selectCourseContainer').checkbox();
	// tree must be built before triggering courseSelect change
	tree.tree({ data : [], useContextMenu : false, autoOpen : 0 });
	$j('#courseSelect').trigger('change');

	// bind function to tree select action
	tree.bind('tree.select',function(event) {
    	if (event.node) {
            // node was selected
            $j('#selNode').text(event.node.id);
            if ($j('button','#importToNode').hasClass('disabled')) $j('button','#importToNode').removeClass('disabled');
        }
        else {
            // event.node is null, a node was deselected
            // e.previous_node contains the deselected node
        	$j('#selNode').text('');
        	if (!$j('button','#importToNode').hasClass('disabled')) $j('button','#importToNode').addClass('disabled');
        }
    });
	// activate step one
	activateStep(1);
}

function doImport(asNewCourse) {
	var error = true;
	var asSlideShow = parseInt($j('input[name="importSlideshow"]:radio:checked').val()) == 1;
	var selectedPages = $j('input[name="selectedPages[]"]:checked').map(function(){
	        return this.value;
	    }).get();
	var selNode = $j('#selNode').text().trim();
	var courseName;

	if (selectedPages.length>0) {
		// should not be here if no page has been selected
		if (asNewCourse) {
			// import as new course
			courseName = $j('#newCourseName').val().trim();
			if ('undefined' != typeof courseName && courseName.length>0) {
				error = false;
			} else {
				showHideDiv($j('#errortitle').html(), $j('#emptycoursename').html(), false);
				return false;
			}
		} else {
			// no new course
			if (selNode.length>0) {
				error = false;
			} else {
				showHideDiv($j('#errortitle').html(), $j('#nonodeselected').html(), false);
				return false;
			}
		}

		if (!error) {
			var msg = "Importerai le pagine " + selectedPages.join(', ');

			if (asSlideShow) {
				msg += " creando uno slideshow ";
			} else {
				msg += " creando un nodo per immagine ";
			}

			if (asNewCourse) {
				msg += " come nuovo corso dal titolo " + courseName;
			} else {
				msg += " come nuovo nodo, figlio di " + selNode;
			}
			activateStep(4);
			alert (msg);
		}
	} else {
		console.log ("No page selected, you should not be here!");
	}

}

function displayPreview() {
	/**
	 * gloval var previewSettings is going to be something like:
	 *
	 * numPages: 2
	 * orientation: "landscape"
	 * url: "http://www.localada.com/upload_file/uploaded_files/2/1453297024_Keyboard_shortcuts__3.0_.pdf"
	 *
	 */
	if ('undefined' != previewSettings.numPages) {
		$j('#previewBox').hide();
		var initLazyLoad = false;
		if ($j('div.column','#previewContainer').length<=0) {
			// populate previewContainer
			for (i=1; i<=previewSettings.numPages; i++) {
				$j('#previewContainer').append(generatePreviewItem(previewSettings.url,i, previewSettings.orientation).html());
			}
			initLazyLoad = true;
		}
		activateStep(2);
		$j('#previewBox').fadeIn('slow', function() {
			if (initLazyLoad) {
				// initialize image lazy loading
				$j("#previewContainer img.preview").lazyload({
					effect : "fadeIn"
				});
			}
		});
	}
}

function generatePreviewItem(url,pageNumber,orientation) {
	var template = $j('#previewPageTemplate').clone();
	template.find('div.column').attr('id','previewPage-'+pageNumber);
	template.find('span.pagenumber').html(pageNumber);
	template.find('img.preview').addClass(orientation).attr('data-original','ajax/getImage.php?url='+url+'&pageNum='+pageNumber)
	template.find('input[type="checkbox"]').attr('value',pageNumber);
	return template;
}

function gotoStep(stepNumber) {
	switch (stepNumber) {
	case 2:
			$j()
		break;
	default:
		break;

	}
}

function activateStep(stepNumber) {
	$j(document.body).scrollTop(0);
	$j('.ui.stepcontainer .ui.step.active').removeClass('active');
	$j('.ui.stepcontainer .ui.step[data-step="'+stepNumber+'"]').addClass('active');
}
