<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/tutor/default.css" type="text/css">
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
            <!-- / percorso -->
            <!--dati utente-->
             <div id="status_bar">
            <div id="user_data" class="user_data_default">
                <i18n>utente: </i18n>
                <span>
                    <template_field class="template_field" name="user_name">user_name</template_field>
                </span>
                <i18n>tipo: </i18n>
                <span>
                    <template_field class="template_field" name="user_type">user_type</template_field>
                </span>
                <div class="status">
                    <i18n>status: </i18n>
                    <span>
                        <template_field class="template_field" name="status">status</template_field>
                    </span>
                </div>
            </div>
            <!-- / dati utente -->
                <!-- label -->
                <!--div id="label">
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
                </div-->
                <!-- /label -->
             </div>

            <!-- contenuto -->
            <div id="content_view">
                <div id="contentcontent" class="contentcontent_view">
                    <div class="first">
                        <div id="help">
                        <template_field class="template_field" name="help">help</template_field>
                        </div>
                        <table id="videochatlog" class="hover row-border display ui padded table doDataTable">
                            <thead>
                                <tr>
                                    <th>Dettagli</th>
                                    <th><i18n>Descrizione</i18n></th>
                                    <th><i18n>Inizio</i18n></th>
                                    <th><i18n>Fine</i18n></th>
                                    <th><i18n>Partecipanti</i18n></th>
                                    <th><i18n>Tipo</i18n></th>
                                </tr>
                            </thead>
                        </table>
                        <div id="videochatlog-details" style="display:none;">
                            <table class="hover row-border display ui table">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th><i18n>Entrata</i18n></th>
                                        <th><i18n>Uscita</i18n></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="bottomcont"> </div>
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
        <span id="exportExcelBtnText" style="display:none;"><i class="download disk icon" aria-hidden="true"></i><i18n>Excel</i18n></span>
        <span id="exportPDFBtnText" style="display:none;"><i class="download disk icon" aria-hidden="true"></i><i18n>PDF</i18n></span>
        <span id="tutorRowText" style="display:none;">&nbsp;(tutor)</span>
    </body>
</html>
