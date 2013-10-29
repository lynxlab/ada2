FCKCommands.RegisterCommand(
    'media',
    new FCKDialogCommand(
        'Media',
		FCKLang['DlgMediaName'],
        FCKConfig.PluginsPath + 'media/media.php', 375, 300));
var oMediaItem = new FCKToolbarButton('media', FCKLang['DlgMediaName']);
oMediaItem.IconPath = FCKConfig.PluginsPath + 'media/media.png' ;
FCKToolbarItems.RegisterItem( 'Media', oMediaItem ) ;

/*
FCKSelection.GetSelectedHTML = function() {	// see http://www.quirksmode.org/js/selected.html for other browsers
	if( FCKBrowserInfo.IsIE) { // IE
		var oRange = FCK.EditorDocument.selection.createRange() ;
		//if an object like a table is deleted, the call to GetType before getting again a range returns Control
		switch ( this.GetType() ) {
			case 'Control':
				start = 1; //to be defined
				end = 2; //to be defined
				html = oRange.item(0).outerHTML;
			break;
			default:
				start = 1; //to be defined
				end = 2; //to be defined
				html = oRange.htmlText;
			break;
			case 'None' :
				start = -1;
				end = -1;
				html = '';
			break;
		}
	}
	else { // Mozilla, Safari, Chrome
		var oSelection = FCK.EditorWindow.getSelection();
		//Gecko doesn't provide a function to get the innerHTML of a selection,
		//so we must clone the selection to a temporary element and check that innerHTML
		var e = FCK.EditorDocument.createElement( 'DIV' );
		for ( var i = 0 ; i < oSelection.rangeCount ; i++ ) {
			e.appendChild( oSelection.getRangeAt(i).cloneContents() );
		}
		start = e.anchorOffset;
		end = e.focusOffset;
		html = removeBR(e.innerHTML);
	}

	return { "start": start, "end": end, "html": html };
};
*/