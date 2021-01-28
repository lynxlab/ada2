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

    <div id="confirmModal" class="ui basic modal">
        <div class="header">
            <i18n>Conferma evasione richiesta</i18n>
        </div>
        <div class="content">
            <div class="left">
                <i class="question icon"></i>
            </div>
            <div class="right">
                <p class="confirmText" data-requesttype="3">
                    <i18n>Questo impedirà accessi futuri all'utente che ha fatto la richiesta.</i18n>
                </p>
                <p class="confirmText" data-requesttype="4">
                    <i18n>Questo renderà <b>illegibili</b> e <b>irrecuperabili</b> i dati dell'utente.</i18n>
                </p>
                <p class="confirmQuestion">
                    <i18n>Confermi l'operazione?</i18n>
                </p>
            </div>
        </div>
        <div class="actions">
            <div class="two fluid ui buttons">
                <div class="ui negative labeled icon button">
                    <i class="remove icon"></i>
                    <i18n>No</i18n>
                </div>
                <div class="ui positive right labeled icon button">
                    <i18n>Sì</i18n>
                    <i class="checkmark icon"></i>
                </div>
            </div>
        </div>
    </div>
    <span id="notEditableMSG" style="display:none;"><i18n>Note non modificabili</i18n></span>
    <span id="clickToEditMSG" style="display:none;"><span class="editablePlaceholder"><i18n>Clic per modificare</i18n></span></span>
</body>

</html>
