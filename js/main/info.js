function initDoc(multiprovider) {
	// details-control, courseId and course title
	var columnsConf = [{ "sClass": "details-control", "bSearchable": false, "bSortable": false },
		{ "bVisible": false },{ "sWidth": (multiprovider ? "50%" : "94%")}];

	// provider name
	if (multiprovider) {
		columnsConf.push({ "sWidth": "44%" });
	}
	// course description
	columnsConf.push({"bVisible": false });
	// course credits
	columnsConf.push({"bVisible": false });
	// more info link
	columnsConf.push({ "bSearchable": false, "bSortable": false, "sClass":"actions", "sWidth": "6%"});
	// instances string, json encoded
	columnsConf.push({"bVisible": false });

	var infotable = $j("#infotable").DataTable({
		"bSort": true,
        "bAutoWidth": false,
        "bInfo" : false,
        "bPaginate" : true,
        "aoColumns": columnsConf,
        "aaSorting": [[ 1, "desc" ]],
	    "bLengthChange": false,
        "oLanguage": {
           "sUrl": HTTP_ROOT_DIR + "/js/include/jquery/dataTables/dataTablesLang.php"
        },
	    "sPaginationType": "full_numbers",
	    "fnDrawCallback": function() {
	    	var targetTable = $j("#"+$j(this).attr('id')+"_paginate");
	    	// remove pagination if only one page
	    	if (targetTable.children('span').children("a").length<=1) {
	    		targetTable.hide();
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
	    		});
	    		targetTable.show();
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

	$j('#infotable').on('click', 'tbody>tr>td:not(.details-control)', function() {
		// hook all cells click to more_info_link click
		var tr = $j(this).closest('tr');
		if  ($j('a.more_info_link', tr).attr('href').trim().length>0) {
			document.location.href = $j('a.more_info_link', tr).attr('href').trim();
		}
	})
	.on('click', 'tbody>tr>td.details-control', function () {
		// hook details-control cell click to open child row
		var tr = $j(this).closest('tr');
		var row = infotable.row(tr);
		if (row.child.isShown()) {
			$j('i.icon', $j(this)).removeClass('minus red').addClass('add green');
			tr.removeClass('details');
			row.child.hide();
		} else {
			// json encoded instances data must be at very last column
			var idx = 'undefined' !== typeof row.data() ? row.data().length-1 : null;
			if (null !== idx && row.data()[idx].length>0) {
				try {
					var instances = JSON.parse(row.data()[idx]);
					var childHtml = [];
					for (var i=0; i<instances.length; i++) {
						if (i==0) {
							childHtml.push('<h3>'+$j('#listinstance-title').html()+'</h3>');
							childHtml.push('<ul>');
						}
						childHtml.push('<li>');
						childHtml.push('<span class="title">'+instances[i].title+'</span>');
						childHtml.push('<span class="separator">,</span>');
						childHtml.push('<span class="from-txt">'+$j('#listinstance-from-txt').html().toLowerCase()+'</span>');
						childHtml.push('<span class="from-date"> '+instances[i].data_inizio_previsto+'</span>');
						childHtml.push('<span class="to-txt">'+$j('#listinstance-to-txt').html().toLowerCase()+'</span>');
						childHtml.push('<span class="to-date"> '+instances[i].data_fine+'</span>');
						if (instances[i].isstarted && !instances[i].isended) {
							childHtml.push('<span class="ui small green label started"><a href="info.php?op=subscribe'+
								'&provider='+instances[i].provider+
								'&course='+instances[i].id_corso+
								'&instance='+instances[i].id_istanza_corso
								+'">'+$j('#listinstance-subscribe-txt').html().toLowerCase()+'</a></span>');
						} else if (instances[i].isended) {
							childHtml.push('<span class="ui small red label ended">'+$j('#listinstance-ended-txt').html().toLowerCase()+'</span>');
						}
						childHtml.push('</li>');
						if (i==instances.length-1) childHtml.push('</ul>');
					}
					$j('i.icon', $j(this)).removeClass('add green').addClass('minus red');
					tr.addClass('details');
					row.child(childHtml.join(''), 'instanceDetails').show();
				} catch (e) {
					console.error('JSON parse error: ', e);
					return false;
				}
			}
		}
	});
}
