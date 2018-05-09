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
		.on('change', 'select#requestType', function() {
			var aForm = $j($j(this).parents('form').first());

			// hide all shownonselected ids
			$j('[data-showonselected]', this).each(function() {
				var target = $j($j('#'+$j(this).data('showonselected'), aForm).parents('li.form').first());
				if (target.is(':visible')) target.slideUp('fast');
			});

			// show showonselected selected option id
			var showID = $j('option:selected', this).data('showonselected') || null;
			if (null !== showID) {
				var target = $j($j('#'+showID, aForm).parents('li.form').first());
				if (!target.is(':visible')) target.slideDown('fast');
			}
		})
		.on('submit', function(e) {
			e.preventDefault();

			var aForm = $j(this);
			if (debugForm) console.log(aForm.serialize());
		    var url = MODULES_GDPR_HTTP + "ajax/saveRequest.php";
		    var showHidePromise;
		    return $j.ajax({
		    	type: "POST",
		    	url: url,
		    	data: aForm.serialize()+'&debugForm='+(debugForm ? 1:0),
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
		    				 $j('#redirectBtn').show().click(function() {
		    					 if (!$j(this).hasClass('disabled')) {
		    						 $j(this).addClass('disabled')
		    						 // if response has a redirect, do it!
		    						 document.location.replace(response.saveResult.redirecturl.trim());
		    					 }
		    					 
		    				 });
		    			 }
		    			 if ('redirectlabel' in response.saveResult && response.saveResult.redirectlabel.trim().length>0) {
		    				 $j('#redirectBtn').show().children('#redirectLbl').first().html(response.saveResult.redirectlabel.trim());
		    			 }
		    			 if ('requestUUID' in response.saveResult) {
		    				 aForm.parents('div.fform').first().transition({
		    					 animation: 'fade',
		    					 complete: function() {
		    						 aForm.parents('div.fform').first().remove();
		    						 $j('#requestUUID','.ui.success.message').html(response.saveResult.requestUUID);
		    						 $j('.ui.success.message').transition('fade')
		    					 }
		    				 });
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
	$j('select#requestType','form[name="'+formName+'"]').trigger('change');
}