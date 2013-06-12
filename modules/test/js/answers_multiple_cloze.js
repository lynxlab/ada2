var classTable = '.multipleClozeTable';
var clonableCell = '#clonableCell';
var clonableRow = '#clonableRow';
var clonableDelRow = '#clonableDelRow';
var clonableCol = '#clonableCol';
var clonableDelCol = '#clonableDelCol';
var colButtonClass = '.colButton';
var sortableClass = '.sortable';
var inputAnswersClass = 'inputAnswers';
var rowsLabelClass = '.rows_label';
var colsLabelClass = '.cols_label';
var typing = [];

function confirmExit() {
	return messageSaveInProgress;
}

function isEmptyValues(values) {
	var empty = true;
	values.each(function (i,e) {
		e = $j(e);
		if (e.val().length != 0) {
			empty = false;
		}
	});
	return empty;
}

function resetTable() {
	var values = $j('.'+inputAnswersClass);

	if (isEmptyValues(values)) {
		var $table = $j(classTable);
		$j('thead tr',$table).find('th').not('.colButton').not(':first').remove();
		$j('tbody tr',$table).not(':first').remove();
		$j('tbody tr',$table).find('td').not(':first').not(':last').remove();
		$j('tfoot tr',$table).first().find('th').not(':first').not(':first').not(':last').remove();
	}
	else {
		alert(messageNoReset);
	}
}

function addRow() {
	var $table = $j(classTable);
	
	$tr = $j(document.createElement('tr'));

	var $clone = $j(clonableRow).clone().removeAttr('id');
	$clone.find(rowsLabelClass).addClass(inputAnswersClass);
	$tr.append($clone);

	for(var i=0; i<$j('thead',$table).find('th').length-2; i++) {
		var rowNum = $j('tbody',$table).find('tr').length;
		var newId = $j(sortableClass,$table).length - 1 + i;
		var $clone = $j(clonableCell).clone().removeAttr('id');
		$clone.find('input')
			.attr('id',$clone.find('input').attr('id').replace('_cell','_'+newId))
			.attr('name',$clone.find('input').attr('name').replace('[row]','['+rowNum+']'))
			.addClass(inputAnswersClass);
		$clone.find('ul').attr('id',$clone.find('ul').attr('id').replace('_cell','_'+newId));
		$tr.append($clone);
	}
	
	$tr.append($j(clonableDelRow).clone().removeAttr('id'));

	$j('tbody',$table).append($tr);

	$tr.find('ul').each(function (i,$e) {
		makeSortable($e);
	});
	saveMultipleClozeAnswers();
}

function addCol() {
	var $table = $j(classTable);

	var $clone = $j(clonableCol).clone().removeAttr('id');
	$clone.find(colsLabelClass).addClass(inputAnswersClass);
	$clone.insertBefore($j('thead tr',$table).find(colButtonClass).last());

	$j('tbody tr',$table).each(function (i,e) {
		var $tr = $j(e);
		var rowNum = i;
		var newId = $j(sortableClass,$table).length - 1;
		var $clone = $j(clonableCell).clone().removeAttr('id');
		$clone.find('input')
			.attr('id',$clone.find('input').attr('id').replace('_cell','_'+newId))
			.attr('name',$clone.find('input').attr('name').replace('[row]','['+rowNum+']'))
			.addClass(inputAnswersClass);
		$clone.find('ul').attr('id',$clone.find('ul').attr('id').replace('_cell','_'+newId));
		$clone.insertBefore($tr.find(colButtonClass));
		makeSortable($clone.find('ul'));
	});

	var $clone = $j(clonableDelCol).clone().removeAttr('id');
	$clone.insertAfter($j('tfoot tr',$table).first().find(colButtonClass).last());
	saveMultipleClozeAnswers();
}

function delRow(element) {
	var $tr = $j(element).closest('tr');
	var values = $tr.find('.'+inputAnswersClass);

	if (isEmptyValues(values)) {
		$tr.remove();
		saveMultipleClozeAnswers();
	}
	else {
		alert(messageNoDelRow);
	}
}

function delCol(element) {
	var $tr = $j(element).closest('tr');
	var $th = $j(element).closest('th');
	var $thead = $j(element).closest('table').find('thead');
	var $tbody = $j(element).closest('table').find('tbody');
	var $tfoot = $j(element).closest('table').find('tfoot');

	var pos = $tr.children().index($th);

	var values = $j();
	$tbody.find('tr').each(function(i,e) {
		values = values.add($j(e).find('td').eq(pos-1).find('.'+inputAnswersClass));
	});

	if (isEmptyValues(values)) {
		$thead.find('th').each(function(i,e) {
			if (i == pos) {
				$j(e).remove();
			}
		});
		
		$tbody.find('tr').each(function(i,e) {
			$j(e).find('td').eq(pos-1).remove();
		});

		$tfoot.find('th').each(function(i,e) {
			if (i == pos) {
				$j(e).remove();
			}
		});
		saveMultipleClozeAnswers();
	}
	else {
		alert(messageNoDelCol);
	}
}

function emptyTable() {
	if (confirm(messageEmptyTable)) {
		resetMultipleClozeAnswers();
	}
}

function saveMultipleClozeAnswers() {
	$j.ajax({
		url: window.location.href,
		data: postVariable+"[operation]=save&"+$j(classTable).find('.'+inputAnswersClass).serialize(),
		type: 'POST',
		dataType: 'text',
		async: true,
		success: function(data) {
			if (data != '1') {
				alert(messageError);
			}
		},
		error: function() {
			alert(messageError);
		},
		complete: function() {
			if (typing.length == 0) {
				window.onbeforeunload = null;
			}
		}
	});
}

function resetMultipleClozeAnswers() {
	$j.ajax({
		url: window.location.href,
		data: postVariable+"[operation]=reset",
		type: 'POST',
		dataType: 'text',
		async: false,
		complete: function(data) {
			window.location.reload();
		}
	});
}

$j(function () {
	$j('.rows_label, .cols_label').typing({
		start: function (event, $elem) {
			typing.push(1);
			window.onbeforeunload = confirmExit;
		},
		stop: function (event, $elem) {
			typing.pop();
			if (typing.length == 0) {
				saveMultipleClozeAnswers();
			}
		},
		delay: 250
	});
});