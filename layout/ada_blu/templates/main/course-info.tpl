<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <link rel="stylesheet" href="../../css/switcher/default.css" type="text/css">
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
            </div>
            <div id="user_wrap">
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
                <!-- label -->
                <div id="label">
                    <div class="topleft">
                        <div class="topright">
                            <div class="bottomleft">
                                <div class="bottomright">
                                    <div class="contentlabel">
                                        <h1 class="ui large dividing header">
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

            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent">
                    <div class="first">
                        <div id="help">
                            <template_field class="template_field" name="help">help</template_field>
                        </div>
                        <template_field class="template_field" name="data">data</template_field>
                        <template_field class="template_field" name="errorMSG">errorMSG</template_field>

                        <div id="courseInfo" class="ui stackable grid">
                            <div class="firstcol <template_field class=" template_field " name="firstcol_wideness ">firstcol_wideness</template_field> column">
                                <!-- course info segment -->
                                <div class="courseinfo">
                                    <div class="ui top attached segment">
                                        <i class="book large icon"></i>
                                        <template_field class="template_field" name="course_title">course_title</template_field>
                                    </div>
                                    <div class="ui attached segment item">
                                        <div class="item">
                                            <template_field class="template_field" name="course_description">course_description</template_field>
                                        </div>
                                    </div>
                                    <div class="ui bottom attached segment">
                                        <div class="item">
                                            <i class="certificate icon"></i>
                                            <template_field class="template_field" name="course_credits">course_credits</template_field>
                                        </div>
                                        <div class="item">
                                            <i class="empty flag icon"></i>
                                            <template_field class="template_field" name="course_language">course_language</template_field>
                                        </div>
                                        <div class="item">
                                            <i class="time icon"></i>
                                            <template_field class="template_field" name="course_duration">course_duration</template_field>
                                        </div>
                                    </div>
                                </div>
                                <!-- /course info segment -->
                                <!-- index and provider grid -->
                                <div class="ui stackable grid">
                                    <div class="two column equal height row">
                                        <div class="column">
                                            <a name="courseIndex"></a>
                                            <div class="courseindex">
                                                <div class="ui top attached segment item">
                                                    <i class="sitemap large icon"></i>
                                                    <i18n>Indice</i18n>
                                                </div>
                                                <div class="ui attached segment">
                                                    <div class="item">
                                                        <template_field class="template_field" name="course_index">course_index</template_field>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="column">
                                            <div class="providerinfo">
                                                <div class="ui top attached segment">
                                                    <i class="thumbs up outline large icon"></i>
                                                    <i18n>Fornito da</i18n>
                                                    <template_field class="template_field" name="provider_name">provider_name</template_field>
                                                </div>
                                                <div class="ui attached content segment">
                                                    <div class="item">
                                                        <template_field class="template_field" name="provider_description">provider_description</template_field>
                                                    </div>
                                                    <div class="item">
                                                        <i class="map marker big icon"></i>
                                                        <template_field class="template_field" name="provider_address">provider_address</template_field>
                                                        <template_field class="template_field" name="provider_address_map">provider_address_map</template_field>
                                                    </div>
                                                    <div class="item">
                                                        <i class="phone sign big icon"></i>
                                                        <template_field class="template_field" name="provider_phone">provider_phone</template_field>
                                                    </div>
                                                    <div class="item">
                                                        <i class="mail big icon"></i>
                                                        <template_field class="template_field" name="provider_email">provider_email</template_field>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /index and provider grid -->
                            </div>
                            <!-- /first column -->

                            <!-- this will generate the second column if needed -->
                            <template_field class="template_field" name="instancesColumn">instancesColumn</template_field>

                        </div>
                        <!-- /courseInfo -->
                    </div>
                    <!-- /first -->
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
