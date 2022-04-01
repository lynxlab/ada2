// vito, 28 nov 2008
document.write("<script type='text/javascript' src='../js/include/menu_functions.js'></script>");
// document.write("<script type='text/javascript' src='../js/include/tablekit/tablekit.js'></script>");
document.write("<script type='text/javascript' src='../js/include/basic.js'></script>");

// vito, 21 luglio 2008
function toggleVisibilityByClassName(container_div, item_class)
{
	//vito, 3 ottobre 2008
	//var children = $(container_div).select('[class='+item_class+']');
	var children = $(container_div).select('[id='+container_div+item_class+']');
	children.invoke('toggle');
	/*
	 * Get span element identifier for span element with title=container_div+item_class:
	 * since there is only one (if it exists) span element with this class name, it is safe
	 * to get its id in this way.
	 */
	var span_element_id = ($(container_div).select('[title='+container_div+item_class+']')).first().identify();

	if ($(span_element_id).hasClassName('hideNodeChildren'))
	{
		$(span_element_id).update();
		$(span_element_id).insert('-');
		$(span_element_id).removeClassName('hideNodeChildren');
		$(span_element_id).toggleClassName('viewNodeChildren');
	}
	else if ($(span_element_id).hasClassName('viewNodeChildren'))
	{
		$(span_element_id).update();
		$(span_element_id).insert('+');
		$(span_element_id).removeClassName('viewNodeChildren');
		$(span_element_id).toggleClassName('hideNodeChildren');
	}

}

function printit()
{
  if (typeof window.print == 'function') {
    window.print();
  }
}
