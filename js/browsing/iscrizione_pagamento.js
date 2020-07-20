function initDoc() {
	$j('input[type="submit"]').hide().removeClass('form').addClass('ui teal button').show();
	$j('#paypal-ada-form input[type="hidden"]').each((index, el) => {
		console.log($j(el).attr('name'), $j(el).val(), $j(el).val().length);
		if ($j(el).val().length<=0) {
			console.log('removing ', $j(el).attr('name'));
			$j(el).parents('li.form').first().remove();
		}
	});
}