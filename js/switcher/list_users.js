/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function initDoc(){
    createDataTable();
}


function createDataTable() {
    
    var oTable = $j('#table_users').dataTable({
        "bJQueryUI": true,
        'bLengthChange': false,
        "bFilter": true,
        "bInfo": false,
        "bSort": true,
        "bAutoWidth": true,
        'bDeferRender': true,
        'aoColumnDefs': [{ "bSortable": false, "aTargets": [ 4 ] } ],
        'bPaginate': false

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
        this.src = HTTP_ROOT_DIR+"/layout/"+ADA_TEMPLATE_FAMILY+"/img/details_close.png";
        oTable.fnOpen( nTr, fnFormatDetails(nTr), 'details' );
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
     $j.ajax({
       type	: 'POST',
       url	: HTTP_ROOT_DIR+ '/switcher/ajax/get_studentDetails.php',
       data	: data,
       dataType :'json'
       })
       .done   (function( JSONObj )
       {
           
       })
       .fail   (function() { 
            console.log("ajax call has failed"); 
	} )
    var sOut=null;
//    var sOut = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
//    sOut += '<tr><td>'+aData[descriptionCol]+'</td></tr>';
//    sOut += '</table>';
    return sOut;
}
}

