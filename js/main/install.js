/**
 * INSTALLATION SCRIPT.
 *
 * @package		main
 * @author		Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
var IE_version = false;
function initDoc() {
    $j('input[name="HTTP_ROOT_DIR"]', $j('form[name="installform"]')).val(window.location.href.split('/').slice(0,-1).join('/')+'/');
    $j('.ui.selection.dropdown').dropdown();

    var formInitObj = {};
    $j('input', $j('form[name="installform"]')).each(function(){
        if ('undefinedd' != typeof $j(this).attr('name') && $j(this).data('semantic-validate-type')) {
            formInitObj[$j(this).attr('name')] = {
                identifier: $j(this).attr('name'),
                rules: [
                    {
                        type: $j(this).data('semantic-validate-type'),
                        prompt: $j(this).data('semantic-validate-prompt')
                    }
                ]
            };
        }
    });

    // Listen to message from child window
    bindEvent(window, 'message', function (e) {
        if (e.isTrusted) {
            if (e.data == "doneException") {
                $j('#retryButton-cnt').show();
            } else if (e.data == "doneOK") {
                $j("form[name='installform']").remove();
            }
        }
    });

    $j('#retryButton').click(function() {
        $j('#retryButton-cnt').hide();
        $j("#installResults").slideUp(500, function() {
            $j("form[name='installform']").slideDown(500, function (){
            });
        });
    });

    $j('form[name="installform"]').form(formInitObj, {
        debug: false,
        onSuccess: function() {
            $j("form[name='installform']").slideUp(500, function() {
                $j("#installResults").slideDown(500, function (){
                });
            });
        }
    });
}

// addEventListener support for IE8
function bindEvent(element, eventName, eventHandler) {
    if (element.addEventListener){
        element.addEventListener(eventName, eventHandler, false);
    } else if (element.attachEvent) {
        element.attachEvent('on' + eventName, eventHandler);
    }
}
