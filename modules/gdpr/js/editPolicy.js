/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

function initDoc(formName) {

	var debugForm = false;

	$j('form[name="'+formName+'"]')
		.on('submit', function(e) {
			e.preventDefault();

			var aForm = $j(this);
			if (debugForm) console.log(aForm.serialize());
		    var url = "ajax/savePolicy.php";
		    var showHidePromise;
		    var data = { 
		    		debugForm: debugForm ? 1 :0,
		    		title: $j('#title', aForm).val().trim()
		    };

		    if (!isNaN(parseInt($j('#policy_content_id', aForm).val()))) {
		    	data.policy_content_id = parseInt($j('#policy_content_id', aForm).val());
		    }

			$j('textarea, input[type="checkbox"]', 'form[name="'+formName+'"]').each(function(index, el) {
				console.log(el);
				var name = $j(el).attr('name');
				if ($j(el).is('textarea')) {
					data[name] = FCKeditorAPI.GetInstance(name).GetData();
				} else if ($j(el).is('input')) {
					data[name] = $j(el).is(':checked') ? 1 :0;
				}
			});

		    return $j.ajax({
		    	type: "POST",
		    	url: url,
		    	data: data,
		    	beforeSend: function() {
		    		aForm.parents('div').first().addClass('loading');
		    	}
		    })
		    .done(function(response) {
		    	if (debugForm) console.log('done callback got ', response);
		    	 showHidePromise = showHideDiv(response.title, response.message, true);
		    	 $j.when(showHidePromise).then(function(){
		    		 if ('saveResult' in response) {
		    			 if ('redirecturl' in response.saveResult && response.saveResult.redirecturl.trim().length>0) {
		    				 if (debugForm) {
		    					 console.log("Redirect to %s", response.saveResult.redirecturl.trim());
		    				 }
		    				 // if response has a redirect, do it!
		    				 document.location.replace(response.saveResult.redirecturl.trim());
		    			 }
		    		 }
		    	 });
		    })
		    .fail(function(response) {
		    	if (debugForm) console.log('fail callback ', response);
		    	if ('responseJSON' in response) {

		    		if (debugForm) {
						console.groupCollapsed(url+' fail');
						if ('errorMessage' in response.responseJSON) {
							console.error('message: %s', response.responseJSON.errorMessage);
						}
						if ('errorTrace' in response.responseJSON) {
							console.error('stack trace %s', response.responseJSON.errorTrace);
						}
						console.groupEnd();
		    		}

		    		showHidePromise = showHideDiv(response.responseJSON.title, response.responseJSON.message, false);

		    	} else {
		    		var errorText = response.statusText;
		    		if ('responseText' in response && response.responseText.length>0) errorText += '<br/>'+response.responseText;
		    		showHidePromise = showHideDiv('Error ' + response.status, errorText, false);
		    	}
		    })
		    .always(function(response) {
		    	if (debugForm) console.log('always callback');
		    	$j.when(showHidePromise).then(function(){
		    		aForm.parents('div').first().removeClass('loading');
		    	});
		    });
		});
	
	// replace all textareas with an fckeditor
	$j('textarea', 'form[name="'+formName+'"]').each(function(index, el) {
		loadFCKeditor($j(el).attr('name'), 'Default');
	});
}

function loadFCKeditor(textarea_name, toolbar) {
	if ($j('#'+textarea_name).size() == 1) {
		toolbar = (typeof toolbar === 'undefined') ? 'Basic' : toolbar;

		var oFCKeditor = new FCKeditor( textarea_name );
		oFCKeditor.BasePath = '../../external/fckeditor/';
		oFCKeditor.Width = '100%';
		oFCKeditor.Height = '300';
		oFCKeditor.ToolbarSet = toolbar;
		oFCKeditor.ReplaceTextarea();
	}
}
