/**
 * @package 	gdpr module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2018, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */
document.write("<script type='text/javascript' src='../../js/include/basic.js'></script>");
document.write("<script type='text/javascript' src='../../js/include/menu_functions.js'></script>");

/**
 * shows and after 500ms removes the div to give feedback to the user about
 * the status of the executed operation (if it's been saved, delete or who knows what..)
 *
 * @param  title title to be displayed
 * @param  message message to the user
 * @return jQuery promise
 */
function showHideDiv ( title, message, isOK ) {
	if ('undefined' == typeof isOK) isOK = false;
	var errorClass = (!isOK) ? ' error' : 'success';
	var hasIcon = false, contentIcon='', contentTitle='', contentMessage='';
	if ('undefined' != typeof title && title.length > 0) {
		title = $j("<div>"+title+"</div>");
		hasIcon = $j('i',title).length>0;
		if (hasIcon) {
			contentIcon = "<i class='"+$j('i',title).attr('class')+"'></i>";
			$j('i',title).remove();
		}
		contentTitle = title.text();
	}
	if ('undefined' != typeof message && message.length > 0) {
		contentMessage = message;
	}

	var content = "<div id='ADAJAX' class='ui "+
		(hasIcon ? 'icon ' : '')+errorClass+" compact floating message' style='transition:none;'>";
	content += contentIcon + "<div class='content'><div class='header'>";
	content += contentTitle + "</div>";
	content += "<p>"+contentMessage+"</p></div></div>";

	var theDiv = $j(content);
	theDiv.hide().appendTo('body')
	      .css("position","fixed")
	      .css("z-index",9000)
	      .css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2))
	      .css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2))
	      .fadeIn(250).delay(2000);
	var thePromise = theDiv.fadeOut(250);
	$j.when(thePromise).done(function() { theDiv.remove(); });
	return thePromise;
}

function semanticConfirm(selector, callbacks) {
	$j(selector)
	  .modal('setting', $j.extend({}, {closable: false}, callbacks || {}))
	  .modal('show');
}
