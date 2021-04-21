var progressbar;
var progressLabel;
var repeatTimer;
var tree;

/**
 * Initializations
 *
 * @param maxSize the max uploadable file size
 */
function initDoc(maxSize) {
	const isAuthorImporting = $j('#isAuthorImporting').length==1 && $j('#isAuthorImporting').val()=='1';
	$j('#isAuthorImporting').remove();

	$j("#importfile").pekeUpload({
		// onSubmit: true,
		allowedExtensions : "zip",
		// onFileError:function(file,error){alert("error on file: "+file.name+"
		// error: "+error+"");},
		onFileSuccess : function(file) {
			goToImportStepTwo(file, isAuthorImporting);
		},
		btnText : "Sfoglia Files",
		// multi: false,
		maxSize : maxSize,
		field : 'uploaded_file',
		url : 'upload.php'
	});

	progressbar = $j("#progressbar");
	progressLabel = $j("#progress-label");

	progressbar.progressbar({
		value : 0,
		max	  : 1,
		change : function() {
			progressLabel.text(progressbar.progressbar("value") + " / " + progressbar.progressbar("option","max"));
		},
		complete : function() {
			progressLabel.text(progressbar.progressbar("option","max") + " / " + progressbar.progressbar("option","max"));
		}
	});

	tree = $j('#courseTree');
	$j('.importSN2buttons').css('display','none');
	tree.css ('display','none');

	if ($j('#courseID').length>0 && $j('#service_level').length>0) {
		$j('#courseID').on('change', function() {
			var opacity = (parseInt($j(this).val())==0) ? 1 : 0;
			$j('#service_level').parents('li').first().fadeTo(200,opacity);
		});
	}

	// move importUrlStatus to proper location
	$j('#importUrlStatus').detach().appendTo('#importUrlFSet');

	// prevent accidental url import by disabling enter key
	$j('#importURL').on('keypress', function(event) {
	    return event.keyCode != 13;
	});

	const getFileFromUrl = function() {
		var theUrl = $j('#importURL').val();
		if (theUrl.length>0) {
			return $j.ajax({
				type    : 'GET',
				url     : HTTP_ROOT_DIR+ '/modules/impexport/getFileFromUrl.php',
				dataType: 'json',
				data    : { url: theUrl },
				beforeSend : function () {
					// this timer will be cleared when the download has finished
					repeatTimer = window.setTimeout ( function() { requestProgress(); }, 100 );
					$j('#importUrlBtn, #importfile').prop("disabled",true);
					$j('a.btn-pekeupload').toggleClass('disabled');
				}
			})
			.done(function(JSONObj){
				if (JSONObj.status=='OK') {
					goToImportStepTwo({ name: JSONObj.filename }, isAuthorImporting);
				} else {
					if ('undefined' != JSONObj.msg && JSONObj.msg.length>0) {
						alert (JSONObj.msg);
					} else {
						alert ('unknown error');
					}
				}
			})
			.always(function(){
				window.clearTimeout(repeatTimer);
				$j('#importUrlBtn, #importfile').prop("disabled",false);
				$j('a.btn-pekeupload').toggleClass('disabled');
			});
		} else {
			alert ($j('#emptyURLMSG').text());
		}
	}

	// do the import from url
	$j('#importUrlBtn').on('click', getFileFromUrl);

	if ($j('#forceRunImport').length==1 && $j('#forceRunImport').val()=='1') {
		$j.when(getFileFromUrl())
		.done(function() {
			if (isAuthorImporting && $j('#selNode').text().trim().length > 0) {
				$j('.importFormStep3').effect('slide');
				$j.when(doImport(isAuthorImporting))
				.done(function() {
					const courseID = $j('#selCourse').text();
					const nodeID = $j('#selNode').text();
					$j('.importFormStep3').html("<h1>Importazione completata!</h1><p>A breve sarai ridirezionato al nodo dell'importazione</p>").show();
					window.setTimeout(function() {
						document.location.href = `${HTTP_ROOT_DIR}/browsing/view.php?id_course=${courseID}&id_node=${nodeID}`;
					}, 2500);
				});
			}
		});
	}
}

/**
 * request the importProgress session vars via an async post ajax call
 * displays them nicely to the user, in the progressbar
 */
function requestProgress()
{
	var requestPB = progressbar;

	$j.ajax({
		cache	: false,
		type	: 'POST',
		url		: HTTP_ROOT_DIR+ '/modules/impexport/requestProgress.php',
		dataType: 'json',
		data	: ''
		})
		.done   (function( JSONObj ) {
			if (JSONObj)
				{
					if (JSONObj.status=='ITEMS')
					{
						requestPB.progressbar( "option", "max", JSONObj.totalItems );
						requestPB.progressbar( "option", "value", JSONObj.currentItem );

						$j('#coursename').html(JSONObj.courseName);
					}
					else if (JSONObj.status=='COPY')
					{
						requestPB.progressbar( "option", "value", JSONObj.totalItems );
						if ($j('.currentCourse').is(':visible'))
						{
							$j('.currentCourse').effect('drop', function() {
								$j('.copyzip').effect('slide'); });
						}
					}
					else if (JSONObj.status=='DOWNLOAD')
					{
						if (JSONObj.progressSTATUS=='RUNNING') {
							$j('#importUrlStatus').html(JSONObj.progressMSG);
						} else {
							$j('#importUrlStatus').html('');
							window.clearTimeout(repeatTimer);
						}
					}
				}
		} )
		.fail   (function() {
		} )
		.always (function(JSONObj) {
			// this timer will be cleared when the import has finished
			if (('undefined' == JSONObj.progressSTATUS) ||
				('undefined' != JSONObj.progressSTATUS && 'ERROR' != JSONObj.progressSTATUS)) {
				repeatTimer = window.setTimeout ( function() { requestProgress(); }, 2000 );
			}
		} );
}

const doImport = function(isAuthorImporting) {
	const authorSelect = document.getElementById('author');
	const authorID = authorSelect.options[authorSelect.selectedIndex].value;
	const fileName = $j('#importFileName').val();
	const courseID = $j('#selCourse').text();
	const nodeID = $j('#selNode').text();
	var postData = new Object();

	postData.importFileName = fileName;
	postData.author = authorID;
	postData.serviceLevel = ($j('#service_level').length>0) ? $j('#service_level').val() : 0;
	postData.op = 'ajaximport';

	if (courseID!='') postData.courseID = parseInt (courseID);
	if (nodeID!='') postData.nodeID = $j.trim(nodeID);

	return $j.ajax({
		cache   : false,
		type	: 'POST',
		url		: HTTP_ROOT_DIR+ '/modules/impexport/import.php',
		data	: postData,
		dataType: 'json',
		beforeSend : function () {
			// this timer will be cleared when the import has finished
			repeatTimer = window.setTimeout ( function() { requestProgress(); }, 1000 ); }
		})
		.done ( function (JSONObj) {
			if (!isAuthorImporting) {
				$j('.importFormStep3').effect('drop', function() {
					$j('.importFormStep3').html(JSONObj.html).effect('slide');
				});
			}
			// $j('.importFormStep3').html (html);
			})
		.fail ( function (JSONObj,t ,m) {
				$j('.importFormStep3').effect('drop', function() {
					$j('.importFormStep3').html('Completato, verificare l\'importazione navigando i nodi importati').effect('slide');
				});
			})
		.always (function (JSONObj) {
			window.clearTimeout (repeatTimer);
		});
}

/**
 * performs actual import via an async post ajax call to the proper php file
 * prevents the displayed form to be submitted.
 * (note that the php file should work as well if the form's being submitted and
 * no ajax call is made).
 *
 * @returns {Boolean} false
 */
function goToImportStepThree ()
{
	var authorSelect = document.getElementById('author');
	var authorID = authorSelect.options[authorSelect.selectedIndex].value;

	if (authorID <= 0)
	{
		alert ('Please select an author from the dropdown list');
	}
	else
	{
		if ($j('.importFormStep2').is(':visible'))
		{
			divToHide = '.importFormStep2';
		} else {
			divToHide = '.divImportSN';
		}
		$j(divToHide).effect('drop', function() {
			$j('.importFormStep3').effect('slide');
		});
		/** make an ajax POST call to the script doing the import **/
		doImport(false);
	}
	return false;
}

function goToImportSelectNode()
{
	var courseSelect = document.getElementById('courseID');
	var courseID = courseSelect.options[courseSelect.selectedIndex].value;

	if (courseID <=0 ) return goToImportStepThree();
	else
	{
		$j('.importFormStep2').effect('drop', function() {
			$j('.divImportSN').effect('slide');
		});

		if ($j('#selCourse').text().trim().length == 0) {
			$j('#selCourse').text(courseID);
		}
		if ($j('#selNode').text().trim().length == 0) {
			$j('#selNode').text(courseID + '_0');
		}

		// loads the treeview..

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
						$j('#courseTreeLoading').hide( function() { $j('.importSN2buttons').effect('fade'); } );

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

function returnToImportStepTwo()
{
	$j('.divImportSN').effect('drop', function() {
		$j('.importFormStep2').effect('slide');
	});

	$j('#selCourse').text('');
	$j('#selNode').text('');

	$j('.importSN2buttons').css('display','none');
	$j('#courseTreeLoading').show();

}

/**
 * displays import step two
 *
 * @param file uploaded file name to be displayed
 */
function goToImportStepTwo(file, isAuthorImporting) {
	if (typeof file !='undefined')
	{
		$j('#importFileName').val(file.name);
		$j('#uploadedFileName').html(file.name);
	}

	if (!isAuthorImporting) {
		$j('.importFormStep1').effect('drop', function() {
			$j('.importFormStep2').effect('slide');
		});
	}
}