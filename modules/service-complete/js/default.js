/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2013, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version		   0.1
 */

document.write("<script type='text/javascript' src='../../js/include/basic.js'></script>");
document.write("<script type='text/javascript' src='../../js/include/menu_functions.js'></script>");

/**
 * change the following to false if you want standard submit
 * instead of ajax 
 */
 var isAjax = true;

function initToolTips() {
	// inizializzo i tooltip sul title di ogni elemento!
	$j('.tooltip').tooltip(
			{
				show : {
					effect : "slideDown",
					delay : 300,
					duration : 100
				},
				hide : {
					effect : "slideUp",
					delay : 100,
					duration : 100
				},
				position : {
					my : "center bottom-5",
					at : "center top"
				}
			});
}

/**
 * shows and after 500ms removes the div to give feedback to the user about
 * the status of the executed operation (if it's been saved, delete or who knows what..)
 * 
 * @param title title to be displayed
 * @param message message to the user
 */
function showHideDiv ( title, message, isOK )
{
		var errorClass = (!isOK) ? ' error' : '';
        var theDiv = $j("<div id='ADAJAX' class='saveResults popup"+errorClass+"'><p class='title'>"+title+"</p><p class='message'>"+message+"</p></div>");
        theDiv.css("position","fixed");
        theDiv.css("z-index",9000);
        theDiv.css("width", "350px");
        theDiv.css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2));
        theDiv.css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2));        
        theDiv.hide().appendTo('body').fadeIn(500).delay(2000).fadeOut(500, function() { 
                theDiv.remove(); 
                });
}