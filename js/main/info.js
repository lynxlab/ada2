function initDoc(multiprovider) {
	var columnsConf = [{ "sWidth": (multiprovider ? "50%" : "94%")}];

	if (multiprovider) {
		columnsConf.push({ "sWidth": "44%" });
	}
	columnsConf.push({"bVisible": false });
	columnsConf.push({"bVisible": false });
	columnsConf.push({ "bSearchable": false, "bSortable": false, "sClass":"actions", "sWidth": "6%"});

	var infotable = $j("#infotable").DataTable({
		"bSort": true,
        "bAutoWidth": false,
        "bInfo" : false,
        "bPaginate" : true,
        "aoColumns": columnsConf,
        "aaSorting": [[ 0, "asc" ]],
	    "bLengthChange": false,
        "oLanguage": {
           "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        },
	    "sPaginationType": "full_numbers",
	    "fnDrawCallback": function() {
	    	var targetTable = $j("#"+$j(this).attr('id')+"_paginate");
	    	// remove pagination if only one page
	    	if (targetTable.children('span').children("a").length<=1) {
	    		targetTable.remove();
	    	} else {
	    		// pagination in semantic ui style
	    		if (!targetTable.hasClass('ui pagination menu')) {
	    			targetTable.addClass("right floated ui pagination menu");
	    		}
	    		$j('a',targetTable).each(function(k, v){
	    			$j(v).addClass("item").removeClass("disabled");
	    			if($j(v).hasClass("paginate_active")){
	    				$j(v).addClass("active");
	    			}
	    			if($j(v).hasClass("paginate_button_disabled")){
	    				$j(v).addClass("disabled");
	    			}
	    		})
	    	}
	    },
	    "fnInitComplete": function(settings, json) {

	    	// input filter in semantic ui style
	        var infotable_filter = $j("#"+$j(this).attr('id')+"_filter");
	        var input = infotable_filter.find("input").clone(true);

	        var placeholder = infotable_filter.find("label").text().trim().slice(0,-1);
	        // capitalize first letter
	        placeholder = placeholder.charAt(0).toUpperCase() + placeholder.slice(1);
	        input.attr("placeholder",placeholder);

	        infotable_filter.find("input").remove();
	        infotable_filter.find("label").remove();
	        infotable_filter.append(input);
	        infotable_filter.append('<i class="filter icon"></i>');
	        infotable_filter.children().wrapAll('<div class="ui right floated basic segment"><div class="ui left labeled icon input field"></div></div>');
	        infotable_filter.addClass("ui form");
	        infotable_filter.after("<div class='clearfix'></div>");
	        $j(this).fadeIn();
	    }
	});
}