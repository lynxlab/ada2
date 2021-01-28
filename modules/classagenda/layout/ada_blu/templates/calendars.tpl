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
                        <div id="help">
                            <template_field class="template_field" name="help">help</template_field>
                        </div>
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

    <!-- piede -->
    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->

</body>

</html>
