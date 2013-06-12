document.write('<script type="text/javascript" src="js/commons.js"></script>');

function toggleRepeatable(id_history_test,repeatable) {
	var loc = window.location.pathname;
	var dir = loc.substring(0, loc.lastIndexOf('/'));
	$j.ajax({
		url: dir+'/tutor_ajax.php?mode=repeatable&id_history_test='+id_history_test+'&repeatable='+repeatable,
		dataType: 'text',
		async: false,
		success: function(data) {
			if (data == '1') {
				if (repeatable) {
					$j('#repeateTestYes').attr('checked',true);
					$j('#repeateTestNo').removeAttr('checked');
				}
				else {
					$j('#repeateTestYes').removeAttr('checked');
					$j('#repeateTestNo').attr('checked',true);
				}
			}
			else {
				if (!repeatable) {
					$j('#repeateTestYes').attr('checked',true);
					$j('#repeateTestNo').removeAttr('checked');
				}
				else {
					$j('#repeateTestYes').removeAttr('checked');
					$j('#repeateTestNo').attr('checked',true);
				}
			}
		},
		error: function() {
			if (!repeatable) {
				$j('#repeateTestYes').attr('checked',true);
				$j('#repeateTestNo').removeAttr('checked');
			}
			else {
				$j('#repeateTestYes').removeAttr('checked');
				$j('#repeateTestNo').attr('checked',true);
			}
		}
	});
}

function saveOpenAnswerPoints(id_answer) {
	points = parseInt($j('#open_answer_test_point_input_'+id_answer).val());
	if (isNaN(points)) {
		points = 0;
	}

	var loc = window.location.pathname;
	var dir = loc.substring(0, loc.lastIndexOf('/'));
	$j.ajax({
		url: dir+'/tutor_ajax.php?mode=points&id_answer='+id_answer+'&points='+points,
		dataType: 'text',
		async: false,
		success: function(data) {
			if (data == '1') {
				$j('#open_answer_test_point_span_'+id_answer).html(points);
				$j('#open_answer_test_point_input_'+id_answer).val(points);
			}
		}
	});
}

function saveCorrectOpenAnswer(id_answer) {
	var textarea = $j('#open_answer_test_point_textarea_'+id_answer);	

	var loc = window.location.pathname;
	var dir = loc.substring(0, loc.lastIndexOf('/'));
	$j.ajax({
		type: "POST",
		data: { answer: textarea.val() },
		url: dir+'/tutor_ajax.php?mode=answer&id_answer='+id_answer,
		dataType: 'text',
		async: false,
		beforeSend: function() {
			textarea.addClass('ajaxloading');
		},
		complete: function() {
			textarea.removeClass('ajaxloading');
		},
		success: function(data) {
			var cssClass;
			if (data == '1') {
				cssClass = 'right_answer_test_bg';
			}
			else {
				cssClass = 'wrong_answer_test_bg';
			}
			textarea.addClass(cssClass);
			setTimeout(function () {
				textarea.removeClass(cssClass);
			}, 2500);
		},
		error: function() {
			var cssClass = 'wrong_answer_test_bg';
			textarea.addClass(cssClass);
			setTimeout(function () {
				textarea.removeClass(cssClass);
			}, 2500);
		}
	});
}

function saveCommentAnswer(id_answer) {
	var textarea = $j('#tutor_comment_textarea_'+id_answer);
	var checkbox = $j('#tutor_comment_checkbox_'+id_answer);
	var checked = (checkbox.is(':checked'));

	var loc = window.location.pathname;
	var dir = loc.substring(0, loc.lastIndexOf('/'));
	$j.ajax({
		type: "POST",
		data: { comment: textarea.val(), notify: checked },
		url: dir+'/tutor_ajax.php?mode=comment&id_answer='+id_answer,
		dataType: 'text',
		async: false,
		beforeSend: function() {
			textarea.addClass('ajaxloading');
		},
		complete: function() {
			textarea.removeClass('ajaxloading');
		},
		success: function(data) {
			var cssClass;
			if (data == '1') {
				cssClass = 'right_answer_test_bg';
			}
			else {
				cssClass = 'wrong_answer_test_bg';
			}
			textarea.addClass(cssClass);
			setTimeout(function () {
				textarea.removeClass(cssClass);
			}, 2500);
		},
		error: function() {
			var cssClass = 'wrong_answer_test_bg';
			textarea.addClass(cssClass);
			setTimeout(function () {
				textarea.removeClass(cssClass);
			}, 2500);
		}
	});
}

function toggleDiv(id) {
	$j('#'+id).toggle();
}