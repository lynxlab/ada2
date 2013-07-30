document.write('<script type="text/javascript" src="../../external/fckeditor/fckeditor.js"></script>');

/**
 * change the following to false if you want standard submit
 * instead of ajax 
 */
 var isAjax = true;

function loadFCKeditor(textarea_name, toolbar) {
	if ($j('#'+textarea_name).size() == 1) {
		toolbar = (typeof toolbar === 'undefined') ? 'Test' : toolbar;

		var oFCKeditor = new FCKeditor( textarea_name );
		oFCKeditor.BasePath = '../../external/fckeditor/';
		oFCKeditor.Width = '100%';
		oFCKeditor.Height = '300';
		oFCKeditor.ToolbarSet = toolbar;
		oFCKeditor.ReplaceTextarea();
	}
}

function initDoc()
{
	if ($j('div.fform.form').length >0) {
		$j('div.fform.form').css('width', '100%');
		$j('#date').mask("99/99/9999");
		loadFCKeditor('htmltext');
		$j('form[name=editnewsletter] :submit').button();
	} else {	
		/**
		 * save results ok button
		 */
		$j('#newsletterSaveResultsbutton').button();
	}
	
	$j('form[name=editnewsletter]').on('submit', function(event) 
	{
		// do standard submit if we don't want ajax call
		// else proceed with ajax
		if (!isAjax) return true;
		else {
			event.preventDefault();
			
			var postData = $j(this).serialize();
			postData += '&requestType=ajax';
			
			$j.ajax({
				type	: 'POST',
				url		: HTTP_ROOT_DIR+ '/modules/newsletter/edit_newsletter.php',
				data	: postData,
				dataType: 'html'
			})
			.done(function (html) {
				$j('div.fform.form').effect('drop', function() {
					$j('#moduleContent').html(html).hide();
					$j('#newsletterSaveResultsbutton').button();
					$j('#moduleContent').effect('slide');
				});
			} );
			
			return false;
		}
	});
	
}