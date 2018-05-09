/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

function initDoc(formName, imgID) {

	var debugForm = false;

	$j('form[name="'+formName+'"]')
		.on('input[type="text"]' ,'keypress', function(e) {
			if (e.which == 13) {
				$j(this).submit();
				return false;
			}
		})
		.on('submit', function(e) {
			e.preventDefault();

			if ($j('#checktxt', $j(this)).val().trim().length <=0) {
				$j('#checktxt', $j(this)).parents().first().children('label').first().addClass('form error');
				$j('#error_form_'+formName, $j(this)).toggleClass('hide_erorr').toggleClass('show_error');
				return false;
			}

			var aForm = $j(this);
			if (debugForm) console.log(aForm.serialize());
		    var url = "ajax/lookupRequest.php";
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
		    	if ('saveResult' in response && 'status' in response && response.status === 'OK') {
		    		aForm.parents('div.fform').first().transition({
		    			animation: 'fade',
		    			complete: function() {
		    				aForm.parents('div.fform').first().remove();
		    				$j('#responseMessage').addClass(response.saveResult.cssClass);
		    				$j('i','#responseMessage').addClass(response.saveResult.icon);
		    				$j('#lookupResponse','#responseMessage').html(response.saveResult.lookupResponse);
		    				$j('#lookupMessage','#responseMessage').html(response.saveResult.lookupMessage);
		    				$j('#responseMessage').transition('fade');
		    			}
		    		});
		    	}
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
		    		loadCaptcha(imgID);
		    		$j('#checktxt', aForm).val('').focus();
		    	});
		    });
		});

	loadCaptcha(imgID);
}

