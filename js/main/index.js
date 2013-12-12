function initDoc() {
    $j(function() {
        $j( ".column" ).sortable({
            connectWith: ".column",
//            handle: ".porlet-header"
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
//        $j( ".column" ).disableSelection();
        $j("select, input, a.button, button").uniform();
    });
}