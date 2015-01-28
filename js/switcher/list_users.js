/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function initDoc(){
    createDataTable();
    initToolTips();
}


function createDataTable() {
    /* se non deve comparire il più per i tutor e gli autori fare a initDoc isstudent se è is studente a target[4] e non sercheable sennò target[3] */
    var oTable = $j('#table_users').dataTable({
        "bJQueryUI": true,
        "bFilter": true,
        "bInfo": true,
        "bSort": true,
        "bAutoWidth": true,
        'aoColumnDefs': [{ "bSortable": false, "aTargets": [ 4 ] } ],
        "oLanguage": 
        {
            "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        }
     
    });
    
    $j('.imgDetls').on('click', function () {
    var nTr = $j(this).parents('tr')[0];    
    if ( oTable.fnIsOpen(nTr) )
    {
        /* This row is already open - close it */
        this.src = HTTP_ROOT_DIR+"/layout/"+ADA_TEMPLATE_FAMILY+"/img/details_open.png";
        oTable.fnClose( nTr );
    }
    else
    {
        /* Open this row */
        this.src = HTTP_ROOT_DIR+"/js/include/jquery/ui/images/ui-anim_basic_16x16.gif";
        var imageReference=this;
        $j.when(fnFormatDetails(nTr))
        .done   (function( JSONObj )
       { 
            oTable.fnOpen( nTr, JSONObj.html, 'details' );
            if(JSONObj.status==='OK'){
                $j('.User_table').dataTable({
                "bJQueryUI": true,
                "oLanguage": 
                {
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
        
        
       
    }
   });
     
  function fnFormatDetails ( nTr )
{
    var aData = oTable.fnGetData( nTr );
    var idUser=null;
    
    $j.each(aData,function(i,val){
        
        if('undefined' != typeof $j(val).attr('class') && $j(val).attr('class')==='id_user'){
            idUser=$j(val).text();
        }
        
    });
    
    var data = {
        'id_user': idUser,
    }
    return $j.ajax({
       type	: 'GET',
       url	: HTTP_ROOT_DIR+ '/switcher/ajax/get_userDetails.php',
       data	: data,
       dataType :'json'
       });
       

}
}
function  initToolTips()
 {
   $j('.tooltip').tooltip({
        
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
        }

        
    }); 
}

