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

function registerDrop(event,ui) {
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

	if (!multiClass) {
		if (targetInput.val() == '' || targetInput.val() == undefined) {
			targetInput.val(value);
			parentInput.removeAttr('value');
		}
		else {
			clearFullClass = false;

			$j(this).sortable('cancel');
		}
	}

	if (clearFullClass) {
		if (!receiver.hasClass(DD_UL_BOX_CLASS) && !receiver.hasClass(DD_UL_MULTI_CLASS)) {
			receiver.addClass(DD_FULL_CLASS);
		}
		if (sender.hasClass(DD_FULL_CLASS)) {
			sender.removeClass(DD_FULL_CLASS);
		}
	}

	if (multiClass) {
		if (typeof saveMultipleClozeAnswers === "function") {
			saveMultipleClozeAnswers();
		}
	}
}

function startDrag(event,ui) {
	$j(ui.item).addClass('target');
}

function makeSortable(e) {
	var dropId = $j(e).attr('id');
	if (dropId.indexOf(DD_BOX_PREFIX) == 0) {
		dropId = dropId.replace(DD_BOX_PREFIX,'');
	}
	else if (dropId.indexOf(DD_GENERIC_PREFIX) == 0) {
		dropId = dropId.replace(DD_GENERIC_PREFIX,'').replace(DD_REGEXP,'');
	}
	else return;

	dropId = dropId.replace('_cell','');

	$j(e).sortable({
		tolerance: 'pointer',
		connectWith: DD_DROP_CLASS_PREFIX+dropId,
		placeholder: 'drop-highlight',
		forcePlaceholderSize: true,
		scroll: true,
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
});