var EH_IDENTIFIERS = '.answer_cloze_erase_test, .answer_cloze_highlight_test';
var EH_ITEM = '.answer_erase_item_test';
var EH_FORM = '#testForm';

$j(document).ready(function() {
	//functionalities for erase/highlight words in text (cloze_erase_test type)
	function resetEraseItemTest($span,$parent) {
		var rel = $span.attr('rel');
		var value = $span.attr('value');
		$j('input[name="'+rel+'"]',$parent).remove();
		$span.removeClass('clicked');
	}

	function clickEraseItemTest($span,$parent) {
		var rel = $span.attr('rel');
		var value = $span.attr('value');
		var input = document.createElement('input');
		input.type = 'hidden';
		input.name = rel;
		input.value = value;
		$parent.append(input);
		$span.addClass('clicked');
	}

	$j(EH_IDENTIFIERS).each(function (i,parent) {
		var parent = $j(parent);
		$j(EH_ITEM,parent).each(function(j,e) {
			var span = $j(e);
			span.click(function () {
				if (span.hasClass('clicked')) {
					resetEraseItemTest(span,parent);
				}
				else {
					clickEraseItemTest(span,parent);
				}
			});
		});
	});

	//functionalities for resetting form
	$j(EH_FORM).bind('reset', function() {
		$j(EH_IDENTIFIERS).each(function (i,parent) {
			var parent = $j(parent);
			$j(EH_ITEM,parent).each(function(j,e) {
				resetEraseItemTest($j(e),parent);
			});
		});
	});
});