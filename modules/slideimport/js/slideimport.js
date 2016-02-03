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
var tree = null;
var previewSettings = null;

function initDoc(userId, userType, preselectedCourseID, uploadSessionVar)
{
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
	$j('#courseSelectInput').on('change','#courseSelect', function() {
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
	$j('.importsettings .form .ui.checkbox','#selectCourseContainer').checkbox();

	$j('#selectCourseContainer .importsettings .form .ui.checkbox').on('change','input[type="radio"]', function() {
		var askFrontPage = ('undefined' != typeof $j(this).data('askfrontpage')) && ($j(this).data('askfrontpage')) ;
		if (askFrontPage) {
			if (!$j('input[name="hasFrontPage"]','#selectCourseContainer .askfrontpage.field').is(':visible')) {
				$j('input[name="hasFrontPage"]','#selectCourseContainer .askfrontpage.field').parents('.askfrontpage.field').slideDown('fast');
			}
		} else {
			$j('input[name="hasFrontPage"]','#selectCourseContainer .askfrontpage.field').parents('.askfrontpage.field').slideUp('fast');
		}
	});

	$j.when(loadCourseSelect($j('#courseSelectInput'), preselectedCourseID)).then (function() {
		// init tree

		if (tree == null) {
			tree = $j('#courseTree');
			tree.css ('display','none');
		}

		// tree must be built before triggering courseSelect change
		tree.tree({ data : [], useContextMenu : false, autoOpen : 0 });

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

		$j('#courseSelect').trigger('change');
	});

	// set onunload handler to clean files and session
	$j(window).unload(function() {
		$j.ajax({
			type	:	'POST',
			url		:	'ajax/cleanSlideImport.php',
			data	:	{ sessionVar : uploadSessionVar }
		});
	});

	// set html elements depending on user type
	if (userType==AMA_TYPE_AUTHOR) {
		$j('#importToCourse').remove();
	} else if (userType==AMA_TYPE_SWITCHER) {
		$j('#importToNode').remove();
	}

	// activate step one
	activateStep(1);
}

function doImport(asNewCourse) {
	var context = '.ui.modal';
	$j.when(doAjaxImport(asNewCourse))
		.done(function (retObj) {
			$j('button.homepage', context).attr('onclick','self.document.location.href=\''+HTTP_ROOT_DIR+'/browsing\'');
			if (retObj && retObj.status === true) {
				if ('undefined' != typeof retObj.nodeId && retObj.nodeId) {
					$j('button.viewnodes', context).attr('onclick','self.document.location.href=\''+
							HTTP_ROOT_DIR+'/browsing/view.php?id_node='+retObj.nodeId+'\'').show();
				} else {
					$j('button.viewnodes', context).hide();
				}
				$j('.content span', context).stop().hide();
				$j('.actions button.showonerror', context).hide();
				$j('.actions button.showonok', context).show();
				$j('.content span#importcompleteok', context).show();
				if (tree!=null) $j('#courseSelect').trigger('change');
				showHideDiv($j('#infotitle').html(), $j('#importcompleteok').html(), true);
				$j('.actions', context).css({'visibility':'visible'});
			} else {
				$j('.actions button.showonok', context).hide();
				$j('.actions button.showonerror', context).show();
				$j('.actions', context).css({'visibility':'visible'});
			}
		})
		.fail(function() {
			$j('.actions button.showonok', context).hide();
			$j('.actions button.showonerror', context).show();
			$j('.content span#importcompleteerror', context).show();
			showHideDiv($j('#errortitle').html(), $j('#importcompleteerror').html(), false);
			$j('.actions', context).css({'visibility':'visible'});
		})
		.always(function() {
			$j("#progressbar").hide();
		});
}

function doAjaxImport(asNewCourse) {
	var deferred = $j.Deferred();

	var error = true;
	var asSlideShow = parseInt($j('input[name="importSlideshow"]:radio:checked').val()) >0;
	var withLinkedNodes = parseInt($j('input[name="importSlideshow"]:radio:checked').val()) ==2;
	var selectedPages = $j('input[name="selectedPages[]"]:checked').map(function(){
	        return this.value;
	    }).get();
	var hasFrontPage = $j('input[name="hasFrontPage"]').filter(':checked').filter(':visible').length >0;
	var selCourse = $j('#selCourse').text().trim();
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
			activateStep(4);
		    $j('.actions', '.ui.modal').css({'visibility':'hidden'});
			$j("#progressbar").progressbar({ value: false }).show();
			$j('.ui.modal').modal('setting', { closable: false }).modal('show');

			var courseID = null;

			$j.when(generateCourse(asNewCourse, courseName)).then(function(JSONObj){
				if (JSONObj && 'undefined' != typeof JSONObj.courseID && parseInt(JSONObj.courseID)>0) {
					courseID = parseInt(JSONObj.courseID);
					$j.when(generateImages(selectedPages, JSONObj.courseID)).then(function(JSONObj) {
						if (JSONObj && 'undefined' != typeof JSONObj.error && parseInt(JSONObj.error)==0) {
							var startNode = (asNewCourse) ? courseID + '_0' : selNode;
							$j.when(generateNodes(selectedPages, courseID, startNode, asNewCourse, asSlideShow, withLinkedNodes, hasFrontPage)).then(function(JSONObj) {
								if (JSONObj && 'undefined' != typeof JSONObj.status) {
									deferred.resolve(JSONObj);
								} else {
									$j('.ui.modal .content span').stop().hide();
									$j('.ui.modal .content span#generatenodeserror').show();
									showHideDiv($j('#errortitle').html(), $j('#generatenodedserror').html(), false);
									if (asNewCourse) {
										// TODO: remove generated course
									}
									deferred.resolve({status: false});
								}
							});
						} else {
							$j('.ui.modal .content span').stop().hide();
							$j('.ui.modal .content span#generateimageserror').show();
							showHideDiv($j('#errortitle').html(), $j('#generateimageserror').html(), false);
							if (asNewCourse) {
								// TODO: remove generated course
							}
							deferred.resolve({status: false});
						}
					});
				} else {
					$j('.ui.modal .content span').stop().hide();
					$j('.ui.modal .content span#newcourseerror').show();
					showHideDiv($j('#errortitle').html(), $j('#newcourseerror').html(), false);
					deferred.resolve({status: false});
				}
			});
		}
	} else {
		console.log ("No page selected, you should not be here!");
		deferred.reject();
	}

	return deferred.promise();

}

function generateCourse(makeNewCourse, courseName) {
	var deferred = $j.Deferred();

	if (makeNewCourse) {
		$j.ajax({
			type	:	'POST',
			url		:	'ajax/generateCourse.php',
			data	:	{ courseName : courseName },
			dataType:	'json',
			beforeSend: function() {
				$j('.content span', '.ui.modal').stop().hide();
				$j('.content span.step1', '.ui.modal').stop().show();
			}
		})
		.done(function(JSONObj) {
			loadCourseSelect($j('#courseSelectInput'), parseInt(JSONObj.courseID));
			deferred.resolve(JSONObj);
		})
		.fail(function() { deferred.reject(); });

		return deferred.promise();
	} else {
		return deferred.resolve ({ courseID : $j('#selCourse').text().trim() }).promise();
	}
}


function generateImages(selectedPages, courseID) {
	var deferred = $j.Deferred();

	$j.ajax({
		type	:	'GET',
		url		:	'ajax/generateImages.php',
		data	:	{ selectedPages : selectedPages, courseID : courseID, url : previewSettings.url },
		dataType:	'json',
		beforeSend: function() {
			$j('.content span', '.ui.modal').stop().hide();
			$j('.content span.step2', '.ui.modal').stop().show();
		}
	})
	.done(function(JSONObj) { deferred.resolve(JSONObj); })
	.fail(function() { deferred.reject(); });

	return deferred.promise();
}

function generateNodes(selectedPages, courseID, startNode, asNewCourse, asSlideShow, withLinkedNodes, hasFrontPage) {
	var deferred = $j.Deferred();
	$j.ajax({
		type	:	'POST',
		url		:	'ajax/generateNodes.php',
		data	:	{ selectedPages : selectedPages, courseID : courseID, startNode : startNode,
					  asNewCourse: (asNewCourse) ? 1 : 0, asSlideShow : (asSlideShow) ? 1 : 0,
					  withLinkedNodes : (withLinkedNodes) ? 1 : 0, hasFrontPage : (hasFrontPage) ? 1 : 0,
					  url : previewSettings.url },
		dataType:	'json',
		beforeSend: function() {
			$j('.content span', '.ui.modal').stop().hide();
			$j('.content span.step3', '.ui.modal').stop().show();
		}
	})
	.done(function(JSONObj) {
		deferred.resolve({
			status : (JSONObj && JSONObj.status=='OK') ? true : false,
			nodeId : (JSONObj && 'undefined' != typeof JSONObj.nodeId) ? JSONObj.nodeId : null
		});
	})
	.fail(function() { deferred.reject(); });

	return deferred.promise();
}


function displayPreview() {
	/**
	 * global var previewSettings is going to be something like:
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

function loadCourseSelect(jQueryObj, selectedID) {
	return $j.ajax({
		type	:	'GET',
		url		:	'ajax/getCourseSelect.php',
		data    :   { selectedID : selectedID },
		dataType:	'html',
	})
	.done(function(retHTML) {
		jQueryObj.html(retHTML);
		if ($j('#courseSelect').length>0) {
			$j('#courseSelect').dropdown();
			$j('button','#importToNode').removeClass("disabled");
			if (tree!=null) $j('#courseSelect').trigger('change');
		} else {
			$j('button','#importToNode').addClass("disabled");
		}
	});
}

function activateStep(stepNumber) {
	$j(document.body).scrollTop(0);
	$j('.ui.stepcontainer .ui.step.active').removeClass('active');
	$j('.ui.stepcontainer .ui.step[data-step="'+stepNumber+'"]').addClass('active');
}
