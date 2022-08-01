/*
 * Vimeo Video PLUGIN 1.0 FOR FCKeditor 2.x
 * Copyright (C) 2008 VINCENZO PUCARELLI http://www.ollie10.it
 * Copyright (C) 2022 GIORGIO CONSORTI <g.consorti@lynxlab.com>
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 */

var oEditor		= window.parent.InnerDialogLoaded() ;
var FCK			= oEditor.FCK ;
var FCKLang		= oEditor.FCKLang ;
var FCKConfig	= oEditor.FCKConfig ;

//SECURITY REGULAR EXPRESSION
var REG_SCRIPT = new RegExp("< *script.*>|< *style.*>|< *link.*>|< *body .*>|< *object .*>|< *url .*>", "i");
var REG_PROTOCOL = new RegExp("javascript:|vbscript:|about:", "i");
var REG_CALL_SCRIPT = new RegExp("&\{.*\};", "i");
var REG_EVENT = new RegExp("onError|onUnload|onBlur|onFocus|onClick|onMouseOver|onMouseOut|onSubmit|onReset|onChange|onSelect|onAbort", "i");
var REG_AUTH = new RegExp("document\.cookie|Microsoft\.XMLHTTP", "i");
var REG_NEWLINE = new RegExp("\x0d|\x0a", "i");


// SET THE DIALOG TAB
window.parent.AddTab('Info', oEditor.FCKLang.DlgInfoTab);

// GET THE SELECTED EMBED IF AVAILABLE
var oFakeImage = FCK.Selection.GetSelectedElement() ;
var oEmbed ;

var videoUrl, videoUrlSplitted, eEmbed;
videoUrl = null;

window.onload = function()
{
	// Translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage(document);

	window.parent.SetAutoSize(true);

	// Activate the "OK" button.
	window.parent.SetOkButton(true);
}

//THE OK BUTTON WAS HIT
function Ok()
{
	if ( GetE('txtUrl').value.length == 0 )
	{
		window.parent.SetSelectedTab( 'Info' ) ;
		GetE('txtUrl').focus() ;

		alert( oEditor.FCKLang.DlgAlertVimeoCode ) ;

		return false ;
	}

	// CHECK SECURITY WITH REGULAR EXPRESSIONS
	if (checkCode(GetE('txtUrl').value.trim()) == false) {
		alert( oEditor.FCKLang.DlgAlertVimeoSecurity ) ;
		return false;
	}

	if(GetE('txtUrl').value.trim().split("vimeo.com").length == 2)
		SetVimeoVideo();

	if(videoUrl == null)
	{
		alert( oEditor.FCKLang.DlgAlertVimeoSecurity ) ;
		return false;
	}

	oEditor.FCKUndo.SaveUndoStep() ;
	FCK.InsertElement(oEmbed);
	FCK.InsertElement(FCK.EditorDocument.createElement('p'));
	return true ;
}

//CHECK FOR INVALID WORDS
function checkCode(code)
{
	if(code.search(REG_SCRIPT) != -1)
		return false;

	if(code.search(REG_PROTOCOL) != -1)
		return false;

	if (code.search(REG_CALL_SCRIPT) != -1)
		return false;

	if (code.search(REG_EVENT) != -1)
		return false;

	if (code.search(REG_AUTH) != -1)
		return false;

	if (code.search(REG_NEWLINE) != -1)
		return false;
}

// SET VIMEO OBJECT
function SetVimeoVideo()
{
	// url will be like: https://vimeo.com/711166775/5ccf73ce49
	// url will be like: https://vimeo.com/709148963/74e4f88ad7
	videoUrl = GetE('txtUrl').value.trim();
	videoUrlSplitted = videoUrl.split('vimeo.com');
	videoUrl = "https://player.vimeo.com/video/";
	videoUrlOpts = "&badge=0&autopause=0&player_id=0&app_id=58479";
	videoParts = videoUrlSplitted.pop().replace(/^\//, '').split("/");
	videoUrl += videoParts[0] + "?h=" + videoParts[1] + videoUrlOpts;
	title = window.top.document.getElementById('name').value;

	container = FCK.EditorDocument.createElement('div');
	SetAttribute(container, "class", "vimeo-container");

	div = FCK.EditorDocument.createElement('div');
	SetAttribute(div, "style", "padding:56.25% 0 0 0;position:relative;");
	container.append(div);

	iframe = FCK.EditorDocument.createElement('iframe');
	SetAttribute(iframe, "src", videoUrl);
	SetAttribute(iframe, "frameborder", "0");
	SetAttribute(iframe, "allow", "autoplay; fullscreen; picture-in-picture");
	SetAttribute(iframe, "allowfullscreen", "");
	SetAttribute(iframe, "style", "position:absolute;top:0;left:0;width:100%;height:100%;");
	if ('undefined' !== typeof title) {
		SetAttribute(iframe, "title", title);
	}
	div.append(iframe);

	script = FCK.EditorDocument.createElement('script');
	SetAttribute(script, "src", "//player.vimeo.com/api/player.js");
	container.append(script);

	oEmbed = container;
}
