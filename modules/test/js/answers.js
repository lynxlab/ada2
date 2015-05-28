document.write('<script type="text/javascript" src="../../external/fckeditor/browseserver.js"></script>');

function move(e,direction) {
	var li = $j(e).closest('li');
	var index = li.closest('ol').children().has('.answers_cell:visible').index(li);
	var count = li.closest('ol').children().has('.answers_cell:visible').length;
	
	if (direction == 'up' && index > 0) {
		var alt = li.prev();
		alt.before(li.detach());
	}
	else if (direction == 'down' && index < count-1) {
		var alt = li.next();
		alt.after(li.detach());
	}
}

var deleted = false;
function del(e,id_nodo) {
	if (id_nodo == undefined) {
		var li = $j(e).closest('li');
		li.remove();
	}
	else {
		if (confirm(i18n['confirmDelete'])) {
			var loc = window.location.pathname;
			var dir = loc.substring(0, loc.lastIndexOf('/'));
			$j.ajax({
				url: dir+'/delete.php?id_nodo='+id_nodo,
				dataType: 'text',
				async: false,
				success: function(data) {
					if (data == '1') {
						var li = $j(e).closest('li');
						li.remove();
						deleted = true;
					}
				}
			});
		}
	}
}

function check_case_sensitive(e) {
	var form = $j(e).closest('form');
	$j('.case_sensitive_checkbox',form).prop('checked',$j(e).prop('checked'));
	if ($j(e).prop('checked')) {
		$j('.case_sensitive_checkbox',form).attr('checked',true);
	}
	else {
		$j('.case_sensitive_checkbox',form).removeAttr('checked');
	}
	$j('.case_sensitive_checkbox',form).each(function(i,el) {
		change_other_answer(el);
	});
}

function change_other_answer(e) {
	if ($j(e).prop('checked')) {
		$parent = $j(e).closest('ol');
		$j('.other_answer',$parent).val(0);
		$j('.other_answer_checkbox',$parent).prop('checked',false);
		$j('.other_answer_checkbox',$parent).removeAttr('checked');

		$j(e).prop('checked',true);
		$j(e).siblings('input').val(1);
	}
	else {
		$j(e).siblings('input').val(0);
	}
}

function change_case_sensitive(e) {
	if ($j(e).prop('checked')) {
		$j(e).siblings('input').val(1);
	}
	else {
		$j(e).siblings('input').val(0);
	}
}

function add_row(e) {
	var form = $j(e).closest('form');
	var list = $j(e).closest('ol');
	var clone = $j('.clonable',form).closest('li').clone();

	clone.find('.clonable').remove();
	clone.removeClass('hidden');
	clone.find(':disabled').removeAttr('disabled');
	clone.find('.case_sensitive').prop('checked',form.find('.case_sensitive_control').prop('checked'));

	list.find('.answers_footer').closest('li').before(clone);
}

function insertImage(e) {
	$j('#insertImage').dialog({
		autoOpen: false,
		height: $j(window).height()*0.98,
		width: $j(window).width()*0.98,
		closeOnEscape: false,
		draggable: false,
		resizable: false,
		modal: true,
		open: function( event, ui ) {
			$j(event.target.parentElement).find('.ui-dialog-titlebar').remove();
			$j(event.target.parentElement).find('.ui-dialog-buttonpane').remove();
			$j('body').css({'height':'100%','overflow':'hidden'});
		},
		close: function( event, ui ) {
			$j('body').css({'height':'auto','overflow':'visible'});
		}
	});
}

var inputAnswer = null;
function insertImage(e) {
	
	$j('#browseserver').button({
	      icons: {
	          primary: "ui-icon-image"	       
	        }
	      }).click(function() { BrowseServer('inputUrl'); });
	
	var input = $j(e).siblings('input.answer');
	inputAnswer = input[0];
	$j('#insertImage').dialog('open');
}

var dialog_img_buttons = {};
dialog_img_buttons[i18n['confirm']] = function() {
	if (inputAnswer != null && inputAnswer != undefined) {
		var inputUrl = $j('#inputUrl').val();
		if (inputUrl.length > 0) {
			/**
			 * giorgio 15/gen/2014
			 * 
			 * check if the file is an audio and produce appropriate tag
			 * valid audio extensions are in the allowedAudioExtensions array
			 */
			var extension =  inputUrl.split('.').pop();
			var allowedAudioExtensions = new Array ("mp3", "ogg", "MP3", "OGG") ;
			var allowedImageExtensions = new Array ("jpg", "png", "gif", "JPG", "PNG", "GIF") ;
			
			if ($j.inArray (extension, allowedAudioExtensions) !=-1) {
				/**
				 * giorgio 28/ott/2014
				 * 
				 * check if inputUrl contains HTTP_ROOT_DIR and
				 * if it does, remove it so that the generated media
				 * will have the correct file address
				 */
				inputUrl = inputUrl.replace(new RegExp(HTTP_ROOT_DIR,"i"),"");
				var media =  '<MEDIA TYPE="'+MEDIA_SOUND+'"';
					media += ' VALUE="'+inputUrl+'">';
				$j(inputAnswer).val(media);
			} else if ($j.inArray (extension, allowedImageExtensions) !=-1) {						
				// var img = '<img src="'+inputUrl+'"';
				var img = '<MEDIA TYPE="'+MEDIA_IMAGE+'"';
					img += ' VALUE="'+inputUrl+'"';
				
				var inputTitle = $j('#inputTitle').val();
				if (inputTitle.length > 0) {
					img+= ' title="'+inputTitle+'" alt="'+inputTitle+'"';
				}
				if ($j('#radioPopupNo').prop('checked')) {
					img+= ' class="noPopup"';
				}
				var width = $j('#inputWidth').val();
				if (!isNaN(parseInt(width))) {
					img+= ' width="'+width+'"';
				}
				var height = $j('#inputHeight').val();
				if (!isNaN(parseInt(height))) {
					img+= ' height="'+height+'"';
				}
				img+='>';
				$j(inputAnswer).val(img);
			} else {
				alert ("Unsupported file format: " + extension );
				$j(inputAnswer).val('');
			}
		}
	}
	$j(this).dialog('close');
};

dialog_img_buttons[i18n['cancel']] =  function() {
	$j(this).dialog('close');
};

document.observe('dom:loaded', function() {
	var div = $j('#insertImage');
	div.dialog({
		autoOpen: false,
		height: 'auto',
		width: 300,
		draggable: false,
		resizable: false,
		modal: true,
		open: function( event, ui ) {
			$j('#inputUrl',div).val('');
			$j('#inputTitle',div).val('');
			$j('#radioPopupYes',div).prop('checked',true);
			$j('#radioPopupNo',div).prop('checked',false);
			$j('#inputWidth',div).val('75');
			$j('#inputHeight',div).val('75');
		},
		buttons: dialog_img_buttons
	});
});