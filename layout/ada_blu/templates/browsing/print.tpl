<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <link rel="stylesheet" href="../../css/browsing/default.css" type="text/css">
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
            <!-- percorso -->
            <div id="journey" class="ui tertiary inverted teal segment">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
                <span> > </span>
                <span>
                    <template_field class="template_field" name="path">path</template_field>
                </span>
            </div>
            <!-- / percorso -->

            <!--dati utente-->
            <div id="status_bar">
                <div class="user_data_default status_bar">
                    <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
                    <template_field class="microtemplate_field" name="user_more_data_micro">user_more_data_micro</template_field>
                </div>
            </div>
            <!-- / dati utente -->

            <!-- contenuto -->
            <div id="content_view" class="content_small">
                <div id="contentcontent" class="contentcontent_view">

                    <div class="firstnode">
                        <h1 class="ui red header">
                            <template_field class="template_field" name="title">title</template_field>
                        </h1>
                        <div class="ui divider"></div>
                        <template_field class="template_field" name="text">text</template_field>
                    </div>
                    <hr>
                    <div id="index_in_text">
                        <h3>
                            <i18n>note di classe</i18n>
                        </h3>
                        <template_field class="template_field" name="notes">notes</template_field>
                        <!--h3><i18n>Approfondimenti:</i18n></h3-->
                        <!--template_field class="template_field" name="index">index</template_field-->
                    </div>
                    <div id="exercises_in_text">
                        <h3>
                            <i18n>note personali</i18n>
                        </h3>
                        <template_field class="template_field" name="personal">personal</template_field>
                        <!--template_field class="template_field" name="exercises">exercises</template_field-->
                    </div>
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
