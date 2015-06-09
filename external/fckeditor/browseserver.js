// File Picker modification for FCK Editor v2.0 - www.fckeditor.net
// by: Pete Forde <pete@unspace.ca> @ Unspace Interactive
// modifications: giorgio <g.consorti@lynxlab.com>

/**
 * Assuming that you have the FCK Editor in your sites'
 * tree, you can just reference this file on your
 * page, and call the File Picker like so:
 * <button type="button" onclick="BrowserServer('txtId');">Pick Image</button>
 * <input type="text" id="txtId"/>
 */

var urlobj;
var oWindow;

function BrowseServer(obj)
{	
	urlobj = obj;	
	OpenServerBrowser(
		HTTP_ROOT_DIR + '/external/fckeditor/editor/filemanager/browser/default/browser.html?Type=Media&Connector='+
		HTTP_ROOT_DIR +'/external/fckeditor/editor/filemanager/connectors/php/connector.php',
		screen.width * 0.7,
		screen.height * 0.7 ) ;
}

function OpenServerBrowser( url, width, height )
{
	var iLeft = (screen.width  - width) / 2 ;
	var iTop  = (screen.height - height) / 2 ;

	var sOptions = "toolbar=no,status=no,resizable=no,location=no,dependent=yes" ;
	sOptions += ",width=" + width ;
	sOptions += ",height=" + height ;
	sOptions += ",left=" + iLeft ;
	sOptions += ",top=" + iTop ;

	oWindow = window.open( url, "BrowseWindow", sOptions ) ;
}

function SetUrl( url, width, height, alt )
{
	var BASE_DOMAIN = HTTP_ROOT_DIR; 	
	var lastSlash = BASE_DOMAIN.lastIndexOf('/');
	if (lastSlash > new String("http://").length ) {
		BASE_DOMAIN = HTTP_ROOT_DIR.substring(0,lastSlash);
	}
	
	document.getElementById(urlobj).value = BASE_DOMAIN + url ;
	try {
	  document.getElementById(urlobj).onchange();
	}
	catch(err) {
	}
	oWindow = null;
}
