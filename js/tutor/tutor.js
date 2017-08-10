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
    
    var lastCol = $j('table.doDataTable thead th').length;
    var colDefs = null;
    var moreColDefs = null;
    
    /**
     * listTutors table must have the last column as sortable
     * and the first non sortable with its own class and width
     */
    if ($j('#listTutors').length<=0) {
    	colDefs = [{
    		"aTargets": [lastCol-1],
    		"bSortable":false
    	}]; 
    } else {
    	colDefs = [{
    		"aTargets": [0],
    		"bSortable":false,
    		"sClass" : "actionCol",
    		"sWidth" : "1%"
    	}];
    }
    
    if ($j('#listCourses').length>0) {
    	// column definitions for list courses table
    	moreColDefs = [
    	                   {"aTargets" : [4], "sType":"date-eu" },
    	                   {"aTargets" : [5], "sType":"formatted-num" },
    	                   {"aTargets" : [lastCol-1], "sClass" : "actionCol" }
    	                   ];
    } else if ($j('#tutorCommunitiesTable').length>0) {
    	// column definitions for tutor communities table
    	moreColDefs = [
    	                   {"aTargets" : [2,4], "sType":"date-eu" },
    	                   {"aTargets" : [3], "sType":"formatted-num" },
    	                   {"aTargets" : [lastCol-1], "sClass" : "actionCol" }
    	                   ];
    }
    initToolTips();
    
    if (colDefs == null) colDefs=[];
    if (moreColDefs != null) for (var x=0; x<moreColDefs.length; x++) colDefs.push(moreColDefs[x]);
    datatable = $j('table.doDataTable').dataTable({
        "bFilter": true,
        "bInfo": true,
        "bSort": true,
        "bAutoWidth": true,
        "bPaginate" : true,
        "aoColumnDefs": colDefs,
        "oLanguage": {
           "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
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
        		// strip off '&mode=update' and re-append it to be
        		// sure it will not be concatenade more than once
        		$j.ajax({
					type	:	'GET',
					url		:	document.URL.replace(/&mode=update/g,"") +'&mode=update'
        		});
        		// self.document.location.href = document.URL.replace(/&mode=update/g,"") +'&mode=update';
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

var openedRow = null;
function toggleTutorDetails(tutor_id,imgObj) {
  var closeOpenedRowOnClick = true;
  
  var nTr = $j(imgObj).parents('tr')[0];
  var oTable = $j(nTr).parents('table').dataTable();
  
  if (closeOpenedRowOnClick && openedRow!=null && oTable.fnIsOpen(openedRow)) {
		$j(openedRow).find('td.actionCol > img').attr('src',HTTP_ROOT_DIR+"/layout/"+ADA_TEMPLATE_FAMILY+"/img/details_open.png");
		oTable.fnClose(openedRow);
  } 
  
  if (!closeOpenedRowOnClick || openedRow != nTr) {
	  openedRow = nTr;
      /* Open this row */
      imgObj.src = HTTP_ROOT_DIR+"/js/include/jquery/ui/images/ui-anim_basic_16x16.gif";
      var imageReference=imgObj;
      $j.when(getTutorDetails(tutor_id))
      .done   (function( JSONObj ) { 
          oTable.fnOpen( nTr, JSONObj.html, 'details' );
          if(JSONObj.status==='OK'){
              $j('.tutor_table').not('.dataTable').dataTable({
	              'aoColumnDefs': JSONObj.columnDefs,
	              "oLanguage": {
	                    "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
	              } 
              });
          }
     })
     .fail   (function() { 
          console.log("ajax call has failed"); 
	} )
      .always(function (){
          imageReference.src = HTTP_ROOT_DIR+"/layout/"+ADA_TEMPLATE_FAMILY+"/img/details_close.png";
      });
  } else openedRow = null;
}

function getTutorDetails ( idTutor ) {
    return $j.ajax({
       type	: 'GET',
       url	: 'ajax/get_tutorDetails.php',
       data	: {'id_tutor': idTutor},
       dataType :'json'
       });
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

/**
 * inits the tooltips
 */
function initToolTips() {
	// inizializzo i tooltip sul title di ogni elemento!
	if ($j('.tooltip').length>0) {
		$j('.tooltip').tooltip(
				{
					show : {
						effect : "slideDown",
						delay : 300,
						duration : 100
					},
					hide : {
						effect : "slideUp",
						delay : 100,
						duration : 100
					},
					position : {
						my : "center bottom-5",
						at : "center top"
					}
				});
	}
}