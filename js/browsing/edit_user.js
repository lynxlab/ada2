/**
 * inits the form
 * 
 * @param hasTabs boolean to tell if it has to be a 'tabbed' multi-form
 */
function initUserRegistrationForm( hasTabs )
{
	/**
	 * tabs initialization
	 */
	if (hasTabs)
	{
		$j('#tabs').tabs({
			// reset form and hide save icon before tab activation
			beforeActivate: function( event, ui ) {
				// if unsaved data ask user if really wants to switch tab
				var theId = ui.oldPanel.attr('id').replace(/^\D+/g, '');
				
				if ($j('#tabSaveIcon'+theId).css('visibility') != 'hidden' &&						
					!confirm(i18n['confirmTabChange']))
					event.preventDefault();
				else // reset the proper form and hide it if visible
				{
	                var theForm = ui.oldPanel.find('form');
	                if (theForm.find('input[name=saveAsMultiRow]').length > 0)
	                {
	                	// this is a form for a multiRow table, need to hide it if it's shown
	                	if (theForm.css('display')!='none') { toggleForm ( theForm.attr('name'), false); }
	                }
	                else resetFormWithHidden (theForm);
					
				}
			}
		});
		
		/**
		 * attach to all input fields to show the 'save' icon in the appropriate tab
		 * on form field change
		 */
		$j(':input').change (
				function() {
					var theId = $j(this).closest("div[role='tabpanel']").attr('id').replace(/^\D+/g, '');
					setSaveIconVisibility (theId , 'visible'); 
				}
		);
				
		/**
		 * attach to all input fields to show the 'save' icon in the appropriate tab
		 * on keydown in a form field
		 */
		$j(':input').each( function() {
			$j(this).keydown( function() {
				var theId = $j(this).closest("div[role='tabpanel']").attr('id').replace(/^\D+/g, '');
				
				// assignment to extend the scope of $j(this) to the function inside the timeout			
				var myThis = $j(this);			
				
				// need a timeout, waiting for the key to be 'really' pressed?
				// 200ms should be enough
				window.setTimeout (
						function() {
							if ( myThis.data('initialValue') != myThis.val() ) 
							{
								setSaveIconVisibility (theId , 'visible');
							}
						} ,200);
			});
		});		
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
	
	/**
	 * init jquery buttons and form initial values
	 */
	initButtons();
	initFormsInitialValues();
	
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
									toggleForm (name, false);
								}
							}
					} )
					.fail   (function() { 
						console.log("edit user has failed"); 
					} )
					.always (function() { 
						if (theId!=-1 && $j('#tabSaveIcon'+theId).css('visibility') == 'visible') setSaveIconVisibility (theId, 'hidden');
					} );				
				return false;				
			}
	);	
}

/**
 * sets save icon visibility
 * 
 * @param iconNumber number of icon for which to set visibility
 * @param visibility css visibility to set, as a string (e.g. 'visible' or 'hidden')
 */
function setSaveIconVisibility( iconNumber, visibility )
{
	$j('#tabSaveIcon'+iconNumber).css('visibility', visibility);	
}

/**
 * inits all forms initial values.
 * 
 * When modifing a row, the initial values are the loaded ones.
 */
function initFormsInitialValues()
{
	$j(':input').each(function() { 
	    $j(this).data('initialValue', $j(this).val()); 
	}); 	
}

/**
 * inits jquery buttons
 */
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
	
	/**
	 * new button
	 */
	$j(".showFormButton").button({
		icons : {
			primary : "ui-icon-circle-plus"
		}
	});
	
	/**
	 * close discarding changes
	 */
	$j(".hideFormButton").button({
		icons : {
			primary : "ui-icon-circle-close"
		}
	});
	
}

/**
 * shows the form for adding new item or modify existing one
 * 
 * @param formName name of the form to be toggled
 * @param mustScroll boolean true if page must scroll to the form after it's been toggled
 */
function toggleForm ( formName, mustScroll )
{ 
	var theForm = $j('form[name='+formName+']');

	theForm.toggle('blind');	
	$j('.showFormButton.'+formName).toggle();
	$j('.hideFormButton.'+formName).toggle();
	
	resetFormWithHidden(theForm);

	if (mustScroll) scrollTo (theForm);
}

/**
 * updates display of extra row depending if
 * it's a new element or an edited one
 * 
 * @param extraID numeric id of the new or edit element
 * @param html html to be displayed
 * @param extraTableName name of the extra table we're working on
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
//		container.append(html);
		container.find('.fform.form').before(html);
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

/**
 * resets form, including all the hidden fields and hides all the 'save icon's
 * 
 * @param theForm jquery object to perform operations onto.
 */
function resetFormWithHidden ( theForm )
{
	theForm.trigger('reset');
	var formName = theForm.attr('name');
	var fieldID = formName.charAt(0).toUpperCase() + formName.slice(1);
	$j ('#id'+fieldID).val('0');
	// hide all save icons
	$j ('span[id^=tabSaveIcon]').each ( function () { $j(this).css('visibility', 'hidden'); });
	// init form initial values
	initFormsInitialValues();
}

/**
 * loads an extra table row to be edited. form values are derived
 * from relative shown html. Loads proper values into the form and displays it.
 * 
 * @param extraTableName name of the extra table we're working on
 * @param extraID numeric id of the row to edit
 */
function editExtra ( extraTableName, extraID )
{
	// store the first form element id in order to scroll to it afterwards
	var firstElementID = null;
	
	// resets the form
	resetFormWithHidden ( $j('form[name='+extraTableName+']') );
	
	// show the form if it's hidden
	if ($j('form[name='+extraTableName+']').css('display')=='none') toggleForm(extraTableName, false);
	
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
	
	// init forms initial values
	initFormsInitialValues();

	// scroll to the label of the first form element so that it'll become visible to the user
	scrollTo ( $j('#l_'+firstElementID) );
}

/**
 * scrolls the page to the passed element
 * 
 * @param jqueryObj jquery Object to which top the page shall scroll
 */
function scrollTo ( jqueryObj )
{
	scrollToValue = parseInt (jqueryObj.offset().top );
	$j("body,html").animate({ scrollTop: scrollToValue+'px' });	
}

/**
 * deletes an extra table row with an AJAX call
 * 
 * @param extraTableName name of the extra table we're working on
 * @param extraID numeric id of the row to edit
 */
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
		});
	}
}

/**
 * shows and after 500ms removes the div to give feedback to the user about
 * the status of the executed operation (if it's been saved, delete or who knows what..)
 * 
 * @param title title to be displayed
 * @param message message to the user
 */
function showHideDiv ( title, message )
{
	var theDiv = $j("<div class='saveResults'><p class='title'>"+title+"</p><p class='message'>"+message+"</p></div>");
	theDiv.css("position","fixed");
	theDiv.css("width", "350px");
	theDiv.css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2));
	theDiv.css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2));	
	theDiv.hide().appendTo('body').fadeIn(500).delay(2000).fadeOut(500, function() { theDiv.remove(); });
}

/**
 * shows a modal dialog box to give feedback to the user about
 * the status of the executed operation (if it's been saved, delete or who knows what..)
 * user must click 'ok' button as an aknowledgement.
 * 
 * NOTE: this is not used as of 2/jul/2013 version, it's here just in case you need it!
 * 
 * @param title title to be displayed
 * @param message message to the user
 */
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

/**
 * ask user to save changes (if any) on browser page unload
 */
window.onbeforeunload = function(){ 
    var msg = i18n['confirmLeavePage']; 
    var mustSave = false; 
 
    $j('span[id^=tabSaveIcon]').each (function () { mustSave = mustSave || ($j(this).css('visibility') != 'hidden'); });
 
    if(mustSave == true) return msg; 
}; 