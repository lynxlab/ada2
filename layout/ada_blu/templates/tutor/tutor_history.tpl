<html>

<head>
    <link rel="stylesheet" href="../../css/tutor/default.css" type="text/css">
</head>

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
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
                <span>
                    <template_field class="template_field" name="path">path</template_field>
                </span>
            </div>
            <!-- / percorso -->
            <div id="status_bar">
                <div class="user_data_default status_bar">
                    <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
                    <span>
                        <template_field class="template_field" name="message">message</template_field>
                    </span>
                </div>
                <div id="label">
                    <!--    <div class="topleft">
                        <div class="topright">
                            <div class="bottomleft">
                                <div class="bottomright">

                                    <div class="contentlabel">
                -->
                    <h1>
                        <template_field class="template_field" name="label">label</template_field>
                    </h1>
                    <!--                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                -->
                </div>

            </div>
            <!-- contenuto -->
            <div id="content" class="content_view">
                <div id="contentcontent" class="contentcontent_view">
                    <div id="help">
                        <template_field class="template_field" name="help">help</template_field>
                    </div>
                    <div>
                        <i18n>Corsista:</i18n>
                        <template_field class="template_field" name="student">student</template_field>
                    </div>
                    <div>
                        <i18n>Corso:</i18n>
                        <template_field class="template_field" name="course_title">course_title</template_field>
                    </div>
                    <div>
                        <template_field class="template_field" name="history">history</template_field>
                    </div>
                </div>
            </div>
            <!--  / contenuto -->

            <!-- menudestra -->
            <!-- / menudestra  -->
        </div>
        <!-- / contenitore -->
        <div id="push"></div>
    </div>
    <!-- com_tools -->
    <div class="clearfix"></div>
    <div id="com_tools">
        <div id="com_toolscontent">
            <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
        </div>
    </div>
    <!-- /com_tools -->
    <!-- PIEDE -->
    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->

</body>

</html>
