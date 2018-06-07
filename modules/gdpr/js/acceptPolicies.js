/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

function initDoc(loginRepeaterFormName, formName) {

	var debugForm = true;

	$j('form[name="'+formName+'"]').on('click','button#savePolicies', function(){
		if (checkAllPoliciesAccepted()) {
			var aForm = $j(this).parents('form').first();
			if (debugForm) console.log(aForm.serialize());
			var url = "ajax/saveUserPolicies.php";
			var showHidePromise;
			var data = (debugForm ? 'debugForm=1&' : '') + aForm.serialize();
			
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
					console.log(loginRepeaterFormName);
					console.log($j('form[name="'+loginRepeaterFormName+'"]').length);
					if ('saveResult' in response) {
						if ('redirecturl' in response.saveResult && response.saveResult.redirecturl.trim().length>0) {
							if (debugForm) {
								console.log("Redirect to %s", response.saveResult.redirecturl.trim());
							}
							// if response has a redirect, do it!
							document.location.replace(response.saveResult.redirecturl.trim());
						} else if ('submit' in response.saveResult && response.saveResult.submit===true) {
							// else submit the loginRepeater
							$j('form[name="'+loginRepeaterFormName+'"]').submit();
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
		} else {
			$j('#acceptPoliciesMSG').modal('show');
		}
	});
}

function checkAllPoliciesAccepted() {
	/**
	 * sum of checked inputs having name that starts with
	 * 'acceptPolicy' must mach the policyCount for all mandatory
	 * policies to be accepted
	 */
	var policyCount = parseInt($j('*[data-mandatory-policy="1"]').length);
	try {
		var acceptCount = $j('input[name^="acceptPolicy"]:checked', '*[data-mandatory-policy="1"]').map(function () {
			return parseInt(this.value);
		}).get().reduce(function(acc, val){ return acc+val; });
	} catch(ex) {
		var acceptCount = 0;
	}
	return policyCount === acceptCount; 
}
