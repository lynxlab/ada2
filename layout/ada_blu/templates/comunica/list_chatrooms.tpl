<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <link rel="stylesheet" href="../../css/comunica/default.css" type="text/css">
</head>

<body>
    <a name="top"></a>
    <div id="pagecontainer">
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header_com">header_com</template_field>
        </div>
        <!-- / testata -->
        <!-- menu -->
        <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>
        <!-- / menu -->
        <!-- contenitore -->
        <div id="container">
            <!-- percorso -->
            <div id="journey_std">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
            </div>
            <!-- / percorso -->

            <!--dati utente-->
            <div id="status_bar">
                <div class="user_data_default status_bar">
                    <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
                    <span>
                        <template_field class="template_field" name="title">title</template_field>
                    </span>
                </div>
            </div>
            <!-- / dati utente -->

            <!-- contenuto -->
            <div id="content_view">
                <div id="contentcontent" class="contentcontent_view">
                    <div id="info_nodo">
                        <span>
                            <template_field class="template_field" name="bookmark">bookmark</template_field>
                        </span>
                    </div>
                    <div class="firstnode">
                        <template_field class="template_field" name="data">data</template_field>
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
    <!-- notifiche eventi -->
    <template_field class="template_field" name="events">events</template_field>
    <!-- / notifiche eventi -->
    </div>
    <!-- / menu a tendina -->
    <!-- pannello video -->
    <div id="rightpanel" class="sottomenu_off rightpanel_view">
        <div id="toprightpanel">
        </div>
        <div id="rightpanelcontent">
            <ul>
                <li class="close">
                    <a href="#" onClick="hideElement('rightpanel', 'right');">
                        <i18n>chiudi</i18n>
                    </a>
                </li>
                <li id="flvplayer">
                </li>
            </ul>
        </div>
        <div id="bottomrightpanel">
        </div>
    </div>
    <!-- / pannello video -->

    <!-- com_tools -->
    <div class="clearfix"></div>
    <div id="com_tools">
        <div id="com_toolscontent">
            <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
        </div>
    </div>
    <!-- /com_tools -->

    <!-- piede -->
    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->
</body>

</html>
