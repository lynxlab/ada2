/**
 * CLASSBUDGET MODULE.
 *
 * @package        classbudget module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2015, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           classbudget
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

function closeDIV(elementID) {
	if (elementID.indexOf('#')!=0) elementID = '#'+elementID;
	$j(elementID).effect('drop',400, function(){ $j(elementID).remove(); });
}

function trim(str) {
	str = str.replace(/^\s+/, '');
	for (var i = str.length - 1; i >= 0; i--) {
		if (/\S/.test(str.charAt(i))) {
			str = str.substring(0, i + 1);
			break;
		}
	}
	return str;
}

/**
 * From: http://stackoverflow.com/questions/149055/how-can-i-format-numbers-as-money-in-javascript
 *  
 * Number.prototype.format(n, x, s, c)
 * 
 * @param integer n: length of decimal
 * @param integer x: length of whole part
 * @param mixed   s: sections delimiter
 * @param mixed   c: decimal delimiter
 */
Number.prototype.format = function(n, x, s, c) {
	var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
		num = this.toFixed(Math.max(0, ~~n));

	return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};