/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var datatable;
function initDoc()
{
    createDataTable();
    initToolTips();
    displayDiv();
    
}
function createDataTable()
{
         datatable = $j('#course_instance_Table').dataTable({
        "bJQueryUI": true,
        "aaSorting": [[1,'asc'],[2,'asc'],[4,'asc']],
        "aoColumnDefs": [
            {
               "aTargets": [ 0 ], 
               "sClass": "Id_Column",
               
            },
            {
               "aTargets": [ 1 ], 
               "sClass": "Name_Column",
               "sType":"string",
               //"sSortDataType": "dom-text"
            },
            {
               "aTargets": [ 2 ], 
               "sClass": "Status_Column",
               "sSortDataType": "dom-select",
               "sType": "numeric"
            },
            {
               "aTargets": [ 3 ], 
               "bVisible":false,
            },
            {
               "aTargets": [ 4 ], 
               "sClass": "Date_Column",
               "sType":"date"
            },
            {
               "aTargets": [ 5 ], 
               "sClass": "Levell_Column",
            },
        ],
        "oLanguage": 
         {
            "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
         },
         "fnDrawCallback":
            function () {
                // put the sort icon outside of the DataTables_sort_wrapper div
                // for better display styling with CSS
                $j(this).find("thead th div.DataTables_sort_wrapper").each(function(){
                sortIcon = $j(this).find('span').clone();
                $j(this).find('span').remove();
                
                if(($j(this).text()!='Certificato'))
                {
                    $j(this).parents('th').append(sortIcon);
                }
                
                });
          } 
       });
      
     if(datatable.fnGetData(0).length < 7)
     {
         
         $j('#course_instance_Table').css('width' ,'101%');
     }
    
    
 }
 
 function  initToolTips()
 {
    $j('.tooltip').tooltip({

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
        },
        content: function() {
        return $j(this).attr('title');
        }
   });
  
    
 }
 function displayDiv()
 {
     
    //$j('.table_result').animate({'marginLeft':'1%'});
    //$j('.fg-toolbar.ui-widget-header').animate({'marginLeft':'90%'});
     $j('.table_result').animate({"height": "toggle"});
       $j('.table_result').animate({'marginLeft':'15%'},"slow");
      
 }
function saveStatus(selectedValue)
{
    var SelectRow = $j(selectedValue).parents('tr')[0];  
    var aData = datatable.fnGetData( SelectRow );
   
    var data = {
        'status' : selectedValue.value,
        'id_user': aData[0],
        'id_instance': aData[3]
    }
     $j.ajax({
       type	: 'POST',
       url	: HTTP_ROOT_DIR+ '/switcher/ajax/updateSubscription.php',
       data	: data,
       dataType :'json',
       async	: false
       })
       .done   (function( JSONObj )
       {
           showHideDiv(JSONObj.title,JSONObj.msg);
           /* if user status is removed  it delets user column from datatable */
           if(selectedValue.value == 3)  
           {
               datatable.fnDeleteRow( SelectRow );
           }
       })
       .fail   (function() { 
            console.log("ajax call has failed"); 
	} )
    
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
function goToSubscription()
{
    $j('.table_result').effect('drop', function() {
        $j('#course_instance_Table').effect('slide');
    });
    setTimeout( function(){
            self.document.location.href = 'subscribe.php'+location.search;
        },220);
    
}
function goToSubscriptions()
{
    $j('.table_result').effect('drop', function() {
        $j('#course_instance_Table').effect('slide');
    });
    setTimeout( function(){
            self.document.location.href = 'subscriptions.php'+location.search;
        },200);
}

