function initDoc() {
	
    var lastCol = $j('table.doDataTable thead th').length;    
    var colDefs = [
           {"aTargets" : [0], "sWidth":"50%" },
           {"aTargets" : [2,4], "sType":"date-eu" },
           {"aTargets" : [3], "sType":"formatted-num" },
           {"aTargets": [lastCol-1], "sClass" : "actionCol", "bSortable":false}
    ]; 
    
    datatable = $j('table.doDataTable').dataTable({
    	"aaSorting": [[ 2, "desc" ]],
		"bJQueryUI": true,
        "bFilter": true,
        "bInfo": true,
        "bSort": true,
        "bAutoWidth": true,
        "bPaginate" : true,
        "aoColumnDefs": colDefs,
        "oLanguage": {
           "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        },
       "fnDrawCallback":
            function () {
                // put the sort icon outside of the DataTables_sort_wrapper div
                // for better display styling with CSS
                $j(this).find("thead th div.DataTables_sort_wrapper").each(function(){
                sortIcon = $j(this).find('span').clone();
                $j(this).find('span').remove();
                $j(this).parents('th').append(sortIcon);
            });
        }
	});
}