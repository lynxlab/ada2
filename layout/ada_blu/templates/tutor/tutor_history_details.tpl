<html>

<head>
    <!-- link rel="stylesheet" href="../../../css/tutor/default/default.css" type="text/css" -->
    <link rel="stylesheet" href="../../../css/tutor/claire/default.css" type="text/css">
</head>

<body>
    <a name="top"></a>
    <div id="pagecontainer">
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div>
        <!-- / testata -->
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
                                            <i18n>Practitioner</i18n>
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
            </div>
            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent">
                    <div class="first">
                        <template_field class="template_field" name="help">help</template_field>
                        <template_field class="template_field" name="class">class</template_field>
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
                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->

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
