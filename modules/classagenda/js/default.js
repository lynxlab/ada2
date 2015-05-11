/**
 * CLASSAGENDA MODULE.
 *
 * @package        classagenda module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           classagenda
 * @version		   0.1
 */

document.write("<script type='text/javascript' src='../../js/include/basic.js'></script>");
document.write("<script type='text/javascript' src='../../js/include/menu_functions.js'></script>");
document.write("<script type='text/javascript' src='js/modules_define.js.php'></script>");

/**
 * shows and after 500ms removes the div to give feedback to the user about
 * the status of the executed operation (if it's been saved, delete or who knows what..)
 * 
 * @param  title title to be displayed
 * @param  message message to the user
 * @return jQuery promise
 */
function showHideDiv ( title, message, isOK ) {
		var errorClass = (!isOK) ? ' error' : '';
		var content = "<div id='ADAJAX' class='saveResults popup"+errorClass+"'>";
		if (title.length > 0) content += "<p class='title'>"+title+"</p>";
		if (message.length > 0) content += "<p class='message'>"+message+"</p>";
		content += "</div>";
		var theDiv = $j(content);
		theDiv.css("position","fixed");
		theDiv.css("z-index",9000);
		theDiv.css("width", "350px");
		theDiv.css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2));
		theDiv.css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2));
		theDiv.hide().appendTo('body').fadeIn(500).delay(2000);
		var thePromise = theDiv.fadeOut(500);
		$j.when(thePromise).done(function() { theDiv.remove(); });
		return thePromise;
}

function showLoading() {
	$j('body').append($j(document.createElement('div')).attr('id','divLoading'));
	$j('body').addClass('loading');
}

function hideLoading() {
	$j('body').removeClass('loading');
	if ($j('#divLoading').length>0) $j('#divLoading').remove();
}