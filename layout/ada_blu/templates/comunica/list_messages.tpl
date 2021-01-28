<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <!-- link rel="stylesheet" href="../../../css/main/default/default.css" type="text/css" -->
    <link rel="stylesheet" href="../../../css/main/default/default.css" type="text/css">
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
        <i18n>messaggeria</i18n>
    </span>
        </div>
        <!-- / percorso -->
        <!-- contenitore -->
        <div id="container">
            <!--dati utente-->
            <div id="user_wrap">
                <!-- label -->
                <div id="label">
                    <div class="topleft">
                        <div class="topright">
                            <div class="bottomleft">
                                <div class="bottomright">
                                    <div class="contentlabel">
                                        <h1>
                                            <template_field class="template_field" name="label">label</template_field>
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /label -->
            </div>
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
                        <div>
                            <template_field class="template_field" name="messages">messages</template_field>
                        </div>
                    </div>
                </div>
            </div>
            <!--  / contenuto -->
        </div>
        <!-- / contenitore -->
        <div id="push"></div>
    </div>

    </div>
    <div class="clearfix"></div>
    <!-- PIEDE -->
    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->
</body>

</html>
