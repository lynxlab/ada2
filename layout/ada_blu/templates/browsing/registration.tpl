<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/browsing/default.css" type="text/css">
    </head>
    <body>
        <a name="top"></a>
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div> <!-- / testata -->
        <!-- contenitore -->
        <div id="container">
            <!-- PERCORSO -->
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
            </div>
            <!-- / percorso -->
            <div id="status_bar">
                <!--dati utente-->
                <div id="user_data" class="user_data_default">

                    <span>
                        <template_field class="template_field" name="message">message</template_field>
                    </span>
                    <i18n>utente: </i18n>
                    <span>
                        <template_field class="template_field" name="user_name">user_name</template_field>
                    </span>
                    <div class="status">
                    <i18n>status: </i18n>
                    <span>
                        <template_field class="template_field" name="status">status</template_field>
                    </span>
                </div>
                </div> <!-- / dati utente -->
                <!-- label -->
                <div id="label">
                    <div class="topleft">
                        <div class="topright">
                            <div class="bottomleft">
                                <div class="bottomright">
                                    <div class="contentlabel">
                                        <i18n>Registrazione</i18n>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /label -->
            </div>
            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent" class="contentcontent_default">
                    <div id="help">
                        <template_field class="template_field" name="help">help</template_field>
                    </div>
                    <div id="data">
                        <template_field class="template_field" name="data">data</template_field>
                    </div>                    
                </div>
                <div id="bottomcont">
                </div>
            </div> <!--  / contenuto -->
            <!-- com_tools -->
            <div id="com_tools">
                <div id="topcom_t">
                </div>
                <div id="com_toolscontent">                   
                </div>
                <div id="bottomcom_t">
                </div>
            </div> <!-- /com_tools -->
        </div> <!-- / contenitore -->

        <!-- MENU A TENDINA -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="../index.php">
                        <i18n>home</i18n>
                    </a>
                </li>
                <li id="ancora_menuright">
                    <a href="../info.php">
                        <i18n>corsi</i18n>
                    </a>
                </li>

                <li id="question_mark" class="unselectedquestion_mark" onclick="toggleElementVisibility('submenu_question_mark','up'); return false;">
                    <a>
                        <i18n>Help</i18n>
                    </a>
                </li>
            </ul> <!-- / menu -->

            <!-- tendina -->
            <div id="dropdownmenu">
                <!-- puntoint -->
                <div id="submenu_question_mark" class="sottomenu  sottomenu_off">
                    <div id="_question_markcontent">
                        <ul>
                            <li>
                                <a href="../help.php">
                                    <i18n>informazioni</i18n>
                                </a>
                            </li>
                            <li>
                                <a href="../credits.php">
                                    <i18n>credits</i18n>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div> <!-- / puntoint -->
            </div> <!-- /tendina-->
        </div> <!-- / MENU A TENDINA -->
        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer_guest">footer_guest</template_field>
        </div> <!-- / piede -->
    </body>
</html>
