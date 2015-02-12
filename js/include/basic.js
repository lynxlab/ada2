/*
 * basic.js
 *
 *
 */
function newWindow(nomefile,x,y)
{
        openNewWindow(nomefile,x,y,'Immagine',false,false);
}

function openMessenger(nomefile,x,y)
{
        openNewWindow(nomefile,x,y,'Messaggeria',true,true);
}

function openNewWindow(nomefile,x,y,title,resizable,forceFocus) {

	prop = ('width='+x+',height='+y+', toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable='+((resizable) ? 'yes' : 'no'));
    win2=window.open(nomefile,title,prop);
    if (forceFocus) win2.focus();
}

function window_scroll(howmuch)
{
	window.scroll(0,howmuch);
}

function parentLoc(nomefile)
{
        win1=window.opener;
        win1.location = nomefile;
}

function confirmCriticalOperationBeforeRedirect(message, critical_operation_url)
{
	if (confirm(message)) {
		window.location = critical_operation_url;
	}
}


function closeMeAndReloadParent() {
  window.close();
  window.opener.location.reload();
}

function closeMe() {
  window.close();
}

function reloadParent() {
  window.opener.location.reload();  
}

function close_page(message) {
    alert(message);
    self.close();
}

function initDateField() {
    if ($j("#birthdate").length>0)
	$j("#birthdate").mask("99/99/9999");
    if ($j("#data_pubblicazione").length>0)
	$j("#data_pubblicazione").mask("99/99/9999");
}

function validateContent(elements, regexps, formName) {
	var error_found = false;
	for (i in elements) {
		var label = 'l_' + elements[i];
		var element = elements[i];
		var regexp = regexps[i];
		var value = null;
		var id = null;
		if ($(element) != null && $(element).getValue) {
			value = $(element).getValue();
			id = $(element).id;
		}
		
		if (value != null) {
			if(!value.match(regexp)) {
				if($(label)) {
					$(label).addClassName('error');					
				}
				error_found = true;
			}
			else {
				
				if($(label)) {
					$(label).removeClassName('error');
				}
				/**
				 * giorgio, if element it's a date field it may validate the regexp,
				 * but could be an invalid date. Must check it.
				 * NOTE: assumption is made that a date field 
				 * contains 'date' (NOT case sensitive) in its id.
				 */
				
				if (id.match(/date/i))
				{			
				 ok = null;
				 dateArray = value.split("/");
				 d = new Date (dateArray[2], dateArray[1]-1, dateArray[0]);
				 now = new Date();				 
				 if ( parseInt(dateArray[2]) < 1900) ok = false;
				 else if ( id.match(/birthdate/i) && (d.getTime() > now.getTime())) ok = false; 
				 else if (d.getFullYear() == dateArray[2] && d.getMonth() + 1 == dateArray[1] && d.getDate() == dateArray[0])
					 ok = true;
				 else ok = false;

				 if (!ok)
					 {
						if($(label)) {
							$(label).addClassName('error');					
						}
						error_found = true;
					 }
				}
			}
		}
	}

	if (error_found) {
		if($('error_form_'+formName)) {
			$('error_form_'+formName).addClassName('show_error');
			$('error_form_'+formName).removeClassName('hide_error');
		}
	}
	else {
		if($('error_form_'+formName)) {
			$('error_form_'+formName).addClassName('hide_error');
			$('error_form_'+formName).removeClassName('show_error');
		}
	}

	return !error_found;
}