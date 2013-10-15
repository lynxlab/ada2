/**
 * Initializations
 * 
 * @param maxSize the max uploadable file size 
 * @param userId the user ID
 */
function initDoc(maxSize,userId) {
	initDateField(); // initialization of maskedDate
        $j('#avatar').closest('li').css('border','none');
        
        FileNameField = $j('input[type=file]').attr('id');
        /*
         * initialization of avatar preview
         */
        if ($j('#avatar').val() != '') {
            var avatarValue = $j('#avatar').val();
        } else {
            var avatarValue = '../owl.png';
        }
        var imgSrcAvatar = $j('<img>').attr('src',HTTP_UPLOAD_PATH+userId+'/'+avatarValue).attr('id','imgAvatar');
        $j('#l_avatarfile').append($j('<div></div>').attr('id', 'avatar_preview'));
         $j('#avatar_preview').append(imgSrcAvatar);
        
	$j("#avatarfile").pekeUpload({
		// onSubmit: true,
		allowedExtensions : "png|jpg|jpeg|gif",
		// onFileError:function(file,error){alert("error on file: "+file.name+"
		// error: "+error+"");},
		onFileSuccess : function(file) {
			showImage(file, userId);
		},
		btnText : "Sfoglia Files..",
		// multi: false,
		maxSize : maxSize,
		field : 'uploaded_file',
		url : HTTP_ROOT_DIR+'/js/include/jquery/pekeUpload/upload.php?userId='+userId+'&fieldUploadName='+FileNameField
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
	
}

function showImage(file,userId) {
    $j('#imgAvatar').attr('src',HTTP_UPLOAD_PATH+userId+'/'+file.name);
    $j('#avatar').val(file.name);
    $j('.pekecontainer').hide();
}

