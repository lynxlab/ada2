var VA_IDENTIFIERS = '.erase_variation_test, .highlight_variation_test';
var VA_CONTAINER = '.answer_group_test';
var VA_FORM = '#testForm';

$j(document).ready(function() {
	//functionalities for single/multi choiche variations (erase, highlight)
	function resetVariationTest($input) {
		$input.prop('checked',false);
		$input.siblings('label').removeClass('clicked');
	}

	$j(VA_IDENTIFIERS).each(function (i,e) {
		var input = $j('input',$j(e));
		var label = $j('label',$j(e));

		if (input.prop('checked')) {
			label.addClass('clicked');
		}
		else {
			label.removeClass('clicked');
		}

		input.click(function (){
			input.closest(VA_CONTAINER).find('input').each(function (i,e) {
				var input = $j(e);
				var label = input.siblings('label');
				if (input.prop('checked')) {
					label.addClass('clicked');
				}
				else {
					label.removeClass('clicked');
				}
			});
		});
	});

	//functionalities for resetting form
	$j(VA_FORM).bind('reset', function() {
		$j(VA_IDENTIFIERS).find('input').each(function (i,e) {
			resetVariationTest($j(e));
		});
	});
});