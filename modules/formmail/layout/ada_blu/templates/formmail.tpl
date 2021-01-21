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
                        <div class="ui form basic segment">
                            <h2 class="ui top attached header">
                                <i class="mail icon"></i>
                                <div class="content">
                                    <i18n>Invia email di richiesta supporto</i18n>
                                </div>
                            </h2>
                            <div class="ui attached segment">
                                <div class="two fields">
                                    <div class="field">
                                        <div class="ui fluid helptype selection dropdown">
                                            <div class="placeholder text">
                                                <i18n>Seleziona il tipo di richiesta</i18n>...</div>
                                            <i class="dropdown icon"></i>
                                            <input type="hidden" name="helptype">
                                            <div class="menu ui transition hidden">
                                                <template_field class="template_field" name="helptypes">helptypes</template_field>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <input placeholder="<i18n>Oggetto</i18n>..." type="text" maxlength="255" id="subject" name="subject">
                                    </div>
                                </div>

                                <div class="field">
                                    <label><i18n>Testo del messaggio</i18n>:</label>
                                    <textarea id="msgbody" name="msgbody"></textarea>
                                </div>

                                <div class="inline field">
                                    <div class="ui checkbox">
                                        <input type="checkbox" name="sendcopy" id="sendcopy">
                                        <label for="sendcopy"><i18n>Invia una copia alla mia email</i18n></label>
                                    </div>
                                </div>

                                <div id="formmailDZ">
                                    <span class="dz-message"><i18n>Trascina qui un file o clicca per aggiungere un allegato</i18n></span>
                                </div>
                            </div>

                            <div class="ui bottom attached right aligned header">
                                <div class="ui blue submit right icon labeled button">
                                    <i class="mail outline icon"></i>
                                    <i18n>Invia</i18n>
                                </div>
                            </div>

                        </div>

                    </div>
                    <!-- /first -->
                </div>
                <!-- /contentcontent -->
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

    <!-- used by js to have messages translations -->
    <div id="messagesContainer">
        <span id="helptypePrompt"><i18n>Selezionare un tipo di richiesta</i18n></span>
        <span id="subjectPrompt"><i18n>Inserire l'oggetto</i18n></span>
        <span id="msgbodyPrompt"><i18n>Scrivere un messaggio</i18n></span>
        <!--  sent ok modal -->
        <div class="ui modal" id="modalSentOK">
            <div class="header">
                <i18n>Richiesta di assistenza inviata</div>
            <div class="content">
                <div class="left"><i class="mail outline icon"></i></div>
                <div class="right">
                    <p class="content">La tua richiesta è stata inviata, sarai contattato dal personale addetto nel più breve tempo possibile</p>
                </div>
            </div>
            <div class="actions">
                <a class="ui positive right labeled icon button" href="<template_field class=" template_field " name="user_homepage ">user_homepage</template_field>">
          				ok
          				<i class="checkmark icon"></i>
        			</a>
            </div>
        </div>
        <!--  /sent ok modal -->
    </div>
</body>

</html>
