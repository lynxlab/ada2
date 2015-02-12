jQuery.extend( jQuery.fn.dataTableExt.oSort, {
    "select-pre": function ( selectHTML ) {
    	var el = document.createElement('div');
    	el.innerHTML = selectHTML;    	
    	var selectEl = el.getElementsByTagName("select")[0];
    	if (selectEl.length>0) {
    		return (selectEl.options[selectEl.selectedIndex].text)
    					? selectEl.options[selectEl.selectedIndex].text : 0;    		
    	} else return 0;
    },
 
    "select-asc": function ( a, b ) {
        a = a.toLowerCase();
        b = b.toLowerCase();
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
 
    "select-desc": function ( a, b ) {
        a = a.toLowerCase();
        b = b.toLowerCase();
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
} );

jQuery.extend( jQuery.fn.dataTableExt.oSort, {
    "select-by-value-pre": function ( selectHTML ) {
    	var el = document.createElement('div');
    	el.innerHTML = selectHTML;    	
    	var selectEl = el.getElementsByTagName("select")[0];
    	if (selectEl.length>0) {
    		return (selectEl.options[selectEl.selectedIndex].value)
    					? selectEl.options[selectEl.selectedIndex].value : 0;    		
    	} else return 0;
    },
 
    "select-by-value-asc": function ( a, b ) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
 
    "select-by-value-desc": function ( a, b ) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
} );
