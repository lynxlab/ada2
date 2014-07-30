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
               "sClass": "first_Column", 
            },
            {
               "aTargets": [ 1 ], 
               "sClass": "second_Column", 
            },
            {
               "aTargets": [ 2 ], 
               "sClass": "third_Column", 
            },
            {
               "aTargets": [ 3 ],    
               "sClass": "details", 
               "bVisible":false
            },
            {
               "aTargets": [ 4 ],    
               "sClass": "details", 
               "bVisible":false
            },
            {
               "aTargets": [ 5 ],    
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
 initButton();
 
 function initButton()
{
    /*
     * actions button
     */
	
    var button=$j('.third_Column').button({
            icons : {
                    primary : 'ui-icon-pencil'
            },
            text : false
    });
    button.attr('class','buttonTranslate');
    button.click(function ()
    {
       var nTr = $j(this).parents('tr')[0];  
       var aData = oTable.fnGetData( nTr );
       $j('.translationResults').animate({'marginLeft':'2%'});
       $j('.translationResults').animate({'width':'40%'},"slow");
       $j('.EditTranslation').effect('slide');
       $j('.EditTranslation').animate({'marginLeft':'40%'});
       /*$j.ajax({
       type	: 'POST',
       url	: HTTP_ROOT_DIR+ '/switcher/ajax/save_Translation.php',
       data	: $j(aData).serialize(),
       dataType :'json',
       async	: false
       })  */ 

    });
}
    
    
} );

}


