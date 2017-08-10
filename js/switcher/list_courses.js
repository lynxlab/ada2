/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function initDoc(filter)
{
    createDataTable(filter);
    initToolTips();
    displayDiv();
}
function createDataTable(filter)
{
    var lastCol = $j('#table_list_courses thead th').length-1;
    var descriptionCol=lastCol-1;
    var titleCol=lastCol-2;
    oTable = $j('#table_list_courses').dataTable({
        "bJQueryUI": false,
        "bFilter": true,
        "bInfo": true,
        "bSort": true,
        "bAutoWidth": true,
        "bPaginate" : true,
        "aaSorting": [[ 1, "asc" ]],
        "aoColumnDefs": [
        {
            "aTargets": [0],
            "bSortable":false
        },
        {
            "aTargets": [descriptionCol],
            "bSortable":false,
            "bVisible":false
        },
        {
            "aTargets": [lastCol],
            "bSortable":false,
            "sClass": "action_Column"
        }
        ],
        "oSearch" : { 
            "sSearch" : ('undefined' !== typeof filter && filter.length>0) ? filter : ''
	},
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
        this.src = HTTP_ROOT_DIR+"/layout/"+ADA_TEMPLATE_FAMILY+"/img/details_close.png";
        oTable.fnOpen( nTr, fnFormatDetails(nTr), 'details' );
    }
   });
     
  function fnFormatDetails ( nTr )
{
    var aData = oTable.fnGetData( nTr );
    var sOut = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
    sOut += '<tr><td>'+aData[descriptionCol]+'</td></tr>';
    sOut += '</table>';
    return sOut;
}

    
}
function initToolTips(){
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
function displayDiv(){
    $j('#data').animate({"height": "toggle"});
    $j('#data').animate({'marginLeft':'0'},"slow");
}