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
	 * birthdate masked input
	 */
	$j("#birthdate").mask("99/99/9999");				
	
	/**
	 * handle to manage submit from all forms
	 * 
	 * detect the name of the for that is being submitted and then
	 * do an ajax call to the appropriate php file
	 */
	$j('form').submit(
			function (e) {
				e.preventDefault();
				
				var theId = -1;
				var name = $j(this).attr('name');
				if (hasTabs) theId = $j(this).closest("div[role='tabpanel']").attr('id').replace(/^\D+/g, '');
				
				$j.ajax({
					type	: 'POST',
					url		: HTTP_ROOT_DIR+'/browsing/ajax/save_'+name+'.php',
					data	: $j(this).serialize(),
					dataType:'json',
					async	: false
					})
					.done   (function( JSONObj ) {
						if (JSONObj)
							{
								showModalDialog ("Salvataggio", JSONObj.msg);
							}
					} )
					.fail   (function() { 
						console.log("fail"); 
					} )
					.always (function() { 
						if (theId!=-1 && $j('#tabSaveIcon'+theId).css('visibility') == 'visible') $j('#tabSaveIcon'+theId).css('visibility', 'hidden');
					} );
				
				return false;				
			}
	);	
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