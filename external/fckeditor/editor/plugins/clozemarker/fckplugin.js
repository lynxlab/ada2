/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 *    http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 *    http://www.fckeditor.net/
 * 
 * File Name: fckplugin.js
 * 
 */
FCKCommands.RegisterCommand('ClozeMarker', new FCKDialogCommand( 'ClozeMarker', FCKLang['ClozeDlgTitle'], FCKPlugins.Items['clozemarker'].Path + 'clozemarker.html', 340, 300 ) );

// Create the "Cloze" toolbar button.
var oClozeItem = new FCKToolbarButton( 'ClozeMarker', FCKLang['ClozeBtn'] ) ;
oClozeItem.IconPath = FCKPlugins.Items['clozemarker'].Path + 'clozemarker.gif' ;
FCKToolbarItems.RegisterItem( 'ClozeMarker', oClozeItem ) ;

// The object used for all Cloze operations.
var FCKCloze = new Object();

FCKCloze.title = 1;

document.createElement('cloze');

// Insert a new Cloze
FCKCloze.Insert = function(val) {
	if (val == undefined) {
		val = FCKCloze.title;		
	}

	var hrefStartHtml	= '<cloze title="'+val+'">';
	var hrefEndHtml		= '</cloze>';

	mySelection = FCKSelection.GetSelectedHTML();
	if (mySelection.html.length > 0) {
		hrefHtml = hrefStartHtml+mySelection.html+hrefEndHtml;
		hrefHtml = ProtectTags(hrefHtml) ; // needed because cloze is a custom tag and browser tends to breaks it.

		this.markSelection(val, mySelection.html);
	}
};

FCKCloze.markSelection = function(title, content) {
	var sel, range;
    if (FCK.EditorWindow.getSelection) {
        sel = FCK.EditorWindow.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
			range = FCK.EditorWindow.getSelection().getRangeAt(0);
        }
    } else if (FCK.EditorDocument.selection && FCK.EditorDocument.selection.createRange) {
		range = FCK.EditorDocument.selection.createRange();
    }

	if (range != undefined) {
		range.deleteContents();
		range.collapse(true);
		var el = FCK.EditorDocument.createElement('cloze');
		el.title = title;
		el.innerHTML = content;
		range.insertNode(el);
	}
};

FCKCloze.Redraw = function () {
	if (FCK.EditorDocument) {
		var clozeTags = FCK.EditorDocument.getElementsByTagName('cloze');
		if (clozeTags != null && clozeTags.length > 0) {
			var max_title = 0;
			for(var i = 0; i<clozeTags.length; i++) {
				var el = clozeTags[i];
				var ord = parseInt(el.getAttribute('title'));
				if (ord > max_title) {
					max_title = ord;
				}
			}
			FCKCloze.title = max_title+1;
		}
		else {
			FCKCloze.title = 1;
		}
		return max_title;
	}
};

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

function removeBR(input) {							/* Used with Gecko */
	var output = '';
	for (var i = 0; i < input.length; i++) {
		if ((input.charCodeAt(i) == 13) && (input.charCodeAt(i + 1) == 10)) {
			i++;
			output += ' ';
		}
		else {
			output += input.charAt(i);
   		}
	}
	return output;
}

function ProtectTags ( html ) { // copied from _source/internals/fck.js in fckeditor 2.4
	// IE doesn't support <cloze> and it breaks it. Let's protect it.
	if ( FCKBrowserInfo.IsIE ) {
		var sTags = 'CLOZE' ;

		var oRegex = new RegExp( '<(' + sTags + ')([ \>])', 'gi' ) ;
		html = html.replace( oRegex, '<FCK:$1$2' ) ;

		oRegex = new RegExp( '<\/(' + sTags + ')>', 'gi' ) ;
		html = html.replace( oRegex, '<\/FCK:$1>' ) ;
	}
	return html ;
}

// put it into the contextmenu (optional)
FCK.ContextMenu.RegisterListener({
	AddItems : function( menu, tag, tagName ) {
		// the command needs the registered command name, the title for the context menu, and the icon path
		menu.AddItem( 'ClozeMarker', FCKLang['Cloze'], oClozeItem.IconPath ) ;
	}
});

FCK.Events.AttachEvent( 'OnAfterSetHTML', FCKCloze.Redraw);