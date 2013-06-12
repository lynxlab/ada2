var ada_media = 'ada_media';

var oEditor = window.parent.InnerDialogLoaded();
var vWidth = "375";
var vHeight = "250";

function fileBrowser(){
	window.SetUrl=function(value) {
		document.getElementById('value').value=value;
		window.parent.SetOkButton( true );
	};
	var filemanager='../../filemanager/browser/default/browser.html';
	var connector='../../connectors/php/connector.php';
	window.open(filemanager+'?Connector='+connector,'fileupload','modal,width=600,height=400');
}

function OnLoad() {
	oEditor.FCKLanguageManager.TranslatePage( document );
	window.parent.SetAutoSize( true );

	var oNode = getoNode();
	if (oNode) {
		var type = oNode.getAttribute('type');
		var value = oNode.getAttribute('title');
		var title = oNode.getAttribute('rel');
		var width = oNode.getAttribute('width');
		var height = oNode.getAttribute('height');


		var select = document.getElementById('type');
        for (var i = 0; i < select.options.length; i++)
        {
            if (select.options[i].value == type)
            {
                select.selectedIndex = i;
				toggleSize(select);
                break;
            }
        }

		document.getElementById('rel').value = title;
		document.getElementById('value').value = value;
		document.getElementById('width').value = width;
		document.getElementById('height').value = height;

		window.parent.SetOkButton( true );
	}
}

function Ok() {
	var oNode = getoNode();

	var select = document.getElementById('type');
	var type = select.options[select.selectedIndex].value;
	var title = document.getElementById('rel').value;
	var value = document.getElementById('value').value;
	var width = document.getElementById('width').value;
	var height = document.getElementById('height').value;

	if (type == '' || value == '') return false;

	switch(type) {
		case MEDIA_IMAGE:
			src = HTTP_ROOT_DIR+'/layout/'+ADA_TEMPLATE_FAMILY+'/img/media_img.png';
		break;
		case MEDIA_SOUND:
			src = HTTP_ROOT_DIR+'/layout/'+ADA_TEMPLATE_FAMILY+'/img/media_audio.png';
		break;
		case MEDIA_VIDEO:
			src = HTTP_ROOT_DIR+'/layout/'+ADA_TEMPLATE_FAMILY+'/img/media_video.png';
		break;
		/*
		case MEDIA_LINK:
			src = HTTP_ROOT_DIR+'/layout/'+ADA_TEMPLATE_FAMILY+'/img/media_video.png';
		break;
		case MEDIA_DOC:
			src = HTTP_ROOT_DIR+'/layout/'+ADA_TEMPLATE_FAMILY+'/img/media_video.png';
		break;
		case MEDIA_EXE:
			src = HTTP_ROOT_DIR+'/layout/'+ADA_TEMPLATE_FAMILY+'/img/media_video.png';
		break;
		*/
		default:
			return false;
		break;
	}

	if (oNode) {
		oNode.setAttribute('src',src);
		oNode.setAttribute('type',type);
		oNode.setAttribute('title',value);
		oNode.setAttribute('rel',title);
		if (type != MEDIA_VIDEO) {
			oNode.removeAttribute('width');
			oNode.removeAttribute('height');
		}
		else {
			oNode.setAttribute('width',width);
			oNode.setAttribute('height',height);
		}
	}
	else {
		var tag = '<img title="'+value+'" type="'+type+'" alt="'+ada_media+'" src="'+src+'"';
		if (title != '') {
			tag+= ' rel="'+title+'"';
		}
		if (width != '' && type === MEDIA_VIDEO) {
			tag+= ' width="'+width+'"';
		}
		if (height != '' && type === MEDIA_VIDEO) {
			tag+= ' height="'+height+'"';
		}
		tag+= ' />';
		oEditor.FCK.InsertHtml(tag);
	}
	return true;
}

function toggleSize(e) {
	var element = document.getElementById("only_video");
	if (e.options[e.selectedIndex].value === MEDIA_VIDEO) {
		element.style.display = 'block';
	}
	else {
		element.style.display = 'none';
	}
}

function getoNode() {
	var notFound = false;
	if( oEditor.FCKBrowserInfo.IsIE) {
		notFound = oEditor.FCK.Selection.HasAncestorNode('img');
	}
	else {
		notFound = oEditor.FCK.Selection.HasAncestorNode('IMG');
	}

	if (notFound) {
		return false;
	}

	if(oEditor.FCKBrowserInfo.IsIE) {
		oNode = oEditor.FCK.Selection.MoveToAncestorNode('img');	// lower-case!!!!!!! I don't know why, but it's working!!!
	}
	else {
		oNode = oEditor.FCK.Selection.MoveToAncestorNode('IMG');
	}

	if (oNode != null) {
		if (oNode.getAttribute('alt') == ada_media) {
			return oNode;
		}
	}
	return false;
}