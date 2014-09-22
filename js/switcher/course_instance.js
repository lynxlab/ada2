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
        "oLanguage": 
         {
            "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
         },
         "aoColumnDefs": [
            {
               "aTargets": [ 0 ], 
               "bVisible":false,
            },
            {
               "aTargets": [ 1 ], 
               "sClass": "Name_Column", 
            },
            {
               "aTargets": [ 2 ], 
               "sClass": "idUser_Column",
               "bVisible":false,
            },
            {
               "aTargets": [ 3 ], 
               "sClass": "istanceId_Column",
               "bVisible":false,
            },
            {
               "aTargets": [ 4 ], 
               "sClass": "Status_Column", 
            },
            {
               "aTargets": [ 5 ], 
               "sClass": "Date_Column", 
            },
            {
               "aTargets": [ 6 ],    
               "sClass": "Levell_Column", 
            },
            {
               "aTargets": [ 7 ],    
               "sClass": "Code_Column", 
            },
            {
               "aTargets": [ 8 ],    
               "sClass": "Certificate_Column", 
            }
      ],
        
    });
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
     $j(".table_result").animate({"height": "toggle"}, { duration: 1000 })
 }
function saveStatus(selectedValue)
{
    var SelectRow = $j(selectedValue).parents('tr')[0];  
    var aData = datatable.fnGetData( SelectRow );
    
    var data = {
        'status' : selectedValue.value,
        'id_user': aData[2],
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
   // alert(aData[2]+'  hhhh  ' +aData[3]);
}
function showHideDiv ( title, message )
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
        },200);
    
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
