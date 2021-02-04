<html>

<head>
    <!-- link rel="stylesheet" href="../../../css/comunica/default/default.css" type="text/css" -->
    <link rel="stylesheet" href="../../../css/comunica/claire/default.css" type="text/css">
</head>

<body>
    <a name="top">
    </a>
    <div id="header">
        <template_field class="microtemplate_field" name="header_com">header_com</template_field>
    </div>
    <!-- menu -->
    <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>
    <!-- / menu -->
    <!-- PERCORSO -->
    <div id="journey" class="ui tertiary inverted teal segment">
        <i18n>dove sei: </i18n>
        <span>
        <i18n>agenda</i18n>
    </span>
        <span>
        > <template_field class="template_field" name="status">status</template_field>
    </span>
    </div>
    <!-- / percorso -->
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
        <!-- label -->
        <div id="label">
            <div class="topleft">
                <div class="topright">
                    <div class="bottomleft">
                        <div class="bottomright">
                            <div class="contentlabel">
                                <h1>
                                    <i18n>nuovo appuntamento</i18n>
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /label -->
        <!-- contenuto -->
        <div id="content">
            <div id="contentcontent">
                <div class="first">
                    <form name="form" method="post" accept-charset="UTF-8" action="send_event.php">
                        <div class="edit">
                            <div>
                                <i18n>Destinatari: </i18n>
                                <div id="js_destinatari_sel" name="js_destinatari_sel">
                                    <template_field class="template_field" name="destinatari">destinatari</template_field>
                                </div>
                            </div>
                            <i18n>Oggetto: </i18n>
                            <div>
                                <input type="text" name="titolo" id="oggetto" maxlength="40" size="60" value="<template_field class="template_field" name="titolo">titolo</template_field>">
                            </div>
                            <div>
                                <i18n>Testo: </i18n>
                                <div>
                                    <textarea name="testo" cols="60" rows="10" WRAP="physical"><template_field class="template_field" name="testo">testo</template_field></textarea>
                                </div>
                            </div>
                            <p>
                                <i18n>Ora (hh:mm:sec): </i18n>
                                <input type="text" name="ora_evento" value="<template_field class="template_field" name="event_time">event_time</template_field>">
                            </p>
                            <p>
                                <i18n>Giorno (gg/mm/aaaa): </i18n>
                                <input name="data_evento" type="text" size="10" maxlength="10" id="event_date" class="date_input" value="<template_field class="template_field" name="event_date">event_date</template_field>">
                                <a href="javascript:show_calendar('document.form.data_evento', document.form.data_evento.value);"><img src="img/cal.png" alt="Scegli una data">
                                </a>
                                <!--input type="text" name="data_evento" value="<template_field class="template_field" name="event_date">event_date</template_field>"-->
                            </p>
                            <div>
                                <input type="submit" name="spedisci" value="Segna">
                                <input type="reset" name="pulisci" value="Annulla">
                            </div>
                        </div>
                        <div class="menur">
                            <div>
                                <i18n>Priorit&agrave;: </i18n>
                                <select name="priorita">
                        <option value="2" selected><i18n>Normale</i18n></option>
                        <option value="1"><i18n>Alta</i18n></option>
                        <option value="3"><i18n>Bassa</i18n></option>
                      </select>
                            </div>
                            <p>
                                <template_field class="template_field" name="rubrica">rubrica</template_field>
                                <div>
                                    <template_field class="template_field" name="student_button">student_button</template_field>
                                    <template_field class="template_field" name="tutor_button">tutor_button</template_field>
                                    <template_field class="template_field" name="author_button">author_button</template_field>
                                    <template_field class="template_field" name="admin_button">admin_button</template_field>
                                </div>
                                <p>
                                    <template_field class="template_field" name="indirizzi">indirizzi</template_field>
                                </p>
                                <!--<div>
              		 <input type="submit" name="conferma" value="Conferma indirizzi">
              		</div>
             -->
                            </p>
                        </div>
                    </form>
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

    </div>
    <!-- PIEDE -->
    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->
</body>

</html>
