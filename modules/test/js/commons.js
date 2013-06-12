document.observe('dom:loaded', function() {
	//tooltip for cloze input editing
	function getToolTip() {
		var title = $j(this).attr('title');
		return $j("#popup_"+title).html();
	}

	$j(document).tooltip({
		items: '.clozePopup, .answerPopup',
		show: false,
		hide: false,
		position: {my: "left middle", at: "right+15 middle"},
		content: getToolTip
	});
	//end tooltip

	$j('.answer_group_test img:not(.noPopup)').each(function (index,element) {
		$j(element).css('cursor','pointer');
		$j(element).click(function () {
			if ($j(element).parent('li').hasClass('draggable')) {
				return;
			}

			var alt = $j(element).attr('alt');
			var title = $j(element).attr('title');
			var name = ' ';
			if (title != undefined && title != '') {
				name = title;
			}
			if (alt != undefined && alt != '') {
				name = alt;
			}
			var cloned = $j(element).clone();
			cloned.removeAttr('width');
			cloned.removeAttr('height');

			var img = new Image();
			img.src = cloned.attr("src");
			var width = img.width+20;
			var max_width = $j(window).width()*0.80;
			if (width > max_width) {
				width = max_width;
			}

			$j(document.createElement('div'))
				.attr('title',name)
				.css('text-align','center')
				.css('width',width+'px')
				.append(cloned)
				.dialog({
					autoOpen: true,
					height: 'auto',
					width: width,
					draggable: false,
					resizable: false,
					modal: true,
					open: function(event, ui) {
						$j(event.target.parentElement).find('.ui-dialog-buttonpane').remove();
					}
				});
		});
	});
});