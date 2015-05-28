function hide_livello(disable) {
	var li = $j('#'+livello_field).closest('li');
	if (disable) {
		li.removeClass('hidden');
	}
	else {
		li.addClass('hidden');
	}
}

function hide_correttezza(disable) {
	var li = $j('#'+correttezza_field).closest('li');
	if (disable) {
		li.removeClass('hidden');
//		$j('#'+correttezza_field).val(0);
		$j('#l_'+correttezza_field).removeClass('error');
		if ('undefined' != typeof validateContentFields_topicForm) {
			for(i=0;i<validateContentFields_topicForm.length;i++) {
				if (validateContentFields_topicForm[i] == correttezza_field) {
					validateContentFields_topicForm.splice(i,1);
					validateContentRegexps_topicForm.splice(i,1);
				}
			}
		}
	}
	else {
		li.addClass('hidden');
		$j('#'+correttezza_field).val('0.00');
		if ('undefined' != typeof validateContentFields_topicForm) {
			validateContentFields_topicForm.push(correttezza_field);
			validateContentRegexps_topicForm.push(correttezza_regexp);
		}
	}
}

var barriera = $j('input[name="'+barriera_field+'"]');
var barriera_value = 0;
for(i=0;i<barriera.length;i++) {
	switch(parseInt($j(barriera[i]).val())) {
		case 0:
			$j(barriera[i]).click(function () {
				hide_livello(false);
				hide_correttezza(false);
			});
		break;
		case 1:
			$j(barriera[i]).click(function () {
				hide_livello(true);
				hide_correttezza(true);
			});
		break;
	}

	if ($j(barriera[i]).attr('checked')) {
		barriera_value = $j(barriera[i]).val();
	}
}

if (barriera_value == 1) {
	hide_livello(true);
	hide_correttezza(true);
}
else {
	hide_livello(false);
	hide_correttezza(false);
}