function disable_didascalia(disable) {
	var li = $j('#'+field).closest('li');
	if (disable) {
		li.addClass('hidden');
		$j('#l_'+field).removeClass('error');
		for(i=0;i<validateContentFields_questionForm.length;i++) {
			if (validateContentFields_questionForm[i] == field) {
				validateContentFields_questionForm.splice(i,1);
				validateContentRegexps_questionForm.splice(i,1);
			}
		}
	}
	else {
		li.removeClass('hidden');
		validateContentFields_questionForm.push(field);
		validateContentRegexps_questionForm.push(regexp);
	}
}

var commento = $j('input[name="'+commento_field+'"]');
var commento_value = 0;
for(i=0;i<commento.length;i++) {
	switch(parseInt($j(commento[i]).val())) {
		case 0:
			$j(commento[i]).click(function () {
				disable_didascalia(true);
			});
		break;
		case 1:
			$j(commento[i]).click(function () {
				disable_didascalia(false);
			});
		break;
	}

	if ($j(commento[i]).attr('checked')) {
		commento_value = $j(commento[i]).val();
	}
}

if (commento_value == 1) {
	disable_didascalia(false);
}
else {
	disable_didascalia(true);
}

function FCKeditor_OnComplete( fckEditor )
{
	if (fckEditor.Name == 'testo') {
		if (fckEditor.ToolbarSet.Name == 'Cloze') {
			fckEditor.Config.EnterMode = 'div';
		}

		template_file = module_http+'/template/template.css';
		fckEditor.Config.EditorAreaCSS += ','+template_file;
		css = '<link rel="stylesheet" type="text/css" href="'+template_file+'">';
		fckEditor.EditorDocument.head.innerHTML+= css;
		

		$j('#'+injectTemplate_field+'_button').click(function () {
			var value = $j('#'+injectTemplate_field).val();
			if (value == undefined || value == '') {
				fckEditor.EditorDocument.body.innerHTML = '';
			}
			else {
				fckEditor.EditorDocument.body.innerHTML = '';
				fckEditor.InsertHtml(value);
			}
		});
	}
}