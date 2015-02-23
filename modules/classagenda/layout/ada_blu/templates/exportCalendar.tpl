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
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div>
        <!-- / testata -->
        <!-- contenitore -->
        <h1><i18n>Calendario per il corso</i18n>: <template_field class="template_field" name="coursename">coursename</template_field></h1>
        <h2><i18n>Classe</i18n>: <template_field class="template_field" name="instancename">instancename</template_field></h2>
        <div id="container">
            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent">
                    <template_field class="template_field" name="data">data</template_field>
                </div>
            </div>
            <!--  / contenuto -->
        </div>
        <!-- / contenitore -->
    </body>
</html>