/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var datatable;
var id_Course;
var id_Course_instance;

function initDoc(id_course, id_course_instance){
    id_Course=id_course;
    id_Course_instance=id_course_instance;
    
    var lastCol = $j('#table_Report thead th').length;
    
    datatable = $j('#table_Report').dataTable({
		"bJQueryUI": true,
        "bFilter": true,
        "bInfo": true,
        "bSort": true,
        "bAutoWidth": true,
        "bPaginate" : true,
        "aoColumnDefs": [
            {
            	"aTargets": [lastCol-1],
            	"bSortable":false
            }
        ],
        "oLanguage": {
           "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        },
       "fnDrawCallback":
            function () {
                // put the sort icon outside of the DataTables_sort_wrapper div
                // for better display styling with CSS
                $j(this).find("thead th div.DataTables_sort_wrapper").each(function(){
                sortIcon = $j(this).find('span').clone();
                $j(this).find('span').remove();
                $j(this).parents('th').append(sortIcon);
            });
        }
	});

    initButton();
}

function initButton() {
	/**
	 * actions button
	 */
	if ($j('.button_Increase').length>0) {
		$j('.button_Increase').button({
			icons : {
				primary : 'ui-icon-plus'
			},
			text : false
		});
	}
	
	if ($j('.button_Decrease').length>0) {
		$j('.button_Decrease').button({
			icons : {
				primary : 'ui-icon-minus'
			},
			text : false
		});
	}
}

function updateLevel(id_student, step, forceUpdate){
    var level = parseInt($j('#studentLevel_'+id_student).text())+step;

    var data = {
        'level'  : level,
        'id_student': id_student,
        'id_instance': id_Course_instance,
        'id_course' : id_Course
    };
    $j.ajax({
	    type     : 'POST',
	    url	: HTTP_ROOT_DIR+ '/tutor/ajax/UpdateStudent_level.php',
	    data     : data,
	    dataType :'json'
    })
    .done (function(JSONObj) {
        showHideDiv(JSONObj.title,JSONObj.msg);
        if(JSONObj.status == 'OK') {
        	$j('#studentLevel_'+id_student).text(level);
        	updateAverageLevel();
        	if (forceUpdate) {
        		self.document.location.href = document.URL +'&mode=update';
        	}
        }
    })
    .fail (function() {
    	console.log("ajax call has failed"); 
     });
}

function updateAverageLevel() {
	var total = 0;
	var elements = 0;
	$j("span[id^='studentLevel_']").each(function(index, element) {
		elements++;
		total += parseInt($j(element).text());
	});
	// output rounded average
	$j('#averageLevel').text(Math.round((total/elements) * 100) / 100);
}

function showHideDiv ( title, message)
{
    var theDiv = $j("<div id='ADAJAX' class='saveResults'><p class='title'>"+title+"</p><p class='message'>"+message+"</p></div>");
    theDiv.css("position","fixed");
    theDiv.css("width", "350px");
    theDiv.css("top", ($j(window).height() / 2) - (theDiv.outerHeight() / 2));
    theDiv.css("left", ($j(window).width() / 2) - (theDiv.outerWidth() / 2));	
    theDiv.hide().appendTo('body').fadeIn(500).delay(2000).fadeOut(500, function() { 
    theDiv.remove(); 
    if (typeof reload != 'undefined' && reload) self.location.reload(true); });
    
}