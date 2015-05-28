document.write('<script type="text/javascript" src="js/commons.js"></script>');

var datatable;
var openedRow = null;

function initDoc(){
    initDataTable();
    
    if ('undefined' != typeof arguments[0]) {
    	/**
    	 * urldecode passed arguments[0] and turn it into a JSON Object
    	 */
    	var passedObj = JSON.parse(decodeURIComponent(arguments[0].replace(/\+/g, ' ')));
    	
    	/**
		 * select the image having data-testid equal to the
		 * passed openTestID, if one is found open that row
		 */
    	if (passedObj != null && 'undefined' != passedObj.openTestID &&
    			$j('img[data-testid="'+passedObj.openTestID+'"]').length>0) {
    		$j('img[data-testid="'+passedObj.openTestID+'"]').trigger('click');
    	}
    }
}

function toggleRepeatable(id_history_test,repeatable) {
	var loc = window.location.pathname;
	var dir = loc.substring(0, loc.lastIndexOf('/'));
	$j.ajax({
		url: dir+'/tutor_ajax.php?mode=repeatable&id_history_test='+id_history_test+'&repeatable='+repeatable,
		dataType: 'text',
		async: false,
		success: function(data) {
			if (data == '1') {
				if (repeatable) {
					$j('#repeateTestYes').attr('checked',true);
					$j('#repeateTestNo').removeAttr('checked');
				}
				else {
					$j('#repeateTestYes').removeAttr('checked');
					$j('#repeateTestNo').attr('checked',true);
				}
			}
			else {
				if (!repeatable) {
					$j('#repeateTestYes').attr('checked',true);
					$j('#repeateTestNo').removeAttr('checked');
				}
				else {
					$j('#repeateTestYes').removeAttr('checked');
					$j('#repeateTestNo').attr('checked',true);
				}
			}
		},
		error: function() {
			if (!repeatable) {
				$j('#repeateTestYes').attr('checked',true);
				$j('#repeateTestNo').removeAttr('checked');
			}
			else {
				$j('#repeateTestYes').removeAttr('checked');
				$j('#repeateTestNo').attr('checked',true);
			}
		}
	});
}

function saveOpenAnswerPoints(id_answer) {
	points = parseInt($j('#open_answer_test_point_input_'+id_answer).val());
	if (isNaN(points)) {
		points = 0;
	}

	var loc = window.location.pathname;
	var dir = loc.substring(0, loc.lastIndexOf('/'));
	$j.ajax({
		url: dir+'/tutor_ajax.php?mode=points&id_answer='+id_answer+'&points='+points,
		dataType: 'text',
		async: false,
		success: function(data) {
			if (data == '1') {
				$j('#open_answer_test_point_span_'+id_answer).html(points);
				$j('#open_answer_test_point_input_'+id_answer).val(points);
			}
		}
	});
}

function saveCorrectOpenAnswer(id_answer) {
	var textarea = $j('#open_answer_test_point_textarea_'+id_answer);	

	var loc = window.location.pathname;
	var dir = loc.substring(0, loc.lastIndexOf('/'));
	$j.ajax({
		type: "POST",
		data: { answer: textarea.val() },
		url: dir+'/tutor_ajax.php?mode=answer&id_answer='+id_answer,
		dataType: 'text',
		async: false,
		beforeSend: function() {
			textarea.addClass('ajaxloading');
		},
		complete: function() {
			textarea.removeClass('ajaxloading');
		},
		success: function(data) {
			var cssClass;
			if (data == '1') {
				cssClass = 'right_answer_test_bg';
			}
			else {
				cssClass = 'wrong_answer_test_bg';
			}
			textarea.addClass(cssClass);
			setTimeout(function () {
				textarea.removeClass(cssClass);
			}, 2500);
		},
		error: function() {
			var cssClass = 'wrong_answer_test_bg';
			textarea.addClass(cssClass);
			setTimeout(function () {
				textarea.removeClass(cssClass);
			}, 2500);
		}
	});
}

function saveCommentAnswer(id_answer) {
	var textarea = $j('#tutor_comment_textarea_'+id_answer);
	var checkbox = $j('#tutor_comment_checkbox_'+id_answer);
	var checked = (checkbox.is(':checked'));

	var loc = window.location.pathname;
	var dir = loc.substring(0, loc.lastIndexOf('/'));
	$j.ajax({
		type: "POST",
		data: { comment: textarea.val(), notify: checked },
		url: dir+'/tutor_ajax.php?mode=comment&id_answer='+id_answer,
		dataType: 'text',
		async: false,
		beforeSend: function() {
			textarea.addClass('ajaxloading');
		},
		complete: function() {
			textarea.removeClass('ajaxloading');
		},
		success: function(data) {
			var cssClass;
			if (data == '1') {
				cssClass = 'right_answer_test_bg';
			}
			else {
				cssClass = 'wrong_answer_test_bg';
			}
			textarea.addClass(cssClass);
			setTimeout(function () {
				textarea.removeClass(cssClass);
			}, 2500);
		},
		error: function() {
			var cssClass = 'wrong_answer_test_bg';
			textarea.addClass(cssClass);
			setTimeout(function () {
				textarea.removeClass(cssClass);
			}, 2500);
		}
	});
}

function toggleDiv(id) {
	$j('#'+id).toggle();
}
function initDataTable(){
    datatable = $j('.default_table').dataTable({
        "bJQueryUI": true,
        "oLanguage": {
                   "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
                },
       "aoColumnDefs": [{ "bSortable": false, "aTargets": [ 0 ], "sWidth" : "1%" },
                        { "bSortable": false, "aTargets": [ 4 ], "sWidth" : "29%" }],
       "fnDrawCallback": function () {
	                // put the sort icon outside of the DataTables_sort_wrapper div
	                // for better display styling with CSS
	                $j(this).find("thead th div.DataTables_sort_wrapper").each(function(){
			                sortIcon = $j(this).find('span').clone();
			                $j(this).find('span').remove();
			                $j(this).parents('th').append(sortIcon);
		                });
        } 
    });
}

function expandListTestsRow(imgObj, url) {
	
	var closeOpenedRowOnClick = true;
	
    var nTr = $j(imgObj).parents('tr')[0];
    
    if ('undefined' != typeof nTr) {
    	
    	if (closeOpenedRowOnClick && openedRow!=null && datatable.fnIsOpen(openedRow)) {
    		/* This row is already open - close it */
    		$j(openedRow).find('img.noPopup').attr('src',HTTP_ROOT_DIR+"/layout/"+ADA_TEMPLATE_FAMILY+"/img/details_open.png");
    		datatable.fnClose(openedRow);
    	}
    	
    	if (!closeOpenedRowOnClick || openedRow != nTr) {
    		openedRow = nTr;
    		/* Open this row */
    		$j(imgObj).attr('src',HTTP_ROOT_DIR+"/js/include/jquery/ui/images/ui-anim_basic_16x16.gif");    		
    		$j.ajax({
    			url: url,
    			data : { isAjax: 1 },
    			type: 'GET',
    			dataType: 'html'
    		}).done(function(htmlResponse){        	
    			if (htmlResponse!=null) {
    				var nDetailsRow = datatable.fnOpen( nTr, htmlResponse, 'details' );
    				nDetailsRow.className += ' '+nTr.className;
    				$j('table').not('.dataTable').dataTable({
    					"bJQueryUI": true,
    					"oLanguage": {
    						"sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
    					},
    					"aaSorting": [[0,'desc']],
    					"aoColumnDefs": [{ "aTargets": [ 0 ], "sWidth" : "10%" },
    					                 { "aTargets": [ 2 ], "sType" : "date-euro" },
    					                 { "aTargets": [ 3 ], "sType" : "date-euro" },
    					                 { "aTargets": [ 4 ], "bSortable": false, "sWidth" : "1%" }],
    					"fnDrawCallback": function() {
    						// put the sort icon outside of the DataTables_sort_wrapper div
    						// for better display styling with CSS
    						$j(this).find("thead th div.DataTables_sort_wrapper").each(function(){
    							sortIcon = $j(this).find('span').clone();
    							$j(this).find('span').remove();
    							$j(this).parents('th').append(sortIcon);
    						});
    					}
    				});
    			}
    		}).always(function(data){
    			if ('undefined' != typeof data && data.indexOf('index.tpl')!=-1) {
    				alert('Your session has expired! Redirecting to home page');
    				self.document.location.href = HTTP_ROOT_DIR;    				
    			}
    			else if ($j(imgObj).length>0) {
    				$j(imgObj).attr('src',HTTP_ROOT_DIR+"/layout/"+ADA_TEMPLATE_FAMILY+"/img/details_close.png");
    			}
    		});
    	} else openedRow=null;
    }
}