/**
 * @package     impersonate module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

impDebug = false;

function newLinkedUser(domObj) {
    ajaxDoLinkedUser(domObj, 'new');
}

function deleteLinkedUser(domObj) {
    ajaxDoLinkedUser(domObj, 'delete');
}

function ajaxDoLinkedUser(domObj, action) {
    const baseUrl = domObj.data('baseUrl');
    const linkedType = domObj.data('linkedType');
    const sourceId = domObj.data('sourceId') || 0;
    const title = '';
    var msg = $j('#unknownErrorMSG').html();
    var isOK = false;
    var callback = function() {};

    if (action == 'new' || action == 'delete') {
        return $j.ajax({
            type: 'POST',
            url: `${baseUrl}/ajax/${action}LinkedUser.php`,
            data: { linkedType: linkedType, sourceId: sourceId },
            dataType: 'json',
        })
        .done(function (resp) {
            if ('status' in resp) {
                isOK = resp.status == 'OK';
            }
            if ('msg' in resp) {
                msg = resp.msg;
            }
            if ('reload' in resp && resp.reload == true) {
                callback = function() {
                    self.document.location.reload();
                }
            }
        })
        .fail(function (resp) {
            msg = 'Server Error';
        })
        .always(function (resp) {
            $j.when(showHideDiv('', msg, isOK)).then(callback);

        });
    }
}

/**
 * shows and after 500ms removes the div to give feedback to the user about
 * the status of the executed operation (if it's been saved, delete or who knows what..)
 *
 * @param  title title to be displayed
 * @param  message message to the user
 * @return jQuery promise
 */
function showHideDiv(title, message, isOK) {
    var errorClass = (!isOK) ? ' error' : '';
    var content = "<div id='ADAJAX' class='saveResults popup" + errorClass + "'>";
    if (title.length > 0) content += "<p class='title'>" + title + "</p>";
    if (message.length > 0) content += "<p class='message'>" + message + "</p>";
    content += "</div>";
    var theDiv = $j(content);
    theDiv.css("position", "fixed");
    theDiv.css("z-index", 9000);
    theDiv.css("width", "350px");
    theDiv.css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2));
    theDiv.css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2));
    theDiv.hide().appendTo('body').fadeIn(500).delay(2000);
    var thePromise = theDiv.fadeOut(500);
    $j.when(thePromise).done(function () { theDiv.remove(); });
    return thePromise;
}
