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
     * authorTable table must have the last 3 columns as non sortable
     */
    if ($j('#authorTable').length>0) {
    	moreColDefs = [{"aTargets": [lastCol-3], "sWidth" : "1%", "bSortable":false, "sClass" : "actionCol" },
    	               {"aTargets": [2], "sType":"date-eu" }];
    	// this column is an extra column only needed if there's a module with an action
    	if (lastCol-4 > 0) {
    		moreColDefs.push({"aTargets": [lastCol-4], "sWidth" : "1%", "bSortable":false, "sClass" : "actionCol" });
    	}
    } else if ($j('#authorReport').length>0) {
    	moreColDefs = [{"aTargets": [lastCol-3], "sWidth" : "7%", "sClass":"centerAlign" },
    	               {"aTargets": [0], "sType":"formatted-num" }];
    } else if ($j('#authorZoom').length>0) {
    	colDefs = [{"aTargets": [lastCol-1], "sWidth" : "15%", "sClass" : "centerAlign" },
    	           {"aTargets": [0], "sType":"date-eu" }];
    }

    if (colDefs == null) colDefs=[];
    if (moreColDefs != null) for (var x=0; x<moreColDefs.length; x++) colDefs.push(moreColDefs[x]);

    datatable = $j('table.doDataTable').dataTable({
        "bFilter": true,
        "bInfo": true,
        "bSort": true,
        "bAutoWidth": true,
        "bPaginate" : true,
        "aoColumnDefs": colDefs,
        "oLanguage": {
           "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        }
	});
}