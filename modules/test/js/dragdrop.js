var DD_DIV_BOX = 'divDragDropBox';
var DD_UL_BOX_CLASS = 'dragdropBox';
var DD_UL_MULTI_CLASS = 'multiDragDropBox';
var DD_BOX_PREFIX = 'ulBox';
var DD_ANSWER_PREFIX = 'answer';
var DD_INPUT_PREFIX = '#dropInput';
var DD_DROP_PREFIX = '#drop';
var DD_GENERIC_PREFIX = 'drop';
var DD_DROP_CLASS_PREFIX = '.drop';
var DD_SORTABLE = '.sortable';
var DD_REGEXP = /_[0-9]+/;
var DD_FULL_CLASS = 'full';
var DD_DRAG_DROP_BOX = '.dragdropBox';
var DD_FORM = '#testForm';

var isDroppedInsidePlaceHolder = false;
// the following two are updated during sort operation
var placeholderLeft=0;
var placeholderTop=0;

/**
 * checks if mouse button is released inside the receiver placeholder area
 * 
 * @author giorgio
 * @param event jQuery event holding mouse position
 * @param ui jQuery ui element
 * @returns {Boolean} true if mouse is released inside receiver placeholder area
 */

function isMouseInsideDropArea (event,ui) {
  
	var horizTolerance = 40;
	var vertTolerance = 40;
	
	placeholder = $j(ui.item[0].parentElement).children("li.drop-highlight");
	
	var posX = event.pageX;
	var posY = event.pageY;
	
	placeholderRight = placeholderLeft + parseInt (placeholder.outerWidth (true));
	placeholderBottom = placeholderTop + parseInt (placeholder.outerHeight(true));
	
	placeholderLeft   -= parseInt(horizTolerance/2);
	placeholderTop    -= parseInt(vertTolerance /2);
	placeholderRight  += parseInt(horizTolerance/2);
	placeholderBottom += parseInt(vertTolerance /2);
	
	var inside =   posX >= placeholderLeft  && posX <= placeholderRight &&
				   posY >= placeholderTop  && posY <= placeholderBottom ;	
	
	/*
	   console.log ("mouse: "+posX+"-"+posY);
	   console.log (placeholderLeft);
	   console.log (placeholderTop);
	   console.log (placeholderRight);	
	   console.log (placeholderBottom);
	   console.log ('is inside? '+inside);
	 */	   
	return inside;
}


function registerDrop(event,ui) {
	
    placeholderLeft = 0;
	placeholderTop = 0;
	
	var sender = $j(event.target);
	var item = $j(ui.item[0]);
	item.removeClass('target');
	var receiver = $j(ui.item[0].parentElement);
	var idTargetAnswer = receiver.attr('id').replace(DD_GENERIC_PREFIX,'');
	var idParentAnswer = $j(event.target).attr('id').replace(DD_GENERIC_PREFIX,'');
	var targetInput = $j(DD_INPUT_PREFIX+idTargetAnswer);
	var parentInput = $j(DD_INPUT_PREFIX+idParentAnswer);
	var value = item.attr('id').replace(DD_ANSWER_PREFIX,'');

	var clearFullClass = true;
	var multiClass = false;
	
	var cancelDrop = ( receiver.html().match(/answer/g).length > 1);
	
	if (receiver.hasClass(DD_UL_BOX_CLASS))
	{	
		var senderBoxNum = parseInt(item.attr('class'));
		var receiverBoxNum  = parseInt(receiver.parent('div').attr('class'));
		
		if (senderBoxNum>0 && receiverBoxNum>0) {		
			cancelDrop = (senderBoxNum != receiverBoxNum);
		}
	}

	if (!cancelDrop)
	{
		if (receiver.hasClass(DD_UL_MULTI_CLASS)) {
			multiClass = true;
			var value = item.attr('id').replace(DD_ANSWER_PREFIX,'');
			var values = targetInput.val().split(',');
			if (targetInput.val().length == 0) {
				values = [value];
			}
			else {
				values = targetInput.val().split(',');
				values.push(value);
			}
			targetInput.val(values.join(','));
		}
		if (sender.hasClass(DD_UL_MULTI_CLASS)) {
			multiClass = true;
			var values = parentInput.val().split(',');
			values.splice(values.indexOf(value),1);
			if (values.length == 0) {
				parentInput.val('');
			}
			else {
				parentInput.val(values.join(','));
			}
		}
		
		/**
		 * @author giorgio 31/ott/2013
		 * 
		 * if it has been dropped outside the placeholder,
		 * cancel the operation and return right away!
		 * 
		 * NOTE: isDroppedInsidePlaceHolder is set in
		 * isMouseInsideDropArea function called on beforeStop 
		 * of the sortable. 
		 */	
		if (!multiClass) {
			if (isDroppedInsidePlaceHolder && (targetInput.val() == '' || targetInput.val() == undefined)) {
				targetInput.val(value);
				parentInput.removeAttr('value');
			}
			else {
				clearFullClass = false;
				$j(this).sortable('cancel');
			}
		}
		
		if (multiClass)
		{
			if (typeof saveMultipleClozeAnswers === "function") {
				saveMultipleClozeAnswers();
			} else if (!isDroppedInsidePlaceHolder) {
				clearFullClass = false;
				$j(this).sortable('cancel');
			}		
		}		
	} else { // if the cell was occupied
		clearFullClass = false;
		$j(this).sortable('cancel');
	}
	
	if (clearFullClass) {
		if (!receiver.hasClass(DD_UL_BOX_CLASS) && !receiver.hasClass(DD_UL_MULTI_CLASS)) {
			receiver.addClass(DD_FULL_CLASS);
		}
		if (sender.hasClass(DD_FULL_CLASS)) {
			sender.removeClass(DD_FULL_CLASS);
		}
	}
		
	/**
	 * @author giorgio 21/ott/2013
	 * if dragged element was NOT dropped in a droppable area,
	 * sender should be equal to receiver and must be cleared from
	 * the DD_FULL_CLASS (only if it's not on the draggable box/column)
	 */
	if (idTargetAnswer == idParentAnswer && !sender.hasClass(DD_FULL_CLASS) && !sender.hasClass(DD_UL_BOX_CLASS)) sender.addClass(DD_FULL_CLASS);
}

function startDrag(event,ui) {
	$j(ui.item).addClass('target');
}

function makeSortable(e) {
	var dropId = $j(e).attr('id');
	if (typeof dropId!='undefined' && dropId.indexOf(DD_BOX_PREFIX) == 0) {
		dropId = dropId.replace(DD_BOX_PREFIX,'').replace(DD_REGEXP,'');
	}
	else if (typeof dropId!='undefined' && dropId.indexOf(DD_GENERIC_PREFIX) == 0) {
		dropId = dropId.replace(DD_GENERIC_PREFIX,'').replace(DD_REGEXP,'');
	}
	else return;

	dropId = dropId.replace('_cell','');

	$j(e).sortable({
		tolerance: 'pointer',
		connectWith: DD_DROP_CLASS_PREFIX+dropId,
		placeholder: 'drop-highlight',
		forcePlaceholderSize: true,
		helper: "clone",
		opacity: 0.6,
		scroll: true,
		beforeStop: function (event,ui) { 
		        isDroppedInsidePlaceHolder = isMouseInsideDropArea(event, ui); },
		sort: function (event,ui) { 
			if ($j(ui.placeholder).length > 0) {
			    placeholderLeft = $j(ui.placeholder).offset().left;
			    placeholderTop  = $j(ui.placeholder).offset().top;
			}
		},
		start: startDrag,
		stop: registerDrop
	}).disableSelection();

	var li = $j(e).children();
	li.hover(
		function(){
			var sender = $j(this).closest(DD_SORTABLE);
			$j('.'+DD_FULL_CLASS).not(sender).sortable('disable');
		},
		function() {
			$j('.'+DD_FULL_CLASS).sortable('enable');
		}
	);
}

$j(window).load(function() {
	$j(DD_DRAG_DROP_BOX).each( function(i,e) {
		var parent = $j(e).parent();
		e = $j(e);
		if (parent.hasClass('left') || parent.hasClass('right')) {
			e.css('width',e.css('width'));
		}
		e.css('height',e.css('height'));
	});

	if ($j(DD_DRAG_DROP_BOX).length > 0 ) {
		$j('#divLoading').dialog('close').dialog('destroy').remove();
	}
});

$j(document).ready(function() {
	if ($j(DD_DRAG_DROP_BOX).length > 0 ) {
		$j(document.createElement('div'))
			.attr('id','divLoading')
			.dialog({
				autoOpen: true,
				height: $j(window).height()*0.98,
				width: $j(window).width()*0.98,
				closeOnEscape: false,
				draggable: false,
				resizable: false,
				modal: true,
				open: function( event, ui ) {
					$j(event.target.parentElement).find('.ui-dialog-titlebar').remove();
					$j(event.target.parentElement).find('.ui-dialog-buttonpane').remove();
					$j('body').css({'height':'100%','overflow':'hidden'});
				},
				close: function( event, ui ) {
					$j('body').css({'height':'auto','overflow':'visible'});
				}
		});
	}

	$j(DD_SORTABLE).each( function(i,e) {
		makeSortable(e);
	});
	
	/**
	 * @author giorgio 21/ott/2013
	 * functionalities for resetting form
	 */
	$j(DD_FORM).bind('reset', function() {
	  // place back all the inputs in their own position
		$j('[id^="'+ DD_INPUT_PREFIX.replace('#','') + '"]').each(function (index,e){
			if ($j(e).val() != '')
			{
				$j(e).removeAttr('value');
				
				var ulItem = $j(e).next('ul');
				var targetId = ulItem.attr('id').replace(DD_GENERIC_PREFIX,'').replace(DD_REGEXP,'');				
				var sourceObj = (ulItem.children('li').first().length > 0) ? ulItem.children('li').first() : ulItem; 
				
				var destId = null;
			    var addFullClass = false;
				if ($j('#'+ DD_BOX_PREFIX + targetId).length > 0) destId = DD_BOX_PREFIX + targetId;
				else if (sourceObj.length > 0)  {
					// must retrieve id of the element where the resetted values must be moved
					secondPartId = sourceObj.attr('id').replace (DD_ANSWER_PREFIX,'');
					if ($j('#'+ DD_GENERIC_PREFIX + targetId + '_' + secondPartId + '00' ).length > 0) destId = DD_GENERIC_PREFIX + targetId + '_' + secondPartId + '00';
					addFullClass = true;
				} 
				
				if (destId != null && sourceObj.length > 0)
				{
					// remove the element with an animation effect
					sourceObj.effect('transfer', { easing: "easeInOutBack", to: '#' + destId , className:'resetDnD'  }, 800, function(){
						$j('#' + destId ).append( ulItem.html() );					
						if (ulItem.hasClass(DD_FULL_CLASS)) ulItem.removeClass(DD_FULL_CLASS);
						if (addFullClass && !$j('#' + destId).hasClass(DD_FULL_CLASS)) $j('#' + destId).addClass(DD_FULL_CLASS);
						ulItem.empty();
						resetWords();
					});
				} else window.location.reload();
			}
		}); // end each function
	}); // end bind function	
});

function resetWords()
{
// if there are words outside their own position, place them back
$j('li [id^="'+DD_ANSWER_PREFIX+'"]').each (function (index, e)
  {
    var answerId = $j(this).attr('id').replace(DD_ANSWER_PREFIX,'');
    
    if ($j(this).parent('ul [id^="'+DD_GENERIC_PREFIX+'"]').length>0)
    {
      var parentId = $j(this).parent('ul').attr('id');      
      var questionId = parentId.replace(DD_GENERIC_PREFIX,'').replace(DD_REGEXP,'');
      var targetId = DD_GENERIC_PREFIX + questionId + '_' + answerId + '00';
      
      if (parentId != targetId)
      {	
	    $j('#'+parentId).children('#'+DD_ANSWER_PREFIX+answerId).fadeOut( 'slow',
		 function () { 
		  $j('#'+targetId).addClass(DD_FULL_CLASS); 
		  $j('#'+targetId).html( $j(this) );
		  $j(this).fadeIn();   
		  $j('#'+parentId).children('#'+DD_ANSWER_PREFIX+answerId).remove();	  
		}
	    );	    
      }
    }
  }); // end each
} // end function