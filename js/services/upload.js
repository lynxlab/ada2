function askActionToUser(encAskOptions) {
		
	var askOptions = JSON.parse(decodeURIComponent(encAskOptions));
	var theButtons = Array();
	
	  for (var i=0; i<askOptions.buttons.length; i++ ) {
			button = askOptions.buttons[i];
			theButtons[i] = {
					text: button.label,
					icons: { primary: button.icon },
					id: "askBtn_"+i,
				    click: function(event) { 
				    	var clickedId= $j(event.target).closest('button').attr('id').replace(/^\D+/g, ''); 
				    	self.document.location.href = askOptions.buttons[clickedId].action;
				    }
			};
	  }

  /**
   * This is to show a modal dialog
   */
  $j("<p style='width:500px; text-align:center;z-index:9999;'>"+askOptions.message+"</p>").dialog({
	buttons: theButtons,
	resizable: false,
	title: askOptions.title,
	modal: true,
	minWidth: 550,
	closeOnEscape: false
  });
}

function initDoc() {
	$j('#users', '#upload_form').multiselect({
		keepRenderingSort: true
	});
}