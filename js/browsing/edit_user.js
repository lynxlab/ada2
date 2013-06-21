function initUserRegistrationForm( hasTabs )
{
	/**
	 * tabs initialization
	 */
	if (hasTabs)
	{
		$j('#tabs').tabs({ 
			// reset form and hide save icon on tab activation
            activate: function (event, ui) {            	
                var active = $j("#tabs").tabs("option", "active");
                var activeID = ($j("#tabs ul>li a").eq(active).attr('href'));
                var theForm= $j(activeID).find("form");
                resetFormWithHidden (theForm);                
            }
		});
		
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
	 * hooks the masked input to every input field that has 'date' in its id.
	 * WARNING: the match is made case-insensitive. This is quite tricky, but works 
	 */
	var re =  RegExp("date" ,"i"); 
	$j("input[id]").filter(function() {
			return re.test(this.id);
		}).each(function() {
			$j(this).mask("99/99/9999");
		});
	
	initButtons();
	
	/**
	 * handle to manage submit from all forms
	 * 
	 * detect the name of the form that is being submitted and then do an ajax
	 * call to the appropriate php file
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
									updateExtraRow (JSONObj.extraID, JSONObj.html, name);
									resetFormWithHidden ( theForm );
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

function initButtons()
{
	/**
	 * edit button
	 */
	$j(".extraEditButton").button({
		icons : {
			primary : "ui-icon-gear"
		}
	});
	
	/**
	 * delete button
	 */
	$j(".extraDeleteButton").button({
		icons : {
			primary : "ui-icon-trash"
		}
	});	
}

/**
 * updates display of extra row depending if
 * it's a new element or an edited one
 */
function updateExtraRow (extraID, html, extraTableName)
{
	var container = $j('#container_' + extraTableName);
	var element = container.children('#extraDIV_' + extraID);
	var isUpdate = (element.length > 0);
	
	if (isUpdate)
	{
		/**
		 * must be surrounded by a div because 
		 * $j("<div id='myid'></div>").find('#myid');
		 * will "obviously" find nothing! :)
		 */
		editedContent = $j('<div>'+html+'</div>').find('#extraDIV_' + extraID).html();
		element.html (editedContent);
		addedElement = element;
	}
	else
	{
		container.append(html);
		addedElement = container.children('#extraDIV_' + extraID);
		addedElement.hide();
	}
	
	scrollTo(addedElement);	
	initButtons();

	if (isUpdate)
		addedElement.delay(1000).effect("highlight", "slow");
	else
		addedElement.delay(1000).fadeIn(600);
}

function resetFormWithHidden ( theForm )
{
	theForm.trigger('reset');
	var formName = theForm.attr('name');
	var fieldID = formName.charAt(0).toUpperCase() + formName.slice(1);
	$j ('#id'+fieldID).val('0');
	// hide the save icon
	$j ('span[id^=tabSaveIcon]').each ( function () { $j(this).css('visibility', 'hidden'); });
}


function editExtra ( extraTableName, extraID )
{
	// store the first form element id in order to scroll to it afterwards
	var firstElementID = null;
	
	// cycle trough each table cell having id='val_*'
	$j('#'+extraTableName+'_'+extraID+" td[id^=val_]").each( function() {
		cellID = $j(this).attr('id');		
		var arrayVals = cellID.split('_');
		var elementID  = arrayVals[1];		
		// sets corresponding form element to the selected value
		
		$j('form[name='+extraTableName+'] #'+elementID).val( $j(this).html() );
		if (firstElementID==null) firstElementID = elementID;
	});
	
	// sets form hidden id value to selected element
	// must capitalize the first letter of extraTableName value before setting
	extraTableForFromID = extraTableName.charAt(0).toUpperCase() + extraTableName.slice(1);
	// ok, now I'm setting the value
	$j('form[name='+extraTableName+'] #id'+extraTableForFromID).val(extraID);

	// scroll to the label of the first form element so that it'll become visible to the user
	scrollTo ( $j('#l_'+firstElementID) );
}

function scrollTo ( jqueryObj )
{
	scrollToValue = parseInt (jqueryObj.offset().top );
	$j("body,html").animate({ scrollTop: scrollToValue+'px' });	
}

function deleteExtra ( extraTableName, extraID )
{
	if (confirm ("Questo cancellera' l'elemento selezionato"))
	{
		$j.ajax({
			type	:	'POST',
			url		:	HTTP_ROOT_DIR+ '/browsing/ajax/delete_multiRow.php',
			data	:	{ id: extraID, extraTableName: extraTableName },
			dataType:	'json'
		})
		.done  (function (JSONObj) {
			if (JSONObj)
				{
					if (JSONObj.status=='OK')
					{
						$j('.'+ extraTableName +'#extraDIV_'+extraID).fadeOut(600, function () { 
							$j('.' + extraTableName + '#extraDIV_'+extraID).remove();
							showHideDiv(JSONObj.title ,JSONObj.msg); } );
					}
				}
		})
		.fail  ()
		.always();
	}
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