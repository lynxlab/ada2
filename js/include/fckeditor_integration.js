/**
  fckeditor_integration.js

  The javascript needed for the integration of FCKeditor into ADA.
  The following functions manage switching between wysywig editing and pure-text editing
  and provide an integration of FCKeditor with ADA node-editing needs.
  We are using prototype to access DOM elements.

  .@author vito
 */

/**
 *
 *
 */

var EDITING_FORM = 'jseditor_form';

var FCKEDITOR_INSTANCE_NAME = 'jsdata_fckeditor';
var FCKEDITOR_DIV = 'jsfckeditor_div';
//
// Variable for extended node
//
var FCKEDITOR_INSTANCE_HYPHENATION = 'jsdata_fckeditor_hyphenarea';
var FCKEDITOR_INSTANCE_GRAMMAR = 'jsdata_fckeditor_grammararea';
var FCKEDITOR_INSTANCE_SEMANTIC = 'jsdata_fckeditor_semanticarea';
var FCKEDITOR_INSTANCE_NOTES = 'jsdata_fckeditor_notesarea';
var FCKEDITOR_INSTANCE_EXAMPLES = 'jsdata_fckeditor_examplesarea';
//
// End variable for extended node
//

//var FCKEDITOR_TOOLBAR_BUTTONS = new Array('Bold', 'Italic', 'OrderedList',
//		'UnorderedList');

var FCKEDITOR_TOOLBAR_BUTTONS = new Array('Bold','Italic','Underline',
		'StrikeThrough','Subscript','Superscript',
		'OrderedList','UnorderedList','Outdent','Indent','Blockquote',
		'JustifyLeft','JustifyCenter','JustifyRight','JustifyFull',
		'Cut','Copy','Paste','PasteText','PasteWord');

var PURE_TEXT_EDITOR = 'jsdata_textarea';
//
// Variable for extended node
//
var PURE_HYPHENATION_EDITOR = 'jsdata_hyphenarea';
var PURE_GRAMMAR_EDITOR = 'jsdata_grammararea';
var PURE_SEMANTIC_EDITOR = 'jsdata_semanticarea';
var PURE_NOTES_EDITOR = 'jsdata_notesarea';
var PURE_EXAMPLES_EDITOR = 'jsdata_examplesarea';
//
// End variable for extended node
//

var PURE_TEXT_EDITOR_DIV = 'jstextarea_div';

var ADA_MEDIA_BUTTONS = 'jsbuttons';
var ADA_MEDIA_DIV = 'jsaddons';
var ADA_MEDIA_INPUT = 'jsnode_data_div';

var NODE_DATA_DIV = 'jsnode_data_div';
var EXTERNAL_LINK_DIV = 'jsid_divle';
var EXTERNAL_LINK_INPUT_NAME = 'jsid_textle';
var EXTERNAL_LINK_BUTTON = 'jsexternal_link';
var EXTERNAL_LINK_SELECTOR = 'jsid_select_external_links';

var INTERNAL_LINK_DIV = 'jsid_divli';
var INTERNAL_LINK_BUTTON = 'jsinternal_link';

var MULTIMEDIA_DIV = 'jsid_divfu';
var MULTIMEDIA_BUTTON = '';
var FILE_UPLOAD_ERROR_DIV = 'jserror_file_upload';

var AUTHOR_FILES_SELECTOR = 'jsid_select_files';
var PARENT_NODE_DIV = 'jsparent_node_selector';
var PARENT_NODE = 'jsparent_id';
var TEXT_PARENT_NODE_ID = 'jsparent_node_text';

var EDITOR_ID_BUTTON_PREFIX = 'jsbutton_for';
var EDITOR_BUTTON_UNSELECTED_CLASSNAME = 'unselected';
var EDITOR_BUTTON_SELECTED_CLASSNAME = 'selected';

var ADDONS = new Array(EXTERNAL_LINK_DIV, INTERNAL_LINK_DIV, MULTIMEDIA_DIV,
		NODE_DATA_DIV);
/*
 * per le icone da visualizzare nell'editor
 */
//var FCKEDITOR_ICONS_PATH         = HTTP_ROOT_DIR + '/img/media_icons/';
var DEFAULT_TEMPLATE_FAMILY = 'standard';
//var TEMPLATE_FAMILY              = DEFAULT_TEMPLATE_FAMILY;
var STYLESHEET_DIR = '/layout/';
var STYLESHEET_IMG_DIR = '/img/'
var FCKEDITOR_ICONS_PATH = HTTP_ROOT_DIR + STYLESHEET_DIR
		+ DEFAULT_TEMPLATE_FAMILY + STYLESHEET_IMG_DIR;

//var FCKEDITOR_ICONS_PATH         = '/ada_cvs_settembre/img/media_icons/';
var FCKEDITOR_ICON_IMAGE = '_img.png';
var FCKEDITOR_ICON_IMAGE_MONTESSORI = '_img_montessori.png';
var FCKEDITOR_ICON_AUDIO = '_audio.png';
var FCKEDITOR_ICON_AUDIO_PRONOUNCE = '_audio_pronounce.png';
var FCKEDITOR_ICON_VIDEO = '_video.png';
var FCKEDITOR_ICON_VIDEO_LIS = '_video_lis.png';
var FCKEDITOR_ICON_VIDEO_LABIALE = '_video_labiale.png';
var FCKEDITOR_ICON_VIDEO_FINGER_SPELLING = '_video_finger_spelling.png';
var FCKEDITOR_ICON_DOC = '_doc.png';
var FCKEDITOR_ICON_INTERNAL_LINK = '_linka.png';
var FCKEDITOR_ICON_EXTERNAL_LINK = '_linkext.png';
var FCKEDITOR_ICON_DEFAULT = FCKEDITOR_ICON_DOC;

/*
 * tipi di media in ADA
 * MEDIA_* vars are defined include/PHPjavascript.php
 */
var ADA_MEDIA_IMAGE = MEDIA_IMAGE;
var ADA_MEDIA_AUDIO = MEDIA_SOUND;
var ADA_MEDIA_VIDEO = MEDIA_VIDEO;
var ADA_MEDIA_LINK = MEDIA_LINK;
var ADA_MEDIA_DOC = MEDIA_DOC;
var ADA_MEDIA_EXE = MEDIA_EXE;
var ADA_MEDIA_INTERNAL_LINK = "INTERNAL"; // this is 7 in PHPjavascript, looks like 7 is not used...??!? 
var ADA_MEDIA_EXTERNAL_LINK = MEDIA_LINK;
var ADA_MEDIA_IMAGE_MONTESSORI = MEDIA_MONTESSORI;
var ADA_MEDIA_VIDEO_LIS = MEDIA_LIS;
var ADA_MEDIA_VIDEO_LABIALE = MEDIA_LABIALE;
var ADA_MEDIA_VIDEO_FINGER_SPELLING = MEDIA_FINGER_SPELLING;
var ADA_MEDIA_AUDIO_PRONOUNCE = MEDIA_PRONOUNCE;

var ADA_MEDIA_IMAGE_LABEL = "image";
var ADA_MEDIA_AUDIO_LABEL = "audio";
var ADA_MEDIA_VIDEO_LABEL = "video";
var ADA_MEDIA_LINK_LABEL = "link";
var ADA_MEDIA_DOC_LABEL = "document";
var ADA_MEDIA_EXE_LABEL = "exe";
var ADA_MEDIA_INTERNAL_LINK_LABEL = "INTERNAL";
var ADA_MEDIA_EXTERNAL_LINK_LABEL = "external link";
var ADA_MEDIA_IMAGE_MONTESSORI_LABEL = "Montessori";
var ADA_MEDIA_VIDEO_LIS_LABEL = "video LIS";
var ADA_MEDIA_VIDEO_LABIALE_LABEL = "video Labiale";
var ADA_MEDIA_VIDEO_FINGER_SPELLING_LABEL = "video Spelling";
var ADA_MEDIA_AUDIO_PRONOUNCE_LABEL = "audio pronucia";
/*
 * Max value for preview image
 */
var MAXH = 200;
var MAXW = 200;

/*
 * per il gestore dei click sull'indice del corso
 */
var SET_PARENT_NODE = 1;
var ADD_INTERNAL_LINK = 0;

var ADA_LEAF_TYPE = 0;
var ADA_GROUP_TYPE = 1;
var ADA_LEAF_ICON = 'nodo.png';
var ADA_GROUP_ICON = 'gruppo.png';

var NODE_ICON_ID = 'icon';

/*
 * codici di errore restituiti dal modulo upload.php
 */
var ADA_FILE_UPLOAD_ERROR_UPLOAD = 101;
var ADA_FILE_UPLOAD_ERROR_MIMETYPE = 102;
var ADA_FILE_UPLOAD_ERROR_FILESIZE = 103;
var ADA_FILE_UPLOAD_ERROR_UPLOAD_PATH = 104;

/*
 * Path for media
 */
var SAVE_MEDIA_URL   = HTTP_ROOT_DIR + '/services/media_manager.php';
var READ_MEDIA_URL   = HTTP_ROOT_DIR + '/services/media_manager.php?op=read';

/**
 * function createEditor
 *
 * creates and returns an instance of FCKeditor.
 * .@param FCKeditorID - the id of the textarea to be replaced by FCKeditor
 * .@return oFCKeditor - FCKeditor instance
 */
function createEditor(FCKeditorID, Plain_textID) {
	$(FCKeditorID).value = ADAToFCKeditor($(Plain_textID).value);

	var oFCKeditor = new FCKeditor(FCKeditorID);
        oFCKeditor.BasePath = '../external/fckeditor/';
        oFCKeditor.Width = '100%';
	oFCKeditor.Height = '350';
	oFCKeditor.ToolbarSet = 'Basic';

	oFCKeditor.ReplaceTextarea();

	return oFCKeditor;
}

// The FCKeditor_OnComplete function is a special function called everytime an
// editor instance is completely loaded and available for API interactions.
function FCKeditor_OnComplete(FCKEDITOR_INSTANCE_NAME) {
	// Enable the switch button. It is disabled at startup, waiting the editor to be loaded.
	if (document.getElementById('switch_to_adacode') != null) {
		document.getElementById('switch_to_adacode').disabled = false;
	}
	// hide MEDIA divs
	if ($(EXTERNAL_LINK_DIV) != null) {
		$(EXTERNAL_LINK_DIV).hide();
	}
	if ($(INTERNAL_LINK_DIV) != null) {
		$(INTERNAL_LINK_DIV).hide();
	}
	if ($(MULTIMEDIA_DIV) != null) {
		$(MULTIMEDIA_DIV).hide();
	}
	if ($(PARENT_NODE_DIV) != null) {
		$(PARENT_NODE_DIV).hide();
	}
}

/*
 *	FUNCTIONS USED TO SWITCH BETWEEN TEXTAREA AND FCKEDITOR
 */

/**
 * function switchToFCKeditor
 *
 * Switches to FCKeditor: here you can add in a wysiwyg way
 * all the media required by a node.
 */
function switchToFCKeditor(template_family) {
	if (typeof (template_family) != 'undefined'
			&& template_family != DEFAULT_TEMPLATE_FAMILY) {
		updateFCKEditorIconsPath(template_family);
	}

	if (typeof (FCKeditorAPI) == 'undefined') {
		oFCKeditor = createEditor(FCKEDITOR_INSTANCE_NAME, PURE_TEXT_EDITOR);
                if ($(PURE_HYPHENATION_EDITOR) != undefined) {
                    oFCKeditor_hyphen = createEditor(FCKEDITOR_INSTANCE_HYPHENATION, PURE_HYPHENATION_EDITOR);
                    oFCKeditor_semantic = createEditor(FCKEDITOR_INSTANCE_SEMANTIC, PURE_SEMANTIC_EDITOR);
                    oFCKeditor_grammar = createEditor(FCKEDITOR_INSTANCE_GRAMMAR, PURE_GRAMMAR_EDITOR);
                    oFCKeditor_notes = createEditor(FCKEDITOR_INSTANCE_NOTES, PURE_NOTES_EDITOR);
                    oFCKeditor_examples = createEditor(FCKEDITOR_INSTANCE_EXAMPLES, PURE_EXAMPLES_EDITOR);
                }
	} else {
		oFCKeditor = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_NAME);
		oFCKeditor.SetData(ADAToFCKeditor($(PURE_TEXT_EDITOR).value));
                if ($(PURE_HYPHENATION_EDITOR) != undefined) {
                    oFCKeditor_hyphen = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_HYPHENATION);
                    oFCKeditor_hyphen.SetData(ADAToFCKeditor($(PURE_HYPHENATION_EDITOR).value));
                    oFCKeditor_semantic = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_SEMANTIC);
                    oFCKeditor_semantic.SetData(ADAToFCKeditor($(PURE_SEMANTIC_EDITOR).value));
                    oFCKeditor_grammar = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_GRAMMAR);
                    oFCKeditor_grammar.SetData(ADAToFCKeditor($(PURE_GRAMMAR_EDITOR).value));
                    oFCKeditor_notes = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_NOTES);
                    oFCKeditor_notes.SetData(ADAToFCKeditor($(PURE_NOTES_EDITOR).value));
                    oFCKeditor_examples = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_EXAMPLES);
                    oFCKeditor_examples.SetData(ADAToFCKeditor($(PURE_EXAMPLES_EDITOR).value));
                }
	}

	$(PURE_TEXT_EDITOR_DIV).hide();
	$(ADA_MEDIA_INPUT).show();
	$(FCKEDITOR_DIV).show();
	$(ADA_MEDIA_BUTTONS).show();
	$(ADA_MEDIA_DIV).show();
}

function updateFCKEditorIconsPath(template_family) {
	FCKEDITOR_ICONS_PATH = HTTP_ROOT_DIR + STYLESHEET_DIR + template_family
			+ STYLESHEET_IMG_DIR;
}
/**
 * function switchToADACode
 *
 * Switches to ADA text editing: here you can manually add MEDIA tags.
 */
function switchToADACode() {
	updateADACode();

	$(PURE_TEXT_EDITOR_DIV).show();
	$(ADA_MEDIA_INPUT).hide();
	$(FCKEDITOR_DIV).hide();
	$(ADA_MEDIA_BUTTONS).hide();
	$(ADA_MEDIA_DIV).hide();
}
/**
 * function updateADACode
 *
 * used to keep updated wizzywig content and its ADACode counterpart.
 */
function updateADACode() {
	if (typeof (FCKeditorAPI) == 'undefined') {
		oFCKeditor = createEditor(FCKEDITOR_INSTANCE_NAME, PURE_TEXT_EDITOR);
                if ($(PURE_HYPHENATION_EDITOR) != undefined) {
                    oFCKeditor_hyphen = createEditor(FCKEDITOR_INSTANCE_HYPHENATION, PURE_HYPHENATION_EDITOR);
                    oFCKeditor_semantic = createEditor(FCKEDITOR_INSTANCE_SEMANTIC, PURE_SEMANTIC_EDITOR);
                    oFCKeditor_grammar = createEditor(FCKEDITOR_INSTANCE_GRAMMAR, PURE_GRAMMAR_EDITOR);
                    oFCKeditor_notes = createEditor(FCKEDITOR_INSTANCE_NOTES, PURE_HYPHENATION_NOTES);
                    oFCKeditor_examples = createEditor(FCKEDITOR_INSTANCE_EXAMPLES, PURE_EXAMPLES_EDITOR);
                }
	} else {
		oFCKeditor = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_NAME);
                if ($(PURE_HYPHENATION_EDITOR) != undefined) {
                    oFCKeditor_hyphen = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_HYPHENATION);
                    oFCKeditor_semantic = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_SEMANTIC);
                    oFCKeditor_grammar = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_GRAMMAR);
                    oFCKeditor_notes = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_NOTES);
                    oFCKeditor_examples = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_EXAMPLES);
                }
	}
	$(PURE_TEXT_EDITOR).value = FCKeditorToADA(oFCKeditor.GetXHTML());
        if ($(PURE_HYPHENATION_EDITOR) != undefined) {
            $(PURE_HYPHENATION_EDITOR).value = FCKeditorToADA(oFCKeditor_hyphen.GetXHTML());
            $(PURE_SEMANTIC_EDITOR).value = FCKeditorToADA(oFCKeditor_semantic.GetXHTML());
            $(PURE_GRAMMAR_EDITOR).value = FCKeditorToADA(oFCKeditor_grammar.GetXHTML());
            $(PURE_NOTES_EDITOR).value = FCKeditorToADA(oFCKeditor_notes.GetXHTML());
            $(PURE_EXAMPLES_EDITOR).value = FCKeditorToADA(oFCKeditor_examples.GetXHTML());
        }
}
/*
 *	FUNCTIONS USED TO CONVERT ADA MEDIA TAGS IN IMG TAGS (in both directions)
 */

function ADAMediaTagToImgTag(matched_string, type, value) {
	type = parseInt(type);

	icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_DEFAULT;

	if (type == ADA_MEDIA_IMAGE) {
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_IMAGE;
	}
	if (type == ADA_MEDIA_IMAGE_MONTESSORI) {
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_IMAGE_MONTESSORI;
	}

	if (type == ADA_MEDIA_AUDIO) {
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_AUDIO;
	}
	if (type == ADA_MEDIA_AUDIO_PRONOUNCE) {
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_AUDIO_PRONOUNCE;
	}

	if (type == ADA_MEDIA_VIDEO) {
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO;
	}
	if (type == ADA_MEDIA_VIDEO_LIS) {
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO_LIS;
	}
	if (type == ADA_MEDIA_VIDEO_LABIALE) {
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO_LABIALE;
	}
	if (type == ADA_MEDIA_VIDEO_FINGER_SPELLING) {
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO_FINGER_SPELLING;
	}

	if (type == ADA_MEDIA_DOC) {
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_DOC;
	}

	if (type == ADA_MEDIA_EXTERNAL_LINK) {
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_EXTERNAL_LINK;
	}

	return '<img title="' + value + '" alt="" src="' + icon + '" />';
}

function ADALinkTagToImgTag(matched_string, type, value) {
	icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_DEFAULT;

	if (type == ADA_MEDIA_INTERNAL_LINK) {
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_INTERNAL_LINK;
	}

	var string = '<img title="' + value + '" alt="" src="' + icon + '" />';
	return string;
}

/**
 * function ADAToFCKeditor
 *
 * used to convert ADA <media> tags into <img> tags.
 * .@param input_text - textarea content
 * .@return output - parsed text with replaced tags
 */

function ADAToFCKeditor(input_text) {
	var media_tag = /<MEDIA TYPE="([0-9]+)" VALUE="([a-zA-Z0-9_\-\/\.?~+%=&,$'():;*@\[\]]+)">/g;

	// vito, 27 mar 2009
	//var internal_link_tag = /<LINK TYPE="(INTERNAL)" VALUE="([0-9]+_[0-9]+)">/g;
	var internal_link_tag = /<LINK TYPE="(INTERNAL)" VALUE="([0-9]+)">/g;

	var output_text = input_text.replace(media_tag, ADAMediaTagToImgTag);

	output_text = output_text.replace(internal_link_tag, ADALinkTagToImgTag);
	return output_text;
}

function ImgTagToADATag(matched_string, title, src) {
	icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_DEFAULT;
	tag = 'MEDIA';
	type = ADA_MEDIA_EXTERNAL_LINK;

	if (src == FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_IMAGE) {
		type = ADA_MEDIA_IMAGE;
	}
	if (src == FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_IMAGE_MONTESSORI) {
		type = ADA_MEDIA_IMAGE_MONTESSORI;
	}

	if (src == FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_AUDIO) {
		type = ADA_MEDIA_AUDIO;
	}
	if (src == FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_AUDIO_PRONOUNCE) {
		type = ADA_MEDIA_AUDIO_PRONOUNCE;
	}

	if (src == FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO) {
		type = ADA_MEDIA_VIDEO;
	}
	if (src == FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO_LIS) {
		type = ADA_MEDIA_VIDEO_LIS;
	}
	if (src == FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO_LABIALE) {
		type = ADA_MEDIA_VIDEO_LABIALE;
	}
	if (src == FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO_FINGER_SPELLING) {
		type = ADA_MEDIA_VIDEO_FINGER_SPELLING;
	}

	if (src == FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_DOC) {
		type = ADA_MEDIA_DOC;
	}

	if (src == FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_INTERNAL_LINK) {
		type = ADA_MEDIA_INTERNAL_LINK;
		tag = 'LINK';
	}

	if (src == FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_EXTERNAL_LINK) {
		type = ADA_MEDIA_EXTERNAL_LINK;
	}
	return '<' + tag + ' TYPE="' + type + '" VALUE="' + title + '">';
}

function RevImgTagToADATag(matched_string, src, title) {
	return ImgTagToADATag(matched_string, title, src);
}

/**
 * function FCKeditorToADA
 *
 * used to convert <img> tags into ADA <media> tags. Because of FCKeditor handling of tag attributes,
 * we need to parse text for <img title src alt /> and <img alt src title /> :(
 * .@param input_text - textarea content
 * .@return output - parsed text with replaced tags
 */
function FCKeditorToADA(input_text) {
	var image_tag = /<img title="([a-zA-Z0-9_\-\/\.?~+%=&,$'():;*@\[\]]+)" alt="" src="([-a-zA-Z0-9_\/:\.]+)" \/>/g;
	var rev_image_tag = /<img src="([-a-zA-Z0-9_\/:\.]+)" alt="" title="([a-zA-Z0-9_\-\/\.?~+%=&,$'():;*@\[\]]+)" \/>/g;

	var output_text = input_text.replace(image_tag, ImgTagToADATag);

	output_text = output_text.replace(rev_image_tag, RevImgTagToADATag);
	return output_text;
}

/*
 *	FUNCTIONS USED TO ADD ADA MEDIA IN FCKEDITOR
 */
function toggleVisibility(element) {
	$(element).toggle();
}

function addExternalLink() {
	var external_link_div = $(EXTERNAL_LINK_DIV);
	var text_input = $(EXTERNAL_LINK_INPUT_NAME);
	var oFCKeditor = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_NAME);

	//vito, 3 giugno 2009
	var external_link = cleanExternalLink(text_input.value);

	/*
	 * if the link is typed correctly, add it
	 */
	if (validateExternalLink(external_link)) {
		oFCKeditor.InsertHtml('<img title="' + external_link + '" alt="" src="'
				+ FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_EXTERNAL_LINK + '" />');
		addToExternalLinkSelector(ADA_MEDIA_EXTERNAL_LINK, external_link);
		// vito, 10 giugno 2009
		//external_link_div.hide();
		text_input.clear();
		// fine di vito, 10 giugno 2009

		if($('jsinvalid_external_link')) {
			$('jsinvalid_external_link').remove();
		}
	}
	/*
	 * show an alert?
	 */
	else {
		if(!$('jsinvalid_external_link')) {
			var div = new Element('div',{'id':'jsinvalid_external_link','class':'error'}).update('link non valido');
			$('span_insert_external_link_text').insert(div);
		}
	}

}

function addToExternalLinkSelector(adatype, external_link) {
	var external_links_in = $(EXTERNAL_LINK_SELECTOR).childElements();
	var already_in = external_links_in.find( function(element) {
		return (element.value == adatype + '|' + external_link);
	});
	if (typeof (already_in) == 'undefined') {
		$(EXTERNAL_LINK_SELECTOR).insert(
				'<option value="' + adatype + '|' + external_link
						+ '">[LINK ESTERNO] ' + external_link + '</option>');
	}
}

function addInternalLink(node) {
	// vito, 27 mar 2009, we only need the idnode part of the node string.
	var splitted_string = node.split('_');
	var idnode = splitted_string[1];

	var internal_link_div = $(INTERNAL_LINK_DIV);
	var oFCKeditor = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_NAME);
	oFCKeditor.InsertHtml('<img title="' + idnode + '" alt="" src="'
			+ FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_INTERNAL_LINK + '" />');
	// vito, 10 giugno 2009
	//internal_link_div.hide();
}

function addMultimedia(file_to_add) {
	var filename = file_to_add[1];
	var adafiletype = file_to_add[0];

	if (filename != null && adafiletype != null) {
		var add_multimedia_div = $(MULTIMEDIA_DIV);
		icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_DEFAULT;

		if (adafiletype == ADA_MEDIA_IMAGE) {
			icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_IMAGE;
		}
		if (adafiletype == ADA_MEDIA_IMAGE_MONTESSORI) {
			icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_IMAGE_MONTESSORI;
		}

		if (adafiletype == ADA_MEDIA_AUDIO) {
			icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_AUDIO;
		}
		if (adafiletype == ADA_MEDIA_AUDIO_PRONOUNCE) {
			icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_AUDIO_PRONOUNCE;
		}

		if (adafiletype == ADA_MEDIA_VIDEO) {
			icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO;
		}
		if (adafiletype == ADA_MEDIA_VIDEO_LIS) {
			icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO_LIS;
		}
		if (adafiletype == ADA_MEDIA_VIDEO_LABIALE) {
			icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO_LABIALE;
		}
		if (adafiletype == ADA_MEDIA_VIDEO_FINGER_SPELLING) {
			icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_VIDEO_FINGER_SPELLING;
		}

		if (adafiletype == ADA_MEDIA_DOC) {
			icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_DOC;
		}

		if (adafiletype == ADA_MEDIA_INTERNAL_LINK) {
			icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_INTERNAL_LINK;
		}

		if (adafiletype == ADA_MEDIA_EXTERNAL_LINK) {
			icon = FCKEDITOR_ICONS_PATH + FCKEDITOR_ICON_EXTERNAL_LINK;
		}

		var oFCKeditor = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_NAME);
		oFCKeditor.InsertHtml('<img title="' + filename + '" alt="" src="'
				+ icon + '" />');

		// corregge segnalazione #80
		//add_multimedia_div.hide();
	}
}

/*
 * Used to select an option in a select field to passed data
 */
function select_option(field, data) {
    var options = $$('select#'+field+' option');
    var len = options.length;

    for(var i=0;i<len;i++){
        if(options[i].value == data) {
            options[i].selected = true;
            //$(field).selectIndex = i;
            return i;
       }
    }
/*
 * Vito Version
 *
    var value = data;
    var alreadySelected = false;

    var selectedOption = $('field').select('option[selected]');
    if (selectedOption.length > 0) {
        if (selectedOption[0].readAttribute('value') == value) {
            alreadySelected = true;
        } else {
            selectedOption[0].removeAttribute('selected');
        }
    }

    if (!alreadySelected) {
        var options = $('field').select('option[value="' + value + '"]');
        if (options.length > 0) {
           if (!options[0].hasAttribute('selected')) {
                options[0].writeAttribute('selected');
            }
        }
    }
 */
}

/*
 * Used to calculate the new dimension of an image in order to resize an image
 */
    function resize_image(img, maxh, maxw , id_img) {
      imgObj = new Image();
      imgObj.src = img;
//      var imgObj = $(id_img);
      var ratio = maxh/maxw;
      if (imgObj.height/imgObj.width > ratio){
         // height is the problem
        if (imgObj.height > maxh){
          imgObj.width = Math.round(imgObj.width*(maxh/imgObj.height));
          imgObj.height = maxh;
        }
      } else {
        // width is the problem
        if (imgObj.width > maxh){
          imgObj.height = Math.round(imgObj.height*(maxw/imgObj.width));
          imgObj.width = maxw;
        }
      }
      newDimension = new Array(imgObj.height, imgObj.width);
      return newDimension;
      //$(id_img) = imgObj.src;
    }

function updateMediaManager() {
    var author_id = $F('id_node_author');
    var media = getFileDataFromSelect('jsid_select_files');
    if ($('jsid_div_media_properties').visible()) {
        toggleVisibility('jsid_div_media_properties');
    }
//    $('jsid_div_media_properties').hidden;
    manageMultimediaProperties(media, author_id);
 //   alert("cambiato:" + author_id + " " + media);
}


/*
 * Used to show media properties reading from DB
 */
function manageMultimediaProperties(media, author_id) {
	if (media[1] == undefined) {
		return false;
	}

    var filename = media[1];
    var adafiletype = media[0];
    $('p_selected_media_extended_type').options.length = 0;
    if (!$('jsid_div_media_properties').visible()) {
        numeric_adaFileType = parseInt(adafiletype);
        $('preview_media').update();
        switch (numeric_adaFileType) {
            case ADA_MEDIA_IMAGE:
            case ADA_MEDIA_IMAGE_MONTESSORI:
                var imgsrc = HTTP_ROOT_DIR + '/services/media/' + author_id + '/' + filename;
                var newDimensionImage = resize_image(imgsrc, MAXH, MAXW, 'img_preview');
                var width =  newDimensionImage[1];
                var height =  newDimensionImage[0];
//                var preview_image = '<img src=\"' + imgsrc + '\" id=\"img_preview\">';
                var preview_image = new Element('img', {src: imgsrc, id: "img_preview", width:width, height:height});
//                var preview_image = '<img src=\"' + imgsrc + '\" id=\"img_preview\" \" width=\"' + width + '\" height=\"' + height +'\">';
                $('preview_media').insert(preview_image);
//                var newDimensionImage = resize_image(imgsrc, MAXH, MAXW, 'img_preview');
//                new Effect.Scale($('img_preview'), 30);
                $('p_selected_media_extended_type').insert(new Element('option', {value: ADA_MEDIA_IMAGE}).update(ADA_MEDIA_IMAGE_LABEL));
                $('p_selected_media_extended_type').insert(new Element('option', {value: ADA_MEDIA_IMAGE_MONTESSORI}).update(ADA_MEDIA_IMAGE_MONTESSORI_LABEL));

/*var ADA_MEDIA_IMAGE_LABEL = "image";
var ADA_MEDIA_AUDIO_LABEL = "audio";
var ADA_MEDIA_VIDEO_LABEL = "video";
var ADA_MEDIA_LINK_LABEL = "link";
var ADA_MEDIA_DOC_LABEL = "document";
var ADA_MEDIA_EXE_LABEL = "exe";
var ADA_MEDIA_INTERNAL_LINK_LABEL = "INTERNAL";
var ADA_MEDIA_EXTERNAL_LINK_LABEL = "external link";
var ADA_MEDIA_IMAGE_MONTESSORI_LABEL = "Montessori";
var ADA_MEDIA_VIDEO_LABIALE_LABEL = "video Labiale";
var ADA_MEDIA_VIDEO_FINGER_SPELLING_LABEL = "video Spelling";
var ADA_MEDIA_AUDIO_PRONOUNCE_LABEL = "audio pronucia";
*/
                break;
            case ADA_MEDIA_AUDIO:
            case ADA_MEDIA_AUDIO_PRONOUNCE:
                $('p_selected_media_extended_type').insert(new Element('option', {value: ADA_MEDIA_AUDIO}).update(ADA_MEDIA_AUDIO_LABEL));
                $('p_selected_media_extended_type').insert(new Element('option', {value: ADA_MEDIA_AUDIO_PRONOUNCE}).update(ADA_MEDIA_AUDIO_PRONOUNCE_LABEL));
                break;
            case ADA_MEDIA_VIDEO:
            case ADA_MEDIA_VIDEO_LABIALE:
            case ADA_MEDIA_VIDEO_FINGER_SPELLING:
            case ADA_MEDIA_VIDEO_LIS:
                $('p_selected_media_extended_type').insert(new Element('option', {value: ADA_MEDIA_VIDEO}).update(ADA_MEDIA_VIDEO_LABEL));
                $('p_selected_media_extended_type').insert(new Element('option', {value: ADA_MEDIA_VIDEO_LABIALE}).update(ADA_MEDIA_VIDEO_LABIALE_LABEL));
                $('p_selected_media_extended_type').insert(new Element('option', {value: ADA_MEDIA_VIDEO_FINGER_SPELLING}).update(ADA_MEDIA_VIDEO_FINGER_SPELLING_LABEL));
                $('p_selected_media_extended_type').insert(new Element('option', {value: ADA_MEDIA_VIDEO_LIS}).update(ADA_MEDIA_VIDEO_LIS_LABEL));
                break;
        }
        new Ajax.Request(READ_MEDIA_URL, {
                    method: 'Post',
                    parameters: {nome_file:filename, id_utente:author_id},
                    onComplete: function(transport) {
                            var json = transport.responseText.evalJSON(true);

                            //if (GET_AJAX_REQUEST_EXECUTION_TIME)
                            //{
                            //        var response_time = Date.now();
                            //}
                        if (typeof(json.id_risorsa_ext) != undefined )
                        {
                            $('jsdata_titlesarea').value = json.titolo;
                            $('jsdata_keywordsarea').value = json.keywords;
                            $('jsdata_descriptionarea').value = json.descrizione;
                            if (json.pubblicato == 1) {
                                $('jsdata_published').checked = true;
                            }else {
                                $('jsdata_published').checked = false;
                            }
//                            alert($('p_selected_language').options[json.lingua]);
                            select_option('p_selected_language',json.lingua);
                            select_option('p_selected_media_extended_type',json.tipo);
                        }
                        else
                        {
                                alert('json error: ' + json.error)
                        }

                    },
                    onFailure: function() {
                            alert('save media', null);
                    }
            });

    }

    toggleVisibility('jsid_div_media_properties');
}

function saveMultimediaProperties(media, id_author) {
    var keywords = $F('jsdata_keywordsarea');
    var title = $F('jsdata_titlesarea');
    var description = $F('jsdata_descriptionarea');
    var language = getFileDataFromSelect('p_selected_language');
    var published = $F('jsdata_published');
    var media = getFileDataFromSelect('jsid_select_files');
    var filename = media[1];
    //var adafiletype = media[0];
    var adafiletype = getFileDataFromSelect('p_selected_media_extended_type');
//    var copyright = $F(copyright);
//    var id_risorsa_ext = $F(id_risorsa_ext);

    new Ajax.Request(SAVE_MEDIA_URL, {
		method: 'Post',
		parameters: {keywords:keywords, titolo:title, nome_file:filename, tipo:adafiletype,
                    id_utente:id_author, descrizione:description, pubblicato:published, lingua:language},
		onComplete: function(transport) {
			var json = transport.responseText.evalJSON(true);

			if (GET_AJAX_REQUEST_EXECUTION_TIME)
			{
				var response_time = Date.now();
			}

		    if ( json.error == 0 )
		    {
				alert('save completed');
			}
			else
			{
                                alert('json error: ' + json.error)
			}

		},
		onFailure: function() {
			alert('save media', null);
		}
	});


}

/**
 * used to switch between preview and save mode
 * DA SISTEMARE!!!
 */

function getAction(action_return_to_edit_node, action_save_edited_node) {
	switch (document.pressed) {
	case 'modify':
		document.preview_form.action = HTTP_ROOT_DIR
				+ action_return_to_edit_node;
		break;
	case 'save':
		document.preview_form.action = HTTP_ROOT_DIR + action_save_edited_node;
		break;
	}
}

/**
 * used to disable all of the controls on the editor page during a file upload.
 *
 */
function enterUploadFileState() {
	document.getElementById('uploadfile').target = 'upload_results';

	/*
	 * disable everything on node editing page until file has been uploaded
	 */

	//disable form
	$(EDITING_FORM).disable();

	var oFCKeditor = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_NAME);

	// disable fckeditor text editing
	if (document.all) {
		oFCKeditor.EditorDocument.body.disabled = true;
	} else {
		oFCKeditor.EditorDocument.designMode = 'off';
	}

	// disable fckeditor toolbar
	FCKEDITOR_TOOLBAR_BUTTONS.each( function(button) {
		oFCKeditor.EditorWindow.parent.FCKToolbarItems.LoadedItems[button]
				.Disable();
	});

	// disable inserting internal link, external link and other files until current file is uploaded
	//$(INTERNAL_LINK_BUTTON).disable();
	//$(EXTERNAL_LINK_BUTTON).disable();
}

/**
 * used to enable all of the controls on the editor page after a file upload
 *
 */
function exitUploadFileState(error, filename, filetype) {
	/*
	 * enable previously disabled controls on node editing page
	 */
	//enable form
	$(EDITING_FORM).enable();

	var oFCKeditor = FCKeditorAPI.GetInstance(FCKEDITOR_INSTANCE_NAME);

	// enable fckeditor text editing
	if (document.all) {
		oFCKeditor.EditorDocument.body.disabled = false;
	} else {
		oFCKeditor.EditorDocument.designMode = 'on';
	}

	// enable fckeditor toolbar
	FCKEDITOR_TOOLBAR_BUTTONS.each( function(button) {
		oFCKeditor.EditorWindow.parent.FCKToolbarItems.LoadedItems[button]
				.Enable();
	});

	// enable inserting internal link, external link and other files
	//	$(INTERNAL_LINK_BUTTON).enable();
	//	$(EXTERNAL_LINK_BUTTON).enable();

	// if file uploaded, add its icon to fckeditor content
	if (!error) {
		file = new Array(filetype, filename);
		addMultimedia(file);
		// add uploaded file to author's file selector too
		addToAuthorFileSelector(filename, filetype);
	} else {
		printErrorMessage(error, filename);
	}
}

function printErrorMessage(error, filename) {
	//alert('Errore ' + error + ' durante l\'upload del file ' + filename);

	switch (error) {
	case ADA_FILE_UPLOAD_ERROR_UPLOAD:
		$(FILE_UPLOAD_ERROR_DIV).innerHTML = 'Il file non e\' stato copiato.';
		break;
	case ADA_FILE_UPLOAD_ERROR_MIMETYPE:
		$(FILE_UPLOAD_ERROR_DIV).innerHTML = 'Il formato del file inviato non e\' stato accettato.';
		break;
	case ADA_FILE_UPLOAD_ERROR_FILESIZE:
		$(FILE_UPLOAD_ERROR_DIV).innerHTML = 'La dimensione del file inviato supera quella massima consentita da ADA.';
		break;
	case ADA_FILE_UPLOAD_ERROR_UPLOAD_PATH:
		$(FILE_UPLOAD_ERROR_DIV).innerHTML = 'La cartella in cui scrivere il file non esite o non e\' scrivibile.';
		break;
	default:
		$(FILE_UPLOAD_ERROR_DIV).innerHTML = 'Errore nell\'upload del file.';
		break;
	}
}

function getFileDataFromSelect(id_select) {
	var f = $F(id_select);

	if (f == null) {
		return false;
	}
	else {
		return $F(id_select).split('|');
	}
}

function addToAuthorFileSelector(filename, filetype) {
	var hint;

	switch (filetype) {
	case ADA_MEDIA_IMAGE:
		hint = '[IMAGE]';
		break;
	case ADA_MEDIA_AUDIO:
		hint = '[AUDIO]';
		break;
	case ADA_MEDIA_VIDEO:
		hint = '[VIDEO]';
		break;
	case ADA_MEDIA_EXTERNAL_LINK:
		hint = '[LINK ESTERNO]';
		break;
	case ADA_MEDIA_DOC:
		hint = '[DOCUMENTO]';
		break;
	}

	$(AUTHOR_FILES_SELECTOR).insert(
			'<option value="' + filetype + '|' + filename + '">' + hint + ' '
					+ filename + '</option>');

	// vito, 10 giugno 2009
	$('id_multimedia').clear();
}

function executeAction(action, node) {
	if (action == '1') {
		setParentNode(node);
	} else if (action == '0') {
		addInternalLink(node);
	}
}

function setParentNode(node) {
	$(PARENT_NODE).value = node;
	$(TEXT_PARENT_NODE_ID).innerHTML = node;
}

function toggleMeHideOthers(addon) {
	var addons_to_hide = ADDONS.without(addon);
	addons_to_hide.each( function(a) {
		$(a).hide();
	});
	$(addon).toggle();
	//	alert('array: ' + buttons + ' pulsante selezionato: ' + button);
}

function showMeHideOthers(button, addon) {

	$('jsid_div_media_properties').hide();

	var addons_to_hide = ADDONS.without(addon);
	addons_to_hide
			.each( function(a) {
				$(a).hide();
				var button_name_for_a = EDITOR_ID_BUTTON_PREFIX
						+ $(a).identify();
				if ($(button_name_for_a).hasClassName(
						EDITOR_BUTTON_SELECTED_CLASSNAME)) {
					$(button_name_for_a).removeClassName(
							EDITOR_BUTTON_SELECTED_CLASSNAME);
					$(button_name_for_a).addClassName(
							EDITOR_BUTTON_UNSELECTED_CLASSNAME);
				}
			});

	if ($(button).hasClassName(EDITOR_BUTTON_UNSELECTED_CLASSNAME)) {
		$(button).removeClassName(EDITOR_BUTTON_UNSELECTED_CLASSNAME);
		$(button).addClassName(EDITOR_BUTTON_SELECTED_CLASSNAME);
	}

	if (!$(addon).visible()) {
		$(addon).show();
	}
}

function toggleVisibilityByClassName_OLD(container_div, item_class) {
	var children = $(container_div).select(
			'[id=' + container_div + item_class + ']');
	children.invoke('toggle');

	/*
	 * Get span element identifier for span element with title=container_div+item_class:
	 * since there is only one (if it exists) span element with this class name, it is safe
	 * to get its id in this way.
	 */
	var span_element_id = ($(container_div).select('[title=' + container_div
			+ item_class + ']')).first().identify();

	if ($(span_element_id).hasClassName('hideNodeChildren')) {
		$(span_element_id).update();
		$(span_element_id).insert('-');
		$(span_element_id).removeClassName('hideNodeChildren');
		$(span_element_id).toggleClassName('viewNodeChildren');
	} else if ($(span_element_id).hasClassName('viewNodeChildren')) {
		$(span_element_id).update();
		$(span_element_id).insert('+');
		$(span_element_id).removeClassName('viewNodeChildren');
		$(span_element_id).toggleClassName('hideNodeChildren');
	}

}

function toggleVisibilityByClassName(className, idName, mode)
{
	var children = $$('#'+idName+'.'+className);

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


function changeNodeIcon(type) {
	var node_type = $F(type);

	if (node_type == ADA_LEAF_TYPE) {
		$(NODE_ICON_ID).value = ADA_LEAF_ICON;
	} else if (node_type == ADA_GROUP_TYPE) {
		$(NODE_ICON_ID).value = ADA_GROUP_ICON;
	}
}
/**
 *
 * @param string
 * @return true if entered text is a valid url
 *         false otherwise
 */
function validateExternalLink(entered_text) {

	/*
	 * regular expression for url matching
	 *
	 * allowed_protocols = (?:http|https|ftp)
	 * separator         = (?::\/\/)
     * authentication    = (?:[a-z0-9]+(?::[a-z0-9]+)?@)
	 * domain_name       = (?:(?:[a-z0-9][a-z0-9\-_\[\]]*\.)+(?:aero|arpa|biz|com|cat|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel|mobi|[a-z]{2}))
	 * ipv4_address      = (?:[0-9]{1,3}(?:\.[0-9]{1,3}){3})
	 * ipv6_address      = (?:[0-9a-fA-F]{1,4}(?:\:[0-9a-fA-F]{1-4}){7}))
	 * port              = (?::[0-9]{1,5})
	 * directory         = (?:\/[a-z0-9_\-\.~+%=&,$'():;*@\[\]]*)*?(?:\/?[?a-z0-9+_\-\.\/%=&,$'():;*@\[\]]*)
	 * query             = (?:\/[a-z0-9_\-\.~+%=&,$'():;*@\[\]]*)*?(?:\/?[?a-z0-9+_\-\.\/%=&,$'():;*@\[\]]*)
	 * anchor            = (?:#[a-z0-9_\-\.~+%=&,$'():;*@\[\]]*)
	 *
	 * url_pattern = allowed_protocols separator authentication? (?:domain_name|ipv4_address|ipv6_address) port? directory? query? anchor?
	 */
	// al momento non e' presente la parte relativa ad  authentication
	var url_pattern = /^(?:http|https|ftp)(?::\/\/)(?:(?:(?:[a-z0-9][a-z0-9\-_\[\]]*\.)+(?:aero|arpa|biz|com|cat|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel|mobi|[a-z]{2}))|(?:[0-9]{1,3}(?:\.[0-9]{1,3}){3})|(?:[0-9a-fA-F]{1,4}(?:\:[0-9a-fA-F]{1-4}){7}))(?::[0-9]{1,5})?(?:\/[a-z0-9_\-\.~+%=&,$'():;*@\[\]]*)*?(?:\/?[?a-z0-9+_\-\.\/%=&,$'():;*@\[\]]*)?(?:#[a-z0-9_\-\.~+%=&,$'():;*@\[\]]*)?$/i;

	/*
	 * regular expression for mailto links matching
	 *
	 * username = (?:[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\'\[\]]+)
	 *
	 * mailto_pattern = username @ (?:domain_name|ipv4_address|ipv6_address)
	 */
	//var mailto_pattern = /^mailto:(?:[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\'\[\]]+)@(?:(?:(?:[a-z0-9][a-z0-9\-_\[\]]*\.)+(?:aero|arpa|biz|com|cat|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel|mobi|[a-z]{2}))|(?:[0-9]{1,3}(?:\.[0-9]{1,3}){3})|(?:[0-9a-fA-F]{1,4}(?:\:[0-9a-fA-F]{1-4}){7}))$/;

	/*
	 * checks if the entered text is a valid url or a valid mailto link and
	 * and returns true, otherwise returns false
	 */
	if (entered_text.match(url_pattern)) {
		return true;
	}
	//else if (entered_text.match(mailto_pattern)) {
	//	return true;
	//}

	return false;
}

/**
 *
 * @param entered_text
 * @return
 */
function cleanExternalLink(entered_text) {
	var protocol_pattern = /^(?:[a-z0-9][a-z0-9\.\-_]*)(?::\/\/)/i;

	/*
	 *  strip all leading and trailing whitespace from entered_text
	 */
	entered_text.strip();

	/*
	 * if no protocol is specified, try using http
	 */
	if (!entered_text.match(protocol_pattern)) {
		entered_text = 'http://'+entered_text;
	}

	return entered_text;
}
