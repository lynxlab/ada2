<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <link rel="stylesheet" href="../../../css/comunica/masterstudio_stabile/default.css" type="text/css">
</head>

<body>
    <a name="top"></a>
    <div id="pagecontainer">
        <div id="header">
            <template_field class="microtemplate_field" name="header_com">header_com</template_field>
        </div>
        <!-- menu -->
        <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>
        <!-- / menu -->
        <!-- PERCORSO -->
        <div id="journey" class="ui tertiary inverted teal segment">
            <i18n>dove sei: </i18n>
            <span>
        <i18n>agenda</i18n>
    </span>
        </div>
        <!-- / percorso -->
        <!-- contenitore -->
        <div id="container">
            <div id="user_wrap">
                <!-- label -->
                <div id="label">
                    <div class="topleft">
                        <div class="topright">
                            <div class="bottomleft">
                                <div class="bottomright">
                                    <div class="contentlabel">
                                        <h1>
                                            <i18n>appuntamenti</i18n>
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /label -->
                <!--dati utente-->
                <div id="status_bar">
                    <div class="user_data_default status_bar">
                        <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
                        <span>
                            <template_field class="template_field" name="message">message</template_field>
                        </span>
                    </div>
                </div>
                <!-- / dati utente -->

                <!-- contenuto -->
                <div id="content">
                    <div id="contentcontent">
                        <div class="first">
                            <i18n>appuntamenti: </i18n>
                            <div>
                                <template_field class="template_field" name="messages">messages</template_field>
                            </div>
                            <div>
                                <template_field class="template_field" name="menu_02">menu_02</template_field>
                            </div>
                        </div>
                    </div>
                    <div id="bottomcont">
                    </div>
                </div>
                <!--  / contenuto -->
            </div>
            <!-- / contenitore -->
            <div id="push"></div>
        </div>

    </div>
    <!-- PIEDE -->
    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->
</body>

</html>
