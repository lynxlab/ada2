<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
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
                    <template_field class="template_field" name="title">title</template_field>
                </span>
            </div>
            <div id="user_wrap">
                <!--dati utente-->
                <div id="status_bar">
                    <div class="user_data_default status_bar">
                        <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
                        <span>
                            <template_field class="template_field" name="label">label</template_field>
                        </span>
                    </div>
                </div>
                <!-- / dati utente -->
            </div>

            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent">
                    <div class="first">
                        <template_field class="template_field" name="data">data</template_field>
                        <div class="ui icon success message" style="display:none;">
                            <i class="checkmark icon"></i>
                            <div class="content">
                                <div class="header">
                                    <i18n>Il tuo numero di pratica è</i18n>: <span id="requestUUID" class="requestUUID"></span>
                                </div>
                                <p>
                                    <i18n>Scrivi questo numero in un posto sicuro! Dovrà essere usato per ogni comunicazione relativa alla richiesta</i18n>
                                </p>
                                <p class="newRequestButtons">
                                    <button type="button" id="redirectBtn" class="ui orange button" style="display:none;"><span id="redirectLbl"><i18n>clicca qui per evadere la pratica</i18n></span></button>
                                    <a href="list.php" id="requestsListBtn" class="ui purple button">
                                        <i18n>Vai all'elenco richieste</i18n>
                                    </a>
                                </p>
                            </div>
                        </div>
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
