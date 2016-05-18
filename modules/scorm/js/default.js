/**
 * SCORM MODULE.
 *
 * @package        scorm module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2016, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           scorm
 * @version        0.1
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
	if ('undefined' == typeof isOK) isOK = false;
	var errorClass = (!isOK) ? ' error' : '';
	var content = "<div id='ADAJAX' class='saveResults popup"+errorClass+"'>";
	if ('undefined' != typeof title && title.length > 0) content += "<p class='title'>"+title+"</p>";
	if ('undefined' != typeof message && message.length > 0) content += "<p class='message'>"+message+"</p>";
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

function urlencode( str ) {
	  //
	  // Ref: http://kevin.vanzonneveld.net/techblog/article/javascript_equivalent_for_phps_urlencode/
	  //

	    var histogram = {}, unicodeStr='', hexEscStr='';
	    var ret = (str+'').toString();

	    var replacer = function(search, replace, str) {
	        var tmp_arr = [];
	        tmp_arr = str.split(search);
	        return tmp_arr.join(replace);
	    };

	    // The histogram is identical to the one in urldecode.
	    histogram["'"]   = '%27';
	    histogram['(']   = '%28';
	    histogram[')']   = '%29';
	    histogram['*']   = '%2A';
	    histogram['~']   = '%7E';
	    histogram['!']   = '%21';
	    histogram['%20'] = '+';
	    histogram['\u00DC'] = '%DC';
	    histogram['\u00FC'] = '%FC';
	    histogram['\u00C4'] = '%D4';
	    histogram['\u00E4'] = '%E4';
	    histogram['\u00D6'] = '%D6';
	    histogram['\u00F6'] = '%F6';
	    histogram['\u00DF'] = '%DF';
	    histogram['\u20AC'] = '%80';
	    histogram['\u0081'] = '%81';
	    histogram['\u201A'] = '%82';
	    histogram['\u0192'] = '%83';
	    histogram['\u201E'] = '%84';
	    histogram['\u2026'] = '%85';
	    histogram['\u2020'] = '%86';
	    histogram['\u2021'] = '%87';
	    histogram['\u02C6'] = '%88';
	    histogram['\u2030'] = '%89';
	    histogram['\u0160'] = '%8A';
	    histogram['\u2039'] = '%8B';
	    histogram['\u0152'] = '%8C';
	    histogram['\u008D'] = '%8D';
	    histogram['\u017D'] = '%8E';
	    histogram['\u008F'] = '%8F';
	    histogram['\u0090'] = '%90';
	    histogram['\u2018'] = '%91';
	    histogram['\u2019'] = '%92';
	    histogram['\u201C'] = '%93';
	    histogram['\u201D'] = '%94';
	    histogram['\u2022'] = '%95';
	    histogram['\u2013'] = '%96';
	    histogram['\u2014'] = '%97';
	    histogram['\u02DC'] = '%98';
	    histogram['\u2122'] = '%99';
	    histogram['\u0161'] = '%9A';
	    histogram['\u203A'] = '%9B';
	    histogram['\u0153'] = '%9C';
	    histogram['\u009D'] = '%9D';
	    histogram['\u017E'] = '%9E';
	    histogram['\u0178'] = '%9F';

	    // Begin with encodeURIComponent, which most resembles PHP's encoding functions
	    ret = encodeURIComponent(ret);

	    for (unicodeStr in histogram) {
	        hexEscStr = histogram[unicodeStr];
	        ret = replacer(unicodeStr, hexEscStr, ret); // Custom replace. No regexing
	    }

	    // Uppercase for full PHP compatibility
	    return ret.replace(/(\%([a-z0-9]{2}))/g, function(full, m1, m2) {
	        return "%"+m2.toUpperCase();
	    });
	}

/**
 * creates a request object, used by SCORM API calls
 */
function createRequest() {

	  // this is the object that we're going to (try to) create
	  var request;

	  // does the browser have native support for
	  // the XMLHttpRequest object
	  try {
	    request = new XMLHttpRequest();
	  }

	  // it failed so it's likely to be Internet Explorer which
	  // uses a different way to do this
	  catch (tryIE) {

	    // try to see if it's a newer version of Internet Explorer
	    try {
	      request = new ActiveXObject("Msxml2.XMLHTTP");
	    }

	    // that didn't work so ...
	    catch (tryOlderIE) {

	      // maybe it's an older version of Internet Explorer
	      try {
	        request = new ActiveXObject("Microsoft.XMLHTTP");
	      }

	      // even that didn't work (sigh)
	      catch (failed) {
	        alert("Error creating XMLHttpRequest");
	      }

	    }
	  }

	  return request;
}

function loadScript(url, callback)
{
    // Adding the script tag to the head as suggested before
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.type = 'application/javascript';
    script.src = url;

    // Then bind the event to the callback function.
    // There are several events for cross browser compatibility.
    script.onreadystatechange = callback;
    script.onload = callback;

    // Fire the loading
    head.appendChild(script);
}
