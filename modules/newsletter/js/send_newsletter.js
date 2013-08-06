/**
 * change the following to false if you want standard submit
 * instead of ajax 
 */
 var isAjax = true;

function initDoc( startingLabel, nothingFoundLabel )
{
	if ($j('form[name=newsletterFilterForm] :submit').length >0) {
		$j('form[name=newsletterFilterForm] :submit').button();
	} 
	
	
	// onchange for every form field to compute and display the summary
	$j('form[name=newsletterFilterForm] :input').on ('change', function() {
		
		 if ($j(this).attr('id') != 'idCourse' && $j(this).attr('id') != 'userType') turnFilterIntoSentence();
	
	});	// end on change
	
	$j('#userType').on ('change', function() {
		var newvalue = parseInt ($j(this).val());
		
		if (newvalue==3) { // student selected
			$j('#userPlatformStatus').removeAttr('disabled');
			$j('#idCourse').removeAttr('disabled');
			$j('#userCourseStatus').removeAttr('disabled');			
		}
		else { // every other selection
			$j('#userPlatformStatus').val(-1).attr('disabled','true');
			$j('#userCourseStatus').val(-1).attr('disabled','true');
			$j('#idCourse').removeAttr('disabled');
			if (newvalue==0 || newvalue==6) // no selection or switcher
			{
				 $j('#idCourse').val(0).change().attr('disabled','true');				 
			}
		}
		turnFilterIntoSentence();
	});
	
	// hooks the instance to the selected course id
	$j('#idCourse').cascade({
        source: HTTP_ROOT_DIR+'/modules/newsletter/ajax/get_instances.php',
        cascaded: "idInstance",
        dependentNothingFoundLabel: decodeURIComponent((nothingFoundLabel + '').replace(/\+/g, '%20')),
        dependentStartingLabel: decodeURIComponent((startingLabel + '').replace(/\+/g, '%20')),
        callback : turnFilterIntoSentence,
        dependentLoadingLabel: "Loading ..."// ,
//        extraParams: { extra: getExtra },
//        spinnerImg: "/Images/Spinner.gif"
    });
	
	
	$j('form[name=newsletterFilterForm]').on('submit', function(event) 
	{
		event.preventDefault();
		
		if (parseInt($j('#recipientsCount').val())>0)
		{		
			// do standard submit if we don't want ajax call
			// else proceed with ajax
			if (!isAjax) return true;
			else {				
				var postData = $j(this).serialize();
				postData += '&requestType=ajax';
				
				$j.ajax({
					type	: 'POST',
					url		: HTTP_ROOT_DIR+ '/modules/newsletter/ajax/enqueue_newsletter.php',
					data	: postData,
					dataType: 'html'
				})
				.done(function (html) {

				} );
				// the message is passed as a hidden field, so that the translateFN function works
				alert ($j('#enqueuedmsg').val());
				self.document.location.href = HTTP_ROOT_DIR + '/modules/newsletter';
				
			}
		}
		else {
			alert ('Nessun utente a cui inviare la newletter');
		}
		
		return false;		
		});	
}

function turnFilterIntoSentence ()
{
	$j.ajax({
		type	: 'POST',
		url		: HTTP_ROOT_DIR+ '/modules/newsletter/ajax/build_summary.php',
		data	: $j('form[name=newsletterFilterForm]').serialize(),
		dataType: 'json'	
	})
	.done (function (json) {
		$j('#recipientsCount').val(json.count);
		$j('#summaryText').fadeOut (500, function (){
			$j('#summaryText').html(json.html).fadeIn(500);
		});	// end done function and fadeOut		
	}); // end ajax (done)		
}