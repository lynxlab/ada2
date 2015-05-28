document.write('<script type="text/javascript" src="../../external/fckeditor/fckeditor.js"></script>');
var name_dragdrop_prefix = 'titolo_dragdrop';
var titolo_dragropd_prefix = name_dragdrop_prefix + '_';

function loadFCKeditor(textarea_name, toolbar) {
	if ($j('#'+textarea_name).size() == 1) {
		toolbar = (typeof toolbar === 'undefined') ? 'Test' : toolbar;

		var oFCKeditor = new FCKeditor( textarea_name );
		oFCKeditor.BasePath = '../../external/fckeditor/';
		oFCKeditor.Width = '100%';		
		/**
		 * giorgio, must check if we are dealing with a test
		 * if yes the height of the textarea is reduced
		 * 
		 * Two choices are given, as 07/10/2013 the uncommented look the best
		 */		
		// var isTest =  (location.pathname.indexOf('edit_test.php') != -1);
		var isTest = (location.search.indexOf('mode=test') != -1); 
		oFCKeditor.Height = (isTest) ? '150' : '300';
		oFCKeditor.ToolbarSet = toolbar;
		oFCKeditor.ReplaceTextarea();
	}
}

function findBoxTitleMaxId()
{
	var maxId = 0;
	// get maxid if titolo_dragdrop_* elements
	$j('[id^="'+ titolo_dragropd_prefix +'"]').each(function(){
		maxId = Math.max(maxId, parseInt($j(this).attr('id').replace(titolo_dragropd_prefix,'')) );
	});
	return maxId;	
}

function removeLastBoxTitleElement()
{
	var maxId = findBoxTitleMaxId();
	if (maxId>1)
	{
		var theText = FCKeditorAPI.GetInstance('testo').GetXHTML(false);
		if (theText.indexOf('table="'+maxId+'"') != -1) alert ("Ci sono tag cloze associati al box da cancellare, correggerli!");
		else $j('#'+ titolo_dragropd_prefix + maxId).closest('li').remove();
	}
	else alert ("Deve esserci almeno un box");
}


function addBoxTitleElement()
{
	var name_dragdrop_prefix = 'titolo_dragdrop';
	var titolo_dragropd_prefix = name_dragdrop_prefix + '_';
	var maxId = findBoxTitleMaxId();
	// get the html of the parent li of the found element, includes the li itself
	// by cloning, wrapping it around a div and getting the parent html
	var sourceObj = $j('#'+ titolo_dragropd_prefix + maxId).closest('li'); 
	var thehtml = sourceObj.clone().wrapAll('<div></div>').parent().html();
	// build up the html to be appended by replacing
	// the titolo_dragdrop_prefix of the found maxId with ++maxId
	// and convertingn it to a jQuery object by calling parseHTML
	thehtml = thehtml.replace ( new RegExp ('#' + (maxId)+'.')   , '#' + (maxId+1)+'.' );
	thehtml = thehtml.replace ( new RegExp (name_dragdrop_prefix+'\\['+maxId+'\\]')   , name_dragdrop_prefix+'['+(maxId+1)+']' );
	thehtml = thehtml.replace ( new RegExp ( titolo_dragropd_prefix + maxId, 'g'), titolo_dragropd_prefix + (++maxId) );
	var jQueryObj = $j( $j.parseHTML (thehtml) );
	// sets newly created form input value to empty string
    jQueryObj.children('input').attr('value','');
    sourceObj.parent().append(jQueryObj);
}

var isCloze = false;
document.observe('dom:loaded', function() {
	var max_width = parseInt($j('div.fform.form').css('width'));
	$j('select.form').css('max-width', max_width+'px');
	loadFCKeditor('consegna');
	loadFCKeditor('consegna_success');
	loadFCKeditor('consegna_error');
	setTimeout(function () {
		if (isCloze) {
			loadFCKeditor('testo', 'Cloze');
		}
		else {
			loadFCKeditor('testo');
			loadFCKeditor('didascalia-field');
			loadFCKeditor('stimolo-field');
		}
	},500);
});