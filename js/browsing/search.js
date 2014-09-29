function initDoc()
{
    //$j('input, a.button, button').uniform();
    var showElement=$j('#s_AdvancedForm').val();
    
    if(showElement==1)
    {
        $j("#div_simpleSearch").animate({"height": "toggle"}, { duration: 0 });
        $j("#div_advancedSearch").animate({"height": "toggle"}, { duration: 0 });
        $j("#span_simpleSearch").toggle();
        $j("#span_advancedSearch").toggle();
        $j('#s_AdvancedForm').val("0");
     }
  
    
    dataTablesExec();
}

function dataTablesExec() {
    var datatable = $j('#table_result').dataTable({
        "bJQueryUI": true,
        "oLanguage": { "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"}});
}



function advancedSearch()
{
   $j("#div_advancedSearch").animate({"height": "toggle"}, { duration: 500 });
   $j("#div_simpleSearch").animate({"height": "toggle"}, { duration: 500 });
   $j("#span_simpleSearch").toggle();
   $j("#span_advancedSearch").toggle();
    
}
function simpleSearch()
{
    $j("#div_advancedSearch").animate({"height": "toggle"}, { duration: 500 });
    $j("#div_simpleSearch").animate({"height": "toggle"}, { duration: 500 });
    $j("#span_advancedSearch").toggle();
    $j("#span_simpleSearch").toggle();
}

function disableForm()
{
   $j('#s_AdvancedForm').val("1");
}
