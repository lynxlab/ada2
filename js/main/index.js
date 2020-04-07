logged = false;

function initDoc(logged) {
    $j(function() {
        $j( ".ada-column" ).sortable({
            connectWith: ".ada-column",
            handle: ".portlet-header"
        });
        $j( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
            .find( ".portlet-header" )
                .addClass( "ui-widget-header ui-corner-all" )
                .prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
                .end()
            .find( ".portlet-content" );
        $j( ".portlet-header .ui-icon" ).click(function() {
            $j( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
            $j( this ).parents( ".portlet:first" ).find( ".portlet-content" ).toggle();
        });
//        $j( ".ada-column" ).disableSelection();
//        $j("select, input, a.button, button").uniform();
    });
    if (logged) {
        $j("#loginform").parent().remove();
    } else {
        $j('img[usemap]').rwdImageMaps();
    	$j('#p_username, #p_password').keypress(function(e) {
    		if (e.which == 13) { // return key does a click on first provider login button
    			$j(this).parents('form').find('button').first().click();
    		}
    	});
    }
}