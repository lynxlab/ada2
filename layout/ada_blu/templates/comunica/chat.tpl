<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>

<head>
    <!-- link rel="stylesheet" href="../../../css/comunica/default/default.css" type="text/css" -->
    <link rel="stylesheet" href="../../../css/comunica/claire/default.css" type="text/css">
</head>

<body>
    <a name="top">
    </a>
    <div id="header">
        <template_field class="microtemplate_field" name="header">header</template_field>
    </div>
    <!-- menu -->
    <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>
    <!-- / menu -->
    <!-- contenitore -->
    <div id="container">
        <! -- PERCORSO -->
        <div id="journey" class="ui tertiary inverted teal segment">
            <i18n>dove sei: </i18n>
            <span>
            		 			 <i18n>chat</i18n>
            		 </span>
        </div>
        <!-- / percorso -->
        <div id="user_wrap">
            <div id="label">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
                                    <h1>
                                        <i18n>chatroom</i18n>
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- dati utente -->
            <div id="status_bar">
                <div class="user_data_default status_bar">
                    <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
                    <template_field class="microtemplate_field" name="user_more_data_micro">user_more_data_micro</template_field>
                </div>
            </div>
            <!-- / dati utente -->

            <!-- label -->
            <!-- /label -->

            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent">
                    <div class="first">
                        <template_field class="template_field" name="chat">chat</template_field>

                    </div>
                </div>
                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->
        </div>
        <!-- / contenitore -->
    </div>
    <!-- MENU A TENDINA -->

    </div>
    <!-- / MENU A TENDINA -->

    <!-- PIEDE -->
    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->

</body>

</html>
