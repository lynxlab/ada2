function initDoc(isError) {
	// set element visibility
	if (isError) {
		$j('#courseInfo').remove();
		$j('#errorMSG').show();
	} else {

		// remove empty elements
		$j('.item, .bottom.segment, .dividing.header').each(function(i,el) {
			if ($j(el).text().trim().length<=0) $j(el).remove();
		});

		// remove links and fix course index
		// 01. root node
		$j('#structIndex>ul>li.courseNode>span','#courseInfo').html($j('#structIndex>ul>li.courseNode>span>a','#courseInfo').text().trim());
		// 02. child nodes, first level only
		$j('#structIndex>ul>li.courseNode>ul.structIndex>li.courseNode','#courseInfo').each(function(i,el) {
			if($j(el).children("span").first().children("a").first().length>0) {
				$j(el).html($j(el).children("span").first().children("a").first().text());
			} else {
				$j(el).html($j(el).text().trim());
			}
		});

		$j('#errorMSG').remove();
		$j('#courseInfo').fadeIn();
	}
}