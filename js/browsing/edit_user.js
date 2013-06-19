function initUserRegistrationForm( hasTabs )
{
	/**
	 * tabs initialization
	 */
	if (hasTabs)
	{
		$j('#tabs').tabs();
		/**
		 * attach to all input fields to show the 'save' icon in the appropriate tab
		 */
		$j(':input').change (
				function() {
					var theId = $j(this).closest("div[role='tabpanel']").attr('id').replace(/^\D+/g, '');
					if ($j('#tabSaveIcon'+theId).css('visibility') == 'hidden') $j('#tabSaveIcon'+theId).css('visibility', 'visible'); 
				}
		);
	}
	
	/**
	 * date fields masked input
	 */
	$j("#birthdate").mask("99/99/9999");
	$j("#eduStartDate").mask("99/99/9999");
	$j("#eduEndDate").mask("99/99/9999");
	
	/**
	 * handle to manage submit from all forms
	 * 
	 * detect the name of the form that is being submitted and then
	 * do an ajax call to the appropriate php file
	 */
	$j('form').submit(
			function (e) {
				e.preventDefault();
				
				var theId = -1;
				var theForm = $j(this);
				var name = $j(this).attr('name');
				var isMultiRow = (theForm.find('input[name=saveAsMultiRow]').val() == 1 ) ? true : false;
				
				var phpSaveFile = (isMultiRow ? "save_multiRow" : "save_"+name) + ".php";

				if (hasTabs) theId = $j(this).closest("div[role='tabpanel']").attr('id').replace(/^\D+/g, '');
				
				$j.ajax({
					type	: 'POST',
					url		: HTTP_ROOT_DIR+ '/browsing/ajax/' + phpSaveFile,
					data	: $j(this).serialize(),
					dataType:'json',
					async	: false
					})
					.done   (function( JSONObj ) {
						if (JSONObj)
							{
								// showModalDialog ("Salvataggio", JSONObj.msg);
								showHideDiv(JSONObj.title ,JSONObj.msg);
								if (isMultiRow && JSONObj.status=='OK') {
									theForm.trigger('reset');
								}
							}
					} )
					.fail   (function() { 
						console.log("edit user has failed"); 
					} )
					.always (function() { 
						if (theId!=-1 && $j('#tabSaveIcon'+theId).css('visibility') == 'visible') $j('#tabSaveIcon'+theId).css('visibility', 'hidden');
					} );				
				return false;				
			}
	);	
}

function showHideDiv ( title, message)
{
	var theDiv = $j("<div class='saveResults'><p class='title'>"+title+"</p><p class='message'>"+message+"</p></div>");
	theDiv.css("position","fixed");
	theDiv.css("width", "350px");
	theDiv.css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2));
	theDiv.css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2));	
	theDiv.hide().appendTo('body').fadeIn(500).delay(2000).fadeOut(500, function() { theDiv.remove(); });
}

function showModalDialog ( title, message )
{
	  $j("<p style='text-align:center;'>"+message+"</p>").dialog( {
	    	buttons: { "Ok": function () { $j(this).dialog("close"); } },
	    	close: function (event, ui) { $j(this).remove(); },
	    	resizable: false,
	    	title: title,
	    	modal: true
	  });	
}