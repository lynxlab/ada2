document.write('<script type="text/javascript" src="../../external/fckeditor/fckeditor.js"></script>');

function loadFCKeditor(textarea_name, toolbar) {
	if ($j('#'+textarea_name).size() == 1) {
		toolbar = (typeof toolbar === 'undefined') ? 'Test' : toolbar;

		var oFCKeditor = new FCKeditor( textarea_name );
		oFCKeditor.BasePath = '../../external/fckeditor/';
		oFCKeditor.Width = '100%';
		oFCKeditor.Height = '300';
		oFCKeditor.ToolbarSet = toolbar;
		oFCKeditor.ReplaceTextarea();
	}
}

var isCloze = false;
document.observe('dom:loaded', function() {
	var max_width = parseInt($j('div.fform.form').css('width'));
	$j('select.form').css('max-width', max_width+'px');
	loadFCKeditor('consegna');
	setTimeout(function () {
		if (isCloze) {
			loadFCKeditor('testo', 'Cloze');
		}
		else {
			loadFCKeditor('testo');
		}
	},500);
});