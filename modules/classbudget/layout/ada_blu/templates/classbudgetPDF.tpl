<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <body>
<script type="text/php">

if ( isset($pdf) ) {

  $font = Font_Metrics::get_font("verdana");
  // If verdana isn't available, we'll use sans-serif.
  if (!isset($font)) { Font_Metrics::get_font("sans-serif"); }
  $size = 8;
  $color = array(0,0,0);
  $text_height = Font_Metrics::get_font_height($font, $size);

  $foot = $pdf->open_object();
  
  $w = $pdf->get_width();
  $h = $pdf->get_height();

  // Draw a line along the bottom
  $y = $h - 2 * $text_height - 24;
  $pdf->line(16, $y, $w - 16, $y, $color, 1);

  $y += $text_height;

  $text = $GLOBALS['adafooter'];
  $pdf->text(16, $y, $text, $font, $size, $color);

  $text = translateFN("Pagina")." {PAGE_NUM} ".translateFN("di")." {PAGE_COUNT}";  

  // Center the text
  $width = Font_Metrics::get_text_width($text, $font, $size);
  $pdf->page_text($w - $width + 80, $y, $text, $font, $size, $color);

  $pdf->close_object();
  $pdf->add_object($foot, "all");
  
}
</script>    
        <a name="top"></a>
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div>
        <!-- / testata -->
        <!-- menu -->
            <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>  
        <!-- / menu -->
        <!-- contenitore -->
        <div id="container">
            <!-- PERCORSO -->
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="title">title</template_field>
                </span>
            </div>
            <div id="user_wrap">
            <!--dati utente-->
                <div id="status_bar">
                    <div id="user_data" class="user_data_default">
                        <i18n>utente: </i18n>
                        <span>
                            <template_field class="template_field" name="user_name">user_name</template_field>
                        </span>
                        <i18n>tipo: </i18n>
                        <span>
                            <template_field class="template_field" name="user_type">user_type</template_field>
                        </span>
                        <div class="status">
                        <i18n>status: </i18n>
                        <span>
                            <template_field class="template_field" name="status">status</template_field>
                        </span>
                        </div>
                          <i18n>ultimo accesso: </i18n>
                        <span>
                            <template_field class="template_field" name="last_visit">last_visit</template_field>
                        </span>
                        <i18n>livello</i18n>:
                          <span>
                            <template_field class="template_field" name="user_level">user_level</template_field>
                         </span>
                    </div>            
                <!-- / dati utente -->
                <!-- label -->
                <div id="label">
                    <div class="topleft">
                        <div class="topright">
                            <div class="bottomleft">
                                <div class="bottomright">
                                    <div class="contentlabel">
                                        <template_field class="template_field" name="message">message</template_field>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /label -->
                </div>
            </div>

            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent">
                    <div class="first">
                        <div id="help">
                            <template_field class="template_field" name="help">help</template_field>
                        </div>
                        <div id="budgetresumeContainer">
                            <h2>
                            <i18n>Il budget per questa classe &egrave; di</i18n> <template_field class="template_field" name="currency">currency</template_field>
                            <span id="instance-budget" data-instance-budget='<template_field class="template_field" name="budget">budget</template_field>'><template_field class="template_field" name="budgetStr">budgetStr</template_field></span>
                            &nbsp;<i18n>le spese ammontano a</i18n>:
                            &nbsp;<template_field class="template_field" name="currency">currency</template_field>&nbsp;
                            <span id="instance-cost" data-instance-totalcost='<template_field class="template_field" name="totalcost">totalcost</template_field>'><template_field class="template_field" name="totalcostStr">totalcostStr</template_field></span>
                            <br/>(<i18n>differenza</i18n>: 
                            <template_field class="template_field" name="currency">currency</template_field>
                            <span id="instance-balance" class='<template_field class="template_field" name="balanceclass">balanceclass</template_field>' data-instance-balance='<template_field class="template_field" name="balance">balance</template_field>'><template_field class="template_field" name="balanceStr">balanceStr</template_field></span>)
                            </h2>
                        </div>
                        <template_field class="template_field" name="data">data</template_field>
                    </div>
                </div>

                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->
        </div>
        <!-- / contenitore -->

        <!-- piede -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->

    </body>
</html>