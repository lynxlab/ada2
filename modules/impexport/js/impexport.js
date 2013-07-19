var progressbar;
var progressLabel;
var repeatTimer;

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

	progressbar = $j("#progressbar"), progressLabel = $j("#progress-label");

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
}

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

function goToImportStepThree ()
{	
	var authorSelect = document.getElementById('author');
	var authorID = authorSelect.options[author.selectedIndex].value; 
	
	if (authorID <= 0)
	{
		alert ('Please select an author from the dropdown list');	
	}
	else
	{
		var fileName = $j('#importFileName').val();
		
		$j('.importFormStep2').effect('drop', function() {
			$j('.importFormStep3').effect('slide');
		});
		
		requestProgress();
		
		/** make an ajax POST call to the script doing the import **/
		$j.ajax({
			type	:	'POST',
			url		: HTTP_ROOT_DIR+ '/modules/impexport/import.php',
			data	: { importFileName: fileName, author: authorID, op:'ajaximport' },
			dataType: 'html'
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

function goToImportStepTwo(file) {
	$j('#importFileName').val(file.name);
	$j('#uploadedFileName').html(file.name);
	$j('.importFormStep1').effect('drop', function() {
		$j('.importFormStep2').effect('slide');
	});
}