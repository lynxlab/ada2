function initDoc(){
    
    var lastCol = $j('table.doDataTable thead th').length;
    var colDefs = null;
    var moreColDefs = null;
    
    colDefs = [{
    	"aTargets": [lastCol-1],
    	"sWidth" : "1%",
    	"bSortable":false,
    	"sClass" : "actionCol"
    },{
    	"aTargets": [lastCol-2],
    	"sWidth" : "1%",
    	"bSortable":false,
    	"sClass" : "actionCol"
    }];
    /**
     * authorReport table must have the last 2 columns as non sortable
     */
    if ($j('#authorTable').length>0) {
    	moreColDefs = [{
	    	"aTargets": [lastCol-3],
	    	"sWidth" : "1%",
	    	"bSortable":false,
	    	"sClass" : "actionCol"
    	},
    	{"aTargets" : [2], "sType":"date-eu" }]
    }
    
    if (colDefs == null) colDefs=[];
    if (moreColDefs != null) for (var x=0; x<moreColDefs.length; x++) colDefs.push(moreColDefs[x]);
    
    datatable = $j('table.doDataTable').dataTable({
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