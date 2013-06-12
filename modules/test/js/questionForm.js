function hide_tipologia_cloze() {
	var e = $j('select[name="'+tipologia_field+'"]');

	disable = true;
	if (e.val() == ADA_CLOZE_TEST_TYPE) {
		disable = false;
	}

	var li = $j('select[name="'+cloze_field+'"]').closest('li');
	if (disable) {
		li.addClass('hidden');
	}
	else {
		li.removeClass('hidden');
	}
}

hide_tipologia_cloze();