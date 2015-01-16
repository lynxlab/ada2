/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function initDoc(isAdmin)
{
    createDataTable(isAdmin===1);
    initToolTips();
    displayDiv();
}
function createDataTable(showElements)
{
    datatable = $j('#table_log_report').dataTable({
        "bJQueryUI": true,
        "bFilter": showElements,
        "bInfo": showElements,
        "bSort": showElements,
        "bAutoWidth": true,
        "bPaginate" : showElements,
        "oLanguage": 
        {
           "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        }
    });
    
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