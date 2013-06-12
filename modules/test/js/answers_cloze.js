document.write('<script type="text/javascript" src="js/commons.js"></script>');

function showAnswers(id) {
	if (domReady) {
		$j('#'+id).dialog('open');
	}
}

var domReady = false;
$j(function() {
	domReady = true;

	var dialog_buttons = {};
	dialog_buttons[i18n['confirm']] = function() {
		$j(this).find('form')[0].submit();
	};
	dialog_buttons[i18n['cancel']] =  function() {
		if (deleted) {
			location.reload();
		}
		else {
			$j(this).find('form')[0].reset();
			$j(this).dialog('close');
		}
	};

	$j('.dialog').dialog({
		autoOpen: false,
		height: 'auto',
		width: 900,
		modal: true,
		buttons: dialog_buttons,
		close: dialog_buttons[i18n['cancel']]
	});	

	$j('.dragdropBox img:not(.noPopup)').off('click');

	$j('.clozePopup').each(function(i,e) {
		var order = $j(e).attr('title').split('_');
		$j(e).attr('rel','ordine'+order[1]);
		$j(e).click(function() {
			var id = $j(e).attr('rel');
			showAnswers(id);
		});
	});
});
