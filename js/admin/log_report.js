/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function initDoc(isAdmin)
{
    createDataTable(isAdmin===1);
    displayDiv();
}
function createDataTable(showElements)
{
    datatable = $j('#table_log_report').dataTable({
        "bJQueryUI": true,
        "bFilter": showElements,
        "bInfo": true,
        "bSort": showElements,
        "bAutoWidth": true,
        "bPaginate" : showElements,
        "oLanguage": 
        {
           "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        }
    });
    
}
function displayDiv(){
    $j('#data').animate({"height": "toggle"});
    $j('#data').animate({'marginLeft':'0'},"slow");
}