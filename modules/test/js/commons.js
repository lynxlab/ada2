// show answer on button click (mobile only)
function showAnswersMobile() {
	
	// do something only if the showAnswersBtn is there
	if ($j('#showAnswersBtn').length>0) {
		
		var theButton = $j('#showAnswersBtn');
		var action = null;
		var showLbl;
		var hideLbl;
		
		// get show answers translated label if it's there
		 if ($j('#showAnswersLbl').length>0 ) showLbl = $j('#showAnswersLbl').text();
		 else showLbl = 'Click To Expand';
		 
		// get hide answers translated label if it's there
		 if ($j('#hideAnswersLbl').length>0 ) hideLbl = $j('#hideAnswersLbl').text();
		 else hideLbl = 'Click To Expand';
		 
		 if (theButton.hasClass('showAnswers')) {
			 // set action to show answers
			 action = 'mouseover';
			 // set button lael to hide
			 newLbl = hideLbl;
		 } else {
			 // set action to hide answers
			 action = 'mouseout';
			 // set button label to show
			 newLbl = showLbl;
		 }
		 
		 // if an action has been set, do it and set button accordingly
		 if (action!=null) {
			 $j('.clozePopup, .answerPopup').tooltip({
				 show: false,
				 hide: false,
				 position: {my: "left middle", at: "right+15 middle"},
				 content: getToolTip
			 })[action]();
			 // change button's label and class
			 theButton.html (newLbl);
			 theButton.toggleClass('hideAnswers');
			 theButton.toggleClass('showAnswers');
		 }
	}
}
	
//tooltip for cloze input editing
function getToolTip() {
	var title = $j(this).attr('title');
	return $j("#popup_"+title).html();
}

document.observe('dom:loaded', function() {
	/**
	 * prevent answer select element from opening both for desktop & mobile
	 */
	$j(function() {
	    $j('select.right_answer_test, select.answerPopup').on('focus mousedown', function(e) {
	            e.preventDefault();
	            this.blur();
	            window.focus();
	    });
	});
	
	if (!IS_MOBILE) {
		/**
		 * if it's not mobile, correct answer tooltips
		 * are handled by jQuery itself on mouseover/mouseout
		 */
		$j(document).tooltip({
			items: '.clozePopup, .answerPopup',
			show: false,
			hide: false,
			position: {my: "left middle", at: "right+15 middle"},
			content: getToolTip
		});
	} else if (IS_MOBILE) {
		/**
		 * if it's mobile, handle the tooltips by ourselves
		 * using click events.
		 */	
	    var eventName = (navigator.userAgent.match(/iPad/i)) ? 'touchstart' : 'click';
	    
		$j(document).on(eventName, '.clozePopup, .answerPopup, [id^=ui-tooltip]' ,function(event){

			var openElement = null;
			
			// destroy all the tooltips
			$j(this).parents('body').find('.clozePopup, .answerPopup').each(function(){
				if ($j(this).data('ui-tooltip')) {					
					$j(this).tooltip('destroy');
					if (openElement==null) openElement = $j(this);
				}
			});
			
			if (openElement==null) doToolTip = true;
			else if (openElement!=null && $j(this)[0]!=openElement[0]) doToolTip = true;
			else doToolTip = false;
			
			if (doToolTip) {
				// create the tooltip on the clicked element
				$j(this).tooltip({
					 show: false,
					 hide: false,
					 position: {my: "left middle", at: "right+15 middle"},
					 content: getToolTip
				 })
				 .on('mouseleave focusout', function(event) {
	                  event.stopImmediatePropagation();
	             });
				 $j(this).tooltip("open");
			 }
		});		
	}
	//end tooltip

	$j('img:not(.noPopup)').each(function (index,element) {
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