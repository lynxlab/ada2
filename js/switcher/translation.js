/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function initDoc()
{
    
}
function showDataTable()
{
    //$j("#translationData").effect("clip");
    /*$j('.translationData').effect('drop', function() {
		$j('.translationResults').effect('slide');
	});*/
   $j.ajax({
        type	: 'POST',
        url	: HTTP_ROOT_DIR+ '/switcher/ajax/get_Translation.php',
        data	: $j('form[name="translatorForm"]').serialize(),
        dataType:'json',
        async	: false
        })   
       .done   (function( JSONObj )
       {
            var content=JSONObj.html;
            
            $j('.translationResults').html(content); 
            createDataTable();
            
            $j('.translationData').effect('drop', function() {
                    $j('.translationResults').effect('slide');
            });
            
            $j('#torna').toggle();
            $j('#home').css('display','none');
            $j('#question_mark').css('display','none');
      })
   return false;
}
function createDataTable()
{
  var oTable;
  $j(document).ready(function() {
   oTable= $j('#table_result').dataTable( {
        "aoColumnDefs": [
            {
               "aTargets": [ 0 ], 
               "sClass": "ColumnStyle_first", 
            },
            {
               "aTargets": [ 1 ], 
               "sClass": "ColumnStyle_second", 
            },
            {
               "aTargets": [ 2 ], 
               "sClass": "ColumnStyle_third", 
            },
            {
               "aTargets": [ 3 ],    
               "sClass": "details", 
               "bVisible":false
            }
         
        ],
        "oLanguage": 
            {
                "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
            }
        
        
    } );
    
    $j('#table_result tbody  td img').on('click', function () {
        var nTr = $j(this).parents('tr')[0];    
        if ( oTable.fnIsOpen(nTr) )
        {
            /* This row is already open - close it */
            this.src = HTTP_ROOT_DIR+"/layout/ada_blu/img/details_open.png";
            oTable.fnClose( nTr );
        }
        else
        {
            /* Open this row */
            this.src = HTTP_ROOT_DIR+"/layout/ada_blu/img/details_close.png";
            oTable.fnOpen( nTr, fnFormatDetails(nTr), 'details' );
        }
   });
  
  function fnFormatDetails ( nTr )
{
    var aData = oTable.fnGetData( nTr );
    var sOut = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
    sOut += '<tr><td>'+aData[3]+'</td></tr>';
    sOut += '</table>';
     
    return sOut;
}
  
    
    
} );

}



