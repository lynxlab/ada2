function initDoc() {
	const debug = false;
	$j('input[type="submit"]').hide().removeClass('form').addClass('ui teal button').show();
	$j('#paypal-ada-form input[type="hidden"]').each((index, el) => {
		if (debug) {
			console.log($j(el).attr('name'), $j(el).val(), $j(el).val().length);
		}
		if ($j(el).val().length<=0) {
			if (debug) {
				console.log('removing ', $j(el).attr('name'));
			}
			$j(el).parents('li.form').first().remove();
		}
	});
}