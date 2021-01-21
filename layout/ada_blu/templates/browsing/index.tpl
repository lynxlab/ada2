<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <link rel="stylesheet" href="../../css/browsing/default.css" type="text/css">
</head>

<body>
    <a name="top"></a>
    <div id="pagecontainer">
        <!--testata-->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div>
        <!--/testata-->
        <!-- menu -->
        <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>
        <!-- / menu -->
        <!--contenutore-->
        <div id="container">
            <!-- PERCORSO -->
            <div id="journey" class="ui tertiary inverted teal segment">
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
                        <template_field class="template_field" name="message">message</template_field>
                    </span>
                </div>
            </div>
            <!--/dati utente-->

            <!--contenuto-->
            <div id="content">
                <div id="contentcontent" class="contentcontent_default content_small">
                    <div class="first">
                    </div>
                    <template_field class="template_field" name="title">title</template_field>
                    <template_field class="template_field" name="index">index</template_field>
                </div>
                <div id="bottomcont">
                </div>
            </div>
            <!-- contenuto -->
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
