<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <body>
        <a name="top"></a>
        <div id="pagecontainer">
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
            <div id="journey" class="ui tertiary inverted teal segment">
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
	                    <script type="application/javascript" src="<template_field class="template_field" name="apiURL">apiURL</template_field>"></script>
    	                <iframe onload="resizeIframe(this)" data-src="<template_field class="template_field" name="launchURL">launchURL</template_field>" id="SCORMWIN"></iframe>
                    </div>
                    
                    <!-- error message -->
                    <div class="ui error icon large message" id="errorMessage">
                      <i class="circular inverted red warning icon"></i>
						  <div class="content">
						    <div class="header">
						      <i18n>Errore</i18n>
						    </div>
						    <p><template_field class="template_field" name="errorMSG">errorMSG</template_field></p>
						  </div>
                    </div>
                    <!-- /error message -->
                    
                </div>
            </div>
            <!--  / contenuto -->
        </div>
        <!-- / contenitore -->
     	<div id="push"></div>
		</div>

        <!-- piede -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->

    </body>
</html>
