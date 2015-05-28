function disable_random_number(disable) {
	var li = $j('#'+field).closest('li');
	if (disable) {
		li.addClass('hidden');
		$j('#'+field).val(0);
		$j('#l_'+field).removeClass('error');
		for(i=0;i<validateContentFields_topicForm.length;i++) {
			if (validateContentFields_topicForm[i] == field) {
				validateContentFields_topicForm.splice(i,1);
				validateContentRegexps_topicForm.splice(i,1);
			}
		}
	}
	else {
		li.removeClass('hidden');
		validateContentFields_topicForm.push(field);
		validateContentRegexps_topicForm.push(regexp);
	}
}

var random = $j('input[name="'+random_field+'"]');
var random_value = 0;
for(i=0;i<random.length;i++) {
	switch(parseInt($j(random[i]).val())) {
		case 0:
			$j(random[i]).click(function () {
				disable_random_number(true);
			});
		break;
		case 1:
			$j(random[i]).click(function () {
				disable_random_number(false);
			});
		break;
	}

	if ($j(random[i]).attr('checked')) {
		random_value = $j(random[i]).val();
	}
}

if (random_value == 1) {
	disable_random_number(false);
}
else {
	disable_random_number(true);
}

function FCKeditor_OnComplete( fckEditor )
{
		template_file = module_http+'/template/template.css';
		fckEditor.Config.EditorAreaCSS += ','+template_file;
		css = '<link rel="stylesheet" type="text/css" href="'+template_file+'">';
		fckEditor.EditorDocument.head.innerHTML+= css;
}