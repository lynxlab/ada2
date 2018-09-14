/**
 * @package 	secretquestion module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

function initDoc(formname) {
    $j('input[type="submit"]', $j('form[name="'+formname+'"]')).addClass('ui submit button');
    var oldOnclick = $j('input[type="submit"]', $j('form[name="'+formname+'"]')).attr('onclick').replace(/return/g,'').replace(/\(\);/g,'').trim();
    $j('form[name="'+formname+'"]').on('click','input[type="submit"]',
        { formname: formname, validateFN: oldOnclick },
        validateAndSubmit
    );
}

function validateAndSubmit(eventData) {
    if ('function' === typeof window[eventData.data.validateFN] && window[eventData.data.validateFN]()) {
        var debugForm = true;
        var aForm = $j('form[name="'+eventData.data.formname+'"]');
        var url = 'ajax/checkAnswer.php';

        $j.ajax({
            type: "POST",
            url: url,
            data: aForm.serialize(),
            beforeSend: function() {
                aForm.parents('div').first().addClass('ui loading');
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
                aForm.parents('div').first().removeClass('ui loading');
            });
        });
    }
    return false;
}