function initDoc() {
	$j(function() {
		$j(".column").sortable({
			connectWith : ".column",
			activate : function(event, ui) {
				ui.item.fadeTo("fast", 0.5);
			},
			deactivate : function(event, ui) {
				ui.item.fadeTo("fast", 1);
			},
		});

		$j(".column").disableSelection();

	});
}