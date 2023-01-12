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
                            <h3 class="ui top attached header">
                                <i class="copy large icon"></i>
                                <div class="content">
                                    <i18n>Clonazione dell'istanza</i18n>: <template_field class="template_field" name="instanceName">instanceName</template_field>
                                    <div class="sub header"><i18n>Corso</i18n>: <template_field class="template_field" name="courseName">courseName</template_field></div>
                                </div>
                            </h3>
                            <div class="ui segment attached">
                                <template_field class="template_field" name="summary">summary</template_field>
                            </div> <!-- /segment -->
                            <div class="ui segment bottom attached">
                                <div id="recapContainer" class="ui info message">
                                    <div class="header">
                                        <i18n>Riepilogo clonazione istanza</i18n>
                                    </div>
                                    <ol class="list"></ol>
                                    <div style="display:flex;">
                                        <a id="recapDownload" class="ui right floated blue tiny button">Download CSV</a>
                                    </div>
                                </div>
                                <template_field class="template_field" name="form">form</template_field>
                            </div> <!-- /bottom segment -->
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

    <!-- piede -->
    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->
    <span id="selectableHeaderTPL" style="display:none;">
    <h3 id="selectableHeader"><i18n>Seleziona i corsi in cui clonare l'istanza</i18n></h3>
    <div style="display:flex;">
        <div style="flex-grow:1; margin-right: 1em;">
            <input type='text' class='search-input' autocomplete='off' placeholder='<i18n>Cerca corso</i18n>...' />
        </div>
        <div>
            <div class="ui icon buttons">
                <a id="deselectAllBtn" class="ui red button" title="<i18n>Deseleziona tutti</i18n>"><i class="ban circle icon"></i></a>
                <a id="selectAllBtn" class="ui green button" title="<i18n>Seleziona tutti</i18n>"><i class="checkmark icon"></i></a>
            </div>
        </div>
    </div>
    </span>
    <span id="selectableFooterTPL" style="display:none;"><h4 id="selectableFooter"></h4></span>
    <span id="noitemselectedTPL" style="display:none;"><i18n>Nessun corso selezionato</i18n></span>
    <span id="oneitemselectedTPL" style="display:none;"><i18n>Un corso selezionato</i18n></span>
    <span id="moreitemselectedTPL" style="display:none;"><i18n>%d corsi selezionati</i18n></span>
    <span id="submitbuttonTPL" style="display:none;"><i18n>Clona</i18n></span>
    <span id="recapRowTPL" style="display:none;">
        <i18n>Istanza clonata con ID</i18n>: <strong>:clonedId</strong>
        <i18n>nel corso</i18n> <strong>:courseName</strong> (<i18n>ID Corso</i18n>: <strong>:courseId</strong>)
    </span>
    <span id="recapCSVheaderTPL" data-filename="<i18n>riepilogo-clonazione-istanza</i18n>" style="display:none;">
        <i18n>Titolo corso</i18n>,<i18n>ID Corso</i18n>,<i18n>ID istanza clonata</i18n>
    </span>

</body>

</html>
