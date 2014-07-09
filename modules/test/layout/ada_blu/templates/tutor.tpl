<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/tutor/default.css" type="text/css">
    </head>
    <body>
        <a name="top"></a>
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div>
        <!-- / testata -->
        <!-- contenitore -->
        <div id="container">
            <!-- PERCORSO -->
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
                <span>
                    <template_field class="template_field" name="path">path</template_field>
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
                 <i18n>livello</i18n>: 
                 <span>
                    <template_field class="template_field" name="user_level">user_level</template_field>
                 </span>
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
                        <template_field class="template_field" name="text">text</template_field>
                    </div>
                </div>
                <div id="bottomcont"> </div>
            </div>
            <!--  / contenuto -->
            <!-- com_tools -->
            <div id="com_tools">
                <div id="topcom_t"> </div>
                <div id="com_toolscontent">
                    <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
                </div>
                <div id="bottomcom_t"> </div>
            </div>
            <!-- /com_tools -->
            <!-- menudestra -->
            <div id="menuright" class="sottomenu_off menuright_default">
                <div id="topmenur"> </div>
                <div id="menurightcontent">
                    <ul>
                        <li class="close"> <a href="#" onClick="toggleElementVisibility('menuright', 'right');">
                                </i18n>
                                chiudi
                                </i18n>
                            </a> </li>
                    </ul>
                </div>
                <div id="bottommenur"> </div>
            </div>
            <!-- / menudestra  -->
        </div>
        <!-- / contenitore -->
        <!-- MENU A TENDINA -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home"> <a href="../../tutor/tutor.php">
                        <i18n>home</i18n>
                    </a> </li>
                <li id="com" class="unselectedcom" onClick="toggleElementVisibility('submenu_com','up')"> <a>
                        <i18n>comunica</i18n>
                    </a> </li>
                <li id="tools" class="unselectedtools" onClick="toggleElementVisibility('submenu_tools','up')"> <a>
                        <i18n>strumenti</i18n>
                    </a> </li>
                <li id="actions" class="unselectedactions" onClick="toggleElementVisibility('submenu_actions','up')">
                    <a>
                        <i18n>agisci</i18n>
                    </a>
                </li>
                <li id="ancora_menuright" onClick="toggleElementVisibility('menuright', 'right');">
                    <a>
                        <i18n>Naviga</i18n>
                    </a>
                </li>
                <li id="question_mark" class="unselectedquestion_mark" onClick="toggleElementVisibility('submenu_question_mark','up'); return false;">
                    <a>
                        <i18n>Help</i18n>
                    </a>
                </li>
                <li id="esc"> <a href="../../index.php">
                        <i18n>esci</i18n>
                    </a> </li>
            </ul>
            <!-- / menu -->
            <!-- tendina -->
            <div id="dropdownmenu">
                <!-- comunica -->
                <div id="submenu_com" class="sottomenu sottomenu_off">
                    <div id="_comcontent">
                        <ul>
                            <li><a href="#" onclick='openMessenger("../../comunica/list_messages.php",800,600);'>
                                    <i18n>messaggeria</i18n>
                                </a>
                            </li>
                            <li><a href="../../comunica/list_chatrooms.php">
                                    <i18n>chatrooms</i18n>
                                </a>
                            </li>

                        </ul>
                    </div>
                    <div class="bottomsubmenu"> </div>
                </div>
                <!-- / comunica -->
                <!-- strumenti -->
                <div id="submenu_tools" class="sottomenu sottomenu_off">
                    <div id="_toolscontent">
                        <ul>
                            <li> <a href="#" onclick='openMessenger("../../comunica/list_events.php",800,600);'>
                                    <i18n>agenda</i18n>
                                </a> </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu"> </div>
                </div>
                <!-- / strumenti -->
                <!-- azioni -->
                <div id="submenu_actions" class="sottomenu sottomenu_off">
                    <div id="_actionscontent">
                        <ul>
                            <li>
                                <!--a href="../../tutor/edit_user.php" alt="edit profile">
                                    <i18n>cambia profilo</i18n>
                                </a-->
                                <template_field class="template_field" name="edit_user">edit_user</template_field>
                            </li>
                            <li>
                              <template_field class="template_field" name="class_student">class_student</template_field>
                            </li>
                            <li>
                              <template_field class="template_field" name="menu_01">menu_01</template_field>
                            </li>
                            <li>
                              <template_field class="template_field" name="menu_02">menu_02</template_field>
                            </li>
                            <li>
                              <template_field class="template_field" name="menu_03">menu_03</template_field>
                            </li>
                            <li>
                              <template_field class="template_field" name="menu_04">menu_04</template_field>
                            </li>
                            <li>
                              <template_field class="template_field" name="menu_05">menu_05</template_field>
                            </li>
                            <li>
                              <template_field class="template_field" name="menu_06">menu_06</template_field>
                            </li>
                            <li>
                              <template_field class="template_field" name="menu_07">menu_07</template_field>
                            </li>
                            <li>
                              <template_field class="template_field" name="menu_08">menu_08</template_field>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu"> </div>
                </div>
                <!-- / azioni -->
               <!-- puntoint -->
                <div id="submenu_question_mark" class="sottomenu  sottomenu_off">
                    <div id="_question_markcontent">
                        <ul>
                            <li>
                                <a href="../../help.php" target="_blank">
                                    <i18n>informazioni</i18n>
                                </a>
                            </li>
                            <li>
                                <a href="../../credits.php">
                                    <i18n>credits</i18n>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
            </div>
            <!--/tendina -->

            <!-- notifiche eventi -->
            <template_field class="template_field" name="events">events</template_field>
            <!-- / notifiche eventi -->
        </div>
        <!-- / MENU A TENDINA -->
        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>
