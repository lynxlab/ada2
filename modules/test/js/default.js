document.write("<script type='text/javascript' src='../../js/include/basic.js'></script>");
document.write("<script type='text/javascript' src='../../js/include/menu_functions.js'></script>");

function zeroFill( number, width )
{
  width -= number.toString().length;
  if ( width > 0 )
  {
    return new Array( width + (/\./.test( number ) ? 2 : 1) ).join( '0' ) + number;
  }
  return number + ""; // always return a string
}

function toggleVisibilityByDiv(className, mode)
{
	$$('ul.'+className).each( function(e) {
		if (!$(e).empty()) {
			toggleVisibilityByClassName(className, e.id, mode);
		}
	});
}
