document.write("<script type='text/javascript' src='../external/fckeditor/fckeditor.js'></script>");
//document.write("<script type='text/javascript' src='../js/include/fckeditor_integration.js'></script>");

function includeFCKeditor(textarea_name) {
   var oFCKeditor = new FCKeditor( textarea_name );
   oFCKeditor.BasePath = '../external/fckeditor/';
   oFCKeditor.Width = '100%';
   oFCKeditor.Height = '350';
   if (textarea_name!='descrizione') {
	   oFCKeditor.ToolbarSet = 'Default';
   } else {
	   oFCKeditor.ToolbarSet = 'editcourse';
   }
   oFCKeditor.Config["StylesXmlPath"] = '../fckADAstyles.xml';
   oFCKeditor.Config['TemplatesXmlPath'] = '../fckADAtemplates.xml';
   oFCKeditor.ReplaceTextarea();
  }


/**
 * function createEditor
 *
 * creates and returns an instance of FCKeditor.
 * .@param FCKeditorID - the id of the textarea to be replaced by FCKeditor
 * .@return oFCKeditor - FCKeditor instance
 */
function createEditor(FCKeditorID, Plain_textID) {
	$(FCKeditorID).value = ADAToFCKeditor($(Plain_textID).value);

	var oFCKeditor = new FCKeditor(FCKeditorID);
        oFCKeditor.BasePath = '../external/fckeditor/';
        oFCKeditor.Width = '100%';
	oFCKeditor.Height = '350';
	oFCKeditor.ToolbarSet = 'Basic';

	oFCKeditor.ReplaceTextarea();

	return oFCKeditor;
}

function initEditCourse(userID, courseID) {
	$j('#uploadFile').change(function() {
		if ($j(this).val().length > 0) {
			$j('#saveFileButton').removeAttr('disabled');
		} else {
			$j('#saveFileButton').attr('disabled', 'disabled');
		}
	});

	$j("#saveFileButton").click(function(event){
		var buttonClosure = $j(this);
		var formData = new FormData();
		formData.append('userID', userID);
		formData.append('courseID', courseID);

		$j(this).parents('fieldset').first().find('input').each(function() {
			if ('file' === $j(this).attr('type')) {
				formData.append($j(this).attr('name'), $j(this)[0].files[0]);
			} else {
				formData.append($j(this).attr('name'), $j(this).val());
			}
		});

		$j.ajax({
			type: 'POST',
			url: 'ajax/uploadCourseAttachment.php',
			data: formData,
			// the following two lines are mandatory to have non empty PHP $_FILES
			processData: false,
			contentType: false,
			beforeSend: function() { buttonClosure.attr('disabled', 'disabled'); $j('.uploadMSG').remove(); }
		})
		.always(function() {
			buttonClosure.parents('fieldset').first().find('input').each(function() {
				if ('hidden' !== $j(this).attr('type')) $j(this).val('');
			});
		})
		.done(function(response) {
			buttonClosure.parents().first().prepend('<div id="uploadOkMSG" class="uploadMSG">'+response.message+'</div>');
			buildCourseAttachmentsTable(courseID, true);
		})
		.fail(function(response) {
			buttonClosure.parents().first().prepend('<div id="uploadErrorMSG" class="uploadMSG">'+response.responseJSON.message+'</div>');
		});
	});
	
	buildCourseAttachmentsTable(courseID, true);
}

function buildCourseAttachmentsTable(courseID, withTrashLink, context) {
	var context = context || $j('ol.form').first().children('li.form').last().parents('ol').first();
	$j('li.form.attachments', context).remove();
	$j.when(getCourseAttachments(courseID, withTrashLink))
	  .always(function(responseData) { 
		  var data = responseData.data || 'unknown error';
		  var error = 'undefined' === typeof responseData.error || responseData.error;
		  if (error === false) {
			  var tableData = data.data;
			  if (Object.keys(tableData).length > 0) {
				var hasHeaders = false;
				var htmlArr = [ '<table id="courseAttachments" class="ui padded table">' ];
				
				if ('udenfined' !== typeof tableData.caption && tableData.caption.length > 0) {
					htmlArr.push('<caption>'+ tableData.caption +'</caption>');
				}
				
				if ('undefined' !== typeof tableData.headers && tableData.headers.length > 0) {
					hasHeaders = true;
					htmlArr.push('<thead><tr>');
					for (var i=0; i<tableData.headers.length; i++) {
						htmlArr.push('<th>'+tableData.headers[i].label+'</th>');
					}
					htmlArr.push('</tr></thead>');
				}
				htmlArr.push('<tbody>');
				for (var resID in tableData.resources) {
					if (tableData.resources.hasOwnProperty(resID)) {
						htmlArr.push('<tr>');
						if (hasHeaders) {
							for (var i=0; i<tableData.headers.length; i++) {
								htmlArr.push('<td>'+tableData.resources[resID][tableData.headers[i].property]+'</td>');
							}							
						} else {
							for (var prop in tableData.resources[resID]) {
								if (tableData.resources[resID].hasOwnProperty(prop)) {
									htmlArr.push('<td>'+ tableData.resources[resID][prop] +'</td>');
									
								}
							}
						}
						htmlArr.push('</tr>');
					}
				}
				htmlArr.push('</tbody></table>');
				htmlString = htmlArr.join("\n");
			  }
		  } else {
			htmlString = '<span style="color:red;">'+data+'</span>';  
		  }
		  $j(context).append('<li class="form attachments">'+htmlString+'</li>');
	  });
}

function getCourseAttachments(courseID, withTrashLink) {
	var promise = $j.Deferred();
	$j.ajax({
		type: 'GET',
		url: 'ajax/getCourseAttachments.php',
		data: { courseID : courseID , trashLink: (withTrashLink ? 1 :0) }
	})
	.done(function(response) {
		promise.resolve({error:false, data: response}); 
	})
	.fail(function(response) { 
		var retStr = [];
		if ('undefined' !== typeof response.responseJSON) {
			if ('udefined' !== typeof response.responseJSON.data && response.responseJSON.data.length>0) {
				retStr.push(response.responseJSON.data.trim());
			}
		}
		if (retStr.length === 0) {
			if ('undefined' !== typeof response.status) retStr.push(response.status);
			if ('undefined' !== typeof response.statusText) retStr.push(response.statusText);
		}
		promise.reject({ error: true, data: retStr.join(' - ') }); 
	});
	
	return promise.promise();
}

function deleteCourseAttachment (resID, courseID) {
	if (confirm($j('#fileDeleteConfirmMSG').val())) {
		$j.ajax({
			type: 'POST',
			url : 'ajax/deleteCourseAttachment.php',
			data: { resourceID: resID, courseID: courseID },
			beforeSend: function() { $j('.uploadMSG').remove(); }
		})
		.done(function(){
			buildCourseAttachmentsTable(courseID, true);
		})
		.fail(function(response) {
			$j('table#courseAttachments').parents().first().append('<div id="uploadErrorMSG" class="uploadMSG">'+response.responseJSON.message+'</div>');
		});
	}
}
