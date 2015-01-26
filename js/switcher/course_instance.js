/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var datatable;
function initDoc()
{
    createDataTable();
  
    displayDiv();  initToolTips();
}
function createDataTable()
{
    /* get lexicographical order */
    $j.extend( $j.fn.dataTableExt.oSort, {
    "string-pre": function ( selectHTML ) {
    	var el = document.createElement('div');
        el.innerHTML = selectHTML;  
        var selectEl = el.getElementsByTagName("span")[0];
        var valueId=$j(selectEl).attr('id');
        if (valueId.length>0) {
            return  valueId ;		
        } else return 0;
    },
 
    "string-asc": function ( a, b ) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
 
    "string-desc": function ( a, b ) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
    });
    datatable = $j('#course_instance_Table').dataTable({
    "bJQueryUI": true,
    "bFilter": true,
    "bInfo": true,
    "bSort": true,
    "bAutoWidth": false,
    "bPaginate" : true,
    "aoColumnDefs": [
        {
           "aTargets": [ 0 ], 
           "bVisible":false,
        },
        {
           "aTargets": [ 1 ], 
           "sClass": "Id_Column",
        },
        {
           "aTargets": [ 2 ], 
           "sClass": "Name_Column",
           "sType":"string",
        },
        {
           "aTargets": [ 3 ], 
           "sClass": "Status_Column",
           "sType": "select",
           "bSearchable":false
        },
        {
           "aTargets": [ 4 ], 
           "bVisible":false,
           "bSearchable":false
        },
        
        {
           "aTargets": [ 5 ], 
           "sClass": "Date_Column",
           "sType":"date-eu"
        },
        {
           "aTargets": [ 6 ], 
           "sClass": "Level_Column",
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
}
function  initToolTips()
 {
   $j(document).tooltip({
        items:  'span[class="UserName tooltip"]',
        show :     {
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
        content: function(){
            if ('undefined' != typeof $j(this).attr('id') && 'undefined' != $j('#user_tooltip_'+$j(this).attr('id'))) {
                return $j('#user_tooltip_'+$j(this).attr('id')).attr('title');
            }            
            return null;
        }
        
    }); 
}
 
 function displayDiv()
{
    $j('.table_result').animate({"height": "toggle"});
    $j('.table_result').animate({'marginLeft':'0'},"slow");
}
function saveStatus(select)
{
   
    var SelectRow = $j(select).parents('tr')[0];
    var indexRow=datatable.fnGetPosition($j(select).parents('tr')[0]);
    var aData = datatable.fnGetData( SelectRow );
    var idUser=null;
    var idInstance=null;
    var indexColumn=null;
     
    $j.each(aData,function(i,val){
        
        if($j(val).attr('class')==='idUser'){
            idUser=$j(val).text();
        }
        if($j(val).attr('class')==='id_instance'){
            idInstance=$j(val).text();
        }
        if($j(val).attr('class')==='hidden_status'){
            indexColumn=i;
        }
    });
   
    var data = {
        'status' : select.value,
        'id_user': idUser,
        'id_instance': idInstance
    }
     $j.ajax({
       type	: 'POST',
       url	: HTTP_ROOT_DIR+ '/switcher/ajax/updateSubscription.php',
       data	: data,
       dataType :'json'
       })
       .done   (function( JSONObj )
       {
           showHideDiv(JSONObj.title,JSONObj.msg);
           var selectedText = $j(select).find('option[value="'+select.value+'"]').text();
           var cloned = $j(aData[indexColumn]).text(selectedText).clone();
           datatable.fnUpdate(cloned[0].outerHTML, indexRow,indexColumn);
           /* if user status is removed  it delets user column from datatable */
           if(select.value == 3)  
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

