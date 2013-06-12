document.write("<script type='text/javascript' src='../js/include/basic.js'></script>");
document.write("<script type='text/javascript' src='../js/include/menu_functions.js'></script>");
document.write("<script type='text/javascript' src='../external/mediaplayer/flowplayer/flowplayer.min.js'></script>");

function toggleVisibilityByDiv(className, mode)
{
	$$('ul.'+className).each( function(e) {
		if (!$(e).empty()) {
			toggleVisibilityByClassName(className, e.id, mode);
		}
	});
}

function toggleVisibilityByClassName(className, idName, mode)
{
	var children = $$('ul#'+idName+'.'+className);

	if (mode == 'show') children.invoke('show');
	else if (mode == 'hide') children.invoke('hide');
	else {
		mode = 'toggle';
		children.invoke('toggle');
	}

	/*
	 * Get span element identifier for span element with title=container_div+item_class:
	 * since there is only one (if it exists) span element with this class name, it is safe
	 * to get its id in this way.
	 */

	var span_element_id = $$('span#s'+idName+'.'+className).first();

	if (typeof span_element_id != 'undefined')
	{
		if (mode == 'show' || (mode == 'toggle' && $(span_element_id).hasClassName('hideNodeChildren')))
		{
			$(span_element_id).update();
			$(span_element_id).insert('-');
			$(span_element_id).removeClassName('hideNodeChildren');
			$(span_element_id).addClassName('viewNodeChildren');
		}
		else if (mode == 'hide' || (mode == 'toggle' && $(span_element_id).hasClassName('viewNodeChildren')))
		{
			$(span_element_id).update();
			$(span_element_id).insert('+');
			$(span_element_id).removeClassName('viewNodeChildren');
			$(span_element_id).addClassName('hideNodeChildren');
		}
	}
}

function printit() 
{
  if (typeof window.print == 'function') {
    window.print();
  }
}

function openInRightPanel(httpFilePath, fileExtension) {
    var rightPanel = 'rightpanel';
    if( $(rightPanel).hasClassName('sottomenu_off')){
        $(rightPanel).removeClassName('sottomenu_off');
        $(rightPanel).hide();
    }

    if($(rightPanel).visible()) {
       $(rightPanel).hide();
    }
    else {
        $(rightPanel).show();
    }
    if(fileExtension == 'flv') {
        var flvPlayer = 'flvplayer';
        $(flvPlayer).innerHTML = "<object id=\"flowplayer\" width=\"500\" height=\"370\" data=\"../external/mediaplayer/flowplayer/flowplayer.swf\""
                               + "type=\"application/x-shockwave-flash\">"
                               + "<param name=\"movie\" value=\"../external/mediaplayer/flowplayer/flowplayer.swf\" />"
                               + "<param name=\"allowfullscreen\" value=\"true\" />"
                               + "<param name=\"flashvars\" value='config={\"clip\":{\"url\":\""+httpFilePath +"\", \"autoPlay\":false, \"autoBuffering\":true}}' />"
                               + "</object>";

    }else {
        var flvPlayer = 'flvplayer';
        $(flvPlayer).innerHTML = "<embed src=\""
                                 + httpFilePath
                                 + "\" controls=\"smallconsole\" width=\"500\" height=\"370\" loop=\"false\" autostart=\"true\">";
    }
}