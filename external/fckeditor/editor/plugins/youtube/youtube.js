/*
 * YouTube, GoogleVideo, MySpace Video PLUGIN 1.0 FOR FCKeditor 2.x
 * Copyright (C) 2008 VINCENZO PUCARELLI http://www.ollie10.it
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

// GET THE SELECTED FLASH EMBED IF AVAILABLE
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

		alert( oEditor.FCKLang.DlgAlertYouTubeCode ) ;

		return false ;
	}
	
	// CHECK SECURITY WITH REGULAR EXPRESSIONS
	if (checkCode(GetE('txtUrl').value) == false) {
		alert( oEditor.FCKLang.DlgAlertYouTubeSecurity ) ;
		return false;
	}
    
	if(GetE('txtUrl').value.indexOf("youtube") > 0)
		SetYoutubeVideo();

	else if(GetE('txtUrl').value.indexOf("video.google") > 0)
		SetGoogleVideo();

	if(GetE('txtUrl').value.indexOf("myspace") > 0)
		SetMySpaceVideo();

	if(videoUrl == null)
	{
		alert( oEditor.FCKLang.DlgAlertYouTubeSecurity ) ;
		return false;
	}
	
	oEditor.FCKUndo.SaveUndoStep() ;

	if (!oFakeImage)
	{
		oFakeImage = oEditor.FCKDocumentProcessor_CreateFakeImage('FCK__Flash', oEmbed);
		oFakeImage.setAttribute('_fckflash', 'true', 0);
		oFakeImage = FCK.InsertElement(oFakeImage);
	}

	oEditor.FCKFlashProcessor.RefreshView( oFakeImage, oEmbed ) ;
	return true ;
}

//GET THE FORM INSERTED URL
function getSourceUrl()
{
	var yt = GetE('txtUrl').value;
	var begin = yt.indexOf('src=\"') + 5;
    var end = yt.indexOf('\"', begin);
    return yt.substring(begin, end);
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

// SET YOUTUBE OBJECT
function SetYoutubeVideo()
{
	videoUrl = GetE('txtUrl').value;
	videoUrlSplitted = videoUrl.split("v=");
	videoUrl = null;
	
	for(var i=0; i<=videoUrlSplitted.length-1; i++)
	{
		if(videoUrlSplitted[i].indexOf("watch") > 0)
			videoUrl = videoUrlSplitted[i+1];
	}

	eEmbed = FCK.EditorDocument.createElement('embed');
	SetAttribute(eEmbed, "src", "http://www.youtube.com/v/" + videoUrl + "&hl=it");
	SetAttribute(eEmbed, "type" , "application/x-shockwave-flash");
	SetAttribute(eEmbed, "wmode", "transparent");
	SetAttribute(eEmbed, "width" , 425);
	SetAttribute(eEmbed, "height", 355);
	oEmbed = eEmbed;
}

// SET GOOGLEVIDEO OBJECT
function SetGoogleVideo()
{
	videoUrl = GetE('txtUrl').value;
	videoUrlSplitted = videoUrl.split("docid=");
	videoUrl = null;
	
	for(var i=0; i<=videoUrlSplitted.length-1; i++)
	{
		if(videoUrlSplitted[i].indexOf("videoplay") > 0)
			videoUrl = videoUrlSplitted[i+1];
	}

	eEmbed = FCK.EditorDocument.createElement('embed');
	SetAttribute(eEmbed, "src", "http://video.google.it/googleplayer.swf?docid=" + videoUrl);
	SetAttribute(eEmbed, "type" , "application/x-shockwave-flash");
	SetAttribute(eEmbed, "wmode", "transparent");
	SetAttribute(eEmbed, "id", "VideoPlayback");
	SetAttribute(eEmbed, "style", "width:400px;height:326px;");
	SetAttribute(eEmbed, "width" , 400);
	SetAttribute(eEmbed, "height", 326);
	oEmbed = eEmbed;
}

// SET MYSPACE OBJECT
function SetMySpaceVideo()
{
	videoUrl = GetE('txtUrl').value;
	videoUrlSplitted = videoUrl.split("videoid=");
	videoUrl = null;
	
	for(var i=0; i<=videoUrlSplitted.length-1; i++)
	{
		if( (videoUrlSplitted[i].indexOf("myspacetv.com") > 0) || (videoUrlSplitted[i].indexOf("fuseaction.com") > 0))
			videoUrl = videoUrlSplitted[i+1];
	}

	eEmbed = FCK.EditorDocument.createElement('embed');
	SetAttribute(eEmbed, "src", "http://lads.myspace.com/videos/vplayer.swf");
	SetAttribute(eEmbed, "type" , "application/x-shockwave-flash");
	SetAttribute(eEmbed, "wmode", "transparent");
	SetAttribute(eEmbed, "flashvars", "m=" + videoUrl + "&v=2&type=video");
	SetAttribute(eEmbed, "width" , 430);
	SetAttribute(eEmbed, "height", 346);
	oEmbed = eEmbed;
}

