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
	
	$j("#importfile").pekeUpload({
		// onSubmit: true,
		allowedExtensions : "zip",
		// onFileError:function(file,error){alert("error on file: "+file.name+"
		// error: "+error+"");},
		onFileSuccess : function(file) {
			goToImportStepTwo(file);
		},
		btnText : "Sfoglia Files..",
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
}

/**
 * request the importProgress session vars via an async post ajax call
 * displays them nicely to the user, in the progressbar
 */
function requestProgress()
{
	var requestPB = progressbar;
	
	$j.ajax({
		type	: 'POST',
		url		: HTTP_ROOT_DIR+ '/modules/impexport/requestProgress.php',
		dataType:'json'
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
				}
		} )
		.fail   (function() { 
			console.log("cannot get progress status"); 
		} )
		.always (function() {
			// this timer will be cleared when the import has finished
			repeatTimer = window.setTimeout ( function() { requestProgress(); }, 1000 );
		} );	
	
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
		var fileName = $j('#importFileName').val();

		var courseID = $j('#selCourse').text();
		var nodeID = $j('#selNode').text();
		
		var postData = new Object();
		
		postData.importFileName = fileName;
		postData.author = authorID;
		postData.op = 'ajaximport';
		
		if (courseID!='') postData.courseID = parseInt (courseID);
		if (nodeID!='') postData.nodeID = $j.trim(nodeID);
		
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
		$j.ajax({
			type	:	'POST',
			url		: HTTP_ROOT_DIR+ '/modules/impexport/import.php',
			data	: postData,
			dataType: 'html',
			beforeSend : function () { requestProgress(); }
			})
			.done ( function (html) { 
				$j('.importFormStep3').effect('drop', function() {
					$j('.importFormStep3').html(html).effect('slide'); 
				});
				// $j('.importFormStep3').html (html);  
				})
			.fail ( function (html) {} )
			.always (function () {
				window.clearTimeout (repeatTimer);
			});
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
		
		$j('#selCourse').text(courseID);
		$j('#selNode').text(courseID + '_0');
		
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
function goToImportStepTwo(file) {
	if (typeof file !='undefined')
	{
		$j('#importFileName').val(file.name);
		$j('#uploadedFileName').html(file.name);
	}
	
	$j('.importFormStep1').effect('drop', function() {
		$j('.importFormStep2').effect('slide');
	});

}