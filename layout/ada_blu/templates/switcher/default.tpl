<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/switcher/default.css" type="text/css">
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
            <div id="user_wrap">
                <div id="status_bar">
                    <!--dati utente-->
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
                            </span> </div>
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

            <!-- com_tools -->
            <div id="com_tools">
                <div id="topcom_t">
                </div>
                <div id="com_toolscontent">
                    <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
                </div>
                <div id="bottomcom_t">
                </div>
            </div>
            <!-- /com_tools -->
        </div>
        <!-- / contenitore -->

        <!-- menu a tendina -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="switcher.php">
                        <i18n>home</i18n>
                    </a>
                </li>
                <li id="com" class="unselectedcom" onClick="toggleElementVisibility('submenu_com','up')">
                    <a>
                        <i18n>comunica</i18n>
                    </a>
                </li>
                <li id="actions" class="unselectedactions" onClick="toggleElementVisibility('submenu_actions','up')">
                    <a>
                        <i18n>agisci</i18n>
                    </a>
                </li>
                <li id="question_mark" class="unselectedquestion_mark">
                    <a href="../help.php" target="_blank">
                        <i18n>aiuto</i18n>
                    </a>
                </li>
                <li id="esc">
                    <a href="../index.php">
                        <i18n>esci</i18n>
                    </a>
                </li>
            </ul>
            <!-- / menu -->
            <!-- tendina -->
            <div id="dropdownmenu">
                <!-- comunica -->
                <div id="submenu_com" class="sottomenu sottomenu_off">
                    <div id="_comcontent">
                        <ul>
                            <li>
                                <a href="#" onclick='openMessenger("../comunica/list_messages.php",800,600);'>
                                    <i18n>messaggeria</i18n>
                                </a>
                            </li>
                            <li>
                            <template_field class="template_field" name="ajax_chat_link">ajax_chat_link</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="mychat">mychat</template_field>
                            </li>
                            <li>
                            	<a href="../modules/newsletter">
                                	    <i18n>newsletter</i18n>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- / comunica -->

                <!-- azioni -->
                <div id="submenu_actions" class="sottomenu sottomenu_off">
                    <div id="_actionscontent">
                        <ul>
                            <li>
                               <template_field class="template_field" name="edit_switcher">edit_switcher</template_field>
                            </li>
                            <li><a href="list_users.php?list=authors"><i18n>Lista autori</i18n></a></li>
                            <li><a href="list_users.php?list=tutors"><i18n>Lista tutor</i18n></a></li>
                            <li><a href="list_users.php?list=students"><i18n>Lista studenti</i18n></a></li>
                            <li><a href="add_user.php"><i18n>Aggiungi utente</i18n></a></li>
                            <li><a href="list_courses.php"><i18n>Lista corsi</i18n></a></li>
                            <li><a href="add_course.php"><i18n>Aggiungi corso</i18n></a></li>
                            <li><a href="translation.php"><i18n>Traduci messaggi</i18n></a></li>
                            <li><a href="../modules/apps/"><i18n>Applicazioni</i18n></a></li>
                            <li><a href="../modules/impexport/import.php"><i18n>Importa corso</i18n></a></li>
                            <li><a href="../modules/impexport/export.php"><i18n>Esporta corso</i18n></a></li>
                            <li><a href="../modules/service-complete/index.php"><i18n>Condizioni di completamento</i18n></a></li>
                            <!--<li><a href="../admin/edit_content.php"><i18n>Edit home page contents</i18n></a></li>-->
                            <template_field class="template_field" name="edit_home_page">edit_home_page</template_field>
                            
                            <!--li>
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
                            </li-->
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- / azioni -->
            </div>
            <!--/tendina -->

        </div>
        <!-- / menu a tendina -->

        <!-- piede -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->

    </body>
</html>
