function initDoc()
{
    //$j('input, a.button, button').uniform();
    var showElement=$j('#s_AdvancedForm').val();
    
    if(showElement==1)
    {
        $j("#div_advancedSearch_form").animate({"height": "toggle"}, { duration: 500 });
        $j("#div_simpleSearch_form").animate({"height": "toggle"}, { duration: 500 });
        $j("#labelSimple_search").css("display","none");
        $j("#labelAdvanced_search").css("display","block");
        $j('#s_AdvancedForm').val("0");
     }
  
    
    dataTablesExec();
}

function dataTablesExec() {
    var datatable = $j('#table_result').dataTable({"oLanguage": { "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"}});
}



function advancedSearch()
{
   $j("#div_advancedSearch_form").animate({"height": "toggle"}, { duration: 500 });
   $j("#div_simpleSearch_form").animate({"height": "toggle"}, { duration: 500 });
   $j("#labelSimple_search").css("display","none");
   $j("#labelAdvanced_search").css("display","block");
    
}
function simpleSearch()
{
    $j("#div_advancedSearch_form").animate({"height": "toggle"}, { duration: 500 });
    $j("#div_simpleSearch_form").animate({"height": "toggle"}, { duration: 500 });
    $j("#labelSimple_search").css("display","block");
    $j("#labelAdvanced_search").css("display","none");
}

function disableForm()
{
   $j('#s_AdvancedForm').val("1");
}
