<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/browsing/default.css" type="text/css">
    </head>
    <body>
        <a name="top">
        </a>
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div>
        <!-- / testata -->
        <!-- contenitore -->
        <div id="container">
            <!-- percorso -->
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
                <span> > </span>
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
		<i18n>livello: </i18n>
                <span>
                    <template_field class="template_field" name="user_level">user_level</template_field>
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
            <div id="labelview">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
                                    <ul>
                                        <li>
                                        <template_field class="template_field" name="title">title</template_field>
                                        <span>, </span>
                                        <i18n>versione: </i18n>
                                        <span>
                                            <template_field class="template_field" name="version">version</template_field>
                                        </span>
                                        <i18n>del</i18n>
                                        <span>
                                            <template_field class="template_field" name="date">date</template_field>
                                        </span>
                                        </li>
                                        <!--li>
                          		 			<i18n>autore:</i18n>
                          		 			<span>
                          		 		 		<template_field class="template_field_disabled" name="author">author</template_field>
                          		 		 	</span>
                					    </li-->
                                        <li>
                                        <i18n>livello nodo:</i18n>
                                        <span>
                                            <template_field class="template_field" name="node_level">node_level</template_field>
                                        </span>
                                        </li>
                                        <li>
                                        <i18n>keywords: </i18n>
                                        <span class="keywords">
                                            <template_field class="template_field" name="keywords">keywords</template_field>
                                        </span>
                                        </li>
                                    </ul>
                                    <!--div class="dattilo" id="dattilo">
                                      <template_field class="template_field_disabled" name="dattilo">dattilo</template_field>
                                    </div-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /label -->
            </div>
            <!-- contenuto -->
            <div id="content_view">
                <div id="contentcontent" class="contentcontent_view">
                    <div id="info_nodo">
                        <span>
                            <template_field class="template_field" name="bookmark">bookmark</template_field>
                        </span>
                    </div>
                    <div class="firstnode">
                         <template_field class="template_field" name="data">data</template_field>
                    </div>

                    <div id="go_next"><template_field class="template_field" name="go_next">go_next</template_field></div>
                    <div id="index_in_text"><template_field class="template_field" name="index">index</template_field></div>
		    <div id="exercises_in_text"><template_field class="template_field" name="exercises">exercises</template_field></div>
  
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
            <!-- menudestra -->
            <div id="menuright" class="sottomenu_off menuright_view">
                <div id="topmenur">
                </div>
                <div id="menurightcontent">
                    <ul>
                        <li class="close">
                            <a href="#" onClick="toggleElementVisibility('menuright', 'right');">
                                <i18n>chiudi</i18n>
                            </a>
                        </li>
                        <!--li class="_menu">
                                <template_field class="template_field_disabled" name="main_index">main_index</template_field>
                        </li-->
                        <li class="_menu">
                                <template_field class="template_field" name="main_index_text">main_index_text</template_field>
                        </li>
                        <li class="_menu">
                        <template_field class="template_field" name="search_form">search_form</template_field>
                        </li>
                        <li class="_menu">
                        <template_field class="template_field" name="go_map">go_map</template_field>
                        </li>
                    </ul>
                    <ul id="attachment">
                        <li class="_name">
                        <i18n>approfondimenti</i18n>
                        </li>
                        <ul>
                            <li>
                            <template_field class="template_field" name="index">index</template_field>
                            </li>
                        </ul>
                        <li class="_name">
                        <i18n>collegamenti</i18n>
                        </li>
                        <ul>
                            <li>
                            <template_field class="template_field" name="link">link</template_field>
                            </li>
                        </ul>
                        <li class="_name">
                        <i18n>esercizi</i18n>
                        </li>
                        <ul>
                            <li>
                            <template_field class="template_field" name="exercises">exercises</template_field>
                            </li>
                        </ul>
                        <!--li class="_name">
                        <i18n>risorse</i18n>
                        </li-->
                        <!--ul>
                            <li>
                            <template_field class="template_field_disabled" name="media">media</template_field>
                            </li>
                        </ul-->
                        <!--li class="_name">
                        <i18n>media di classe</i18n>
                        </li-->
                        <!--ul>
                            <li>
                            <template_field class="template_field_disabled" name="user_media">user_media</template_field>
                            </li>
                        </ul-->
                        <!--"li class="_name">
                        <i18n>note di classe</i18n>
                        </li-->
                        <!--ul>
                            <li>
                            <template_field class="template_field_disabled" name="notes">notes</template_field>
                            </li>
                        </ul-->
                        <!--li class="_name">
                        <i18n>note personali</i18n>
                        </li-->
                        <!--ul>
                            <li>
                            <template_field class="template_field_disabled" name="personal">personal</template_field>
                            </li>
                        </ul-->
                    </ul>
                </div>
                <div id="bottommenur">
                </div>
            </div>
            <!-- / menudestra  -->
        </div>
        <!-- / contenitore -->

        <!-- menu -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="user.php">
                        <i18n>home</i18n>
                    </a>
                </li>
                <li id="com" class="unselectedcom" onClick="toggleElementVisibility('submenu_com','up')">
                    <a>
                        <i18n>comunica</i18n>
                    </a>
                </li>
                <li id="tools" class="unselectedtools" onClick="toggleElementVisibility('submenu_tools','up')">
                    <a>
                        <i18n>strumenti</i18n>
                    </a>
                </li>
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
                <li id="esc">
                    <a href="../index.php">
                        <i18n>esci</i18n>
                    </a>
                </li>
            </ul>
            <!-- / menu -->

            <!-- notifiche eventi -->
            <template_field class="template_field" name="events">events</template_field>
            <!-- / notifiche eventi -->
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
                                <a href="main_index.php?op=forum">
                                    <i18n>forum</i18n>
                                </a>
                            </li>
                            <li>
                            <template_field class="template_field" name="chat">chat</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="video_chat">video_chat</template_field>
                            </li>
                            <li>
                                <a href="download.php">
                                    <i18n>collabora</i18n>
                                </a>
                            </li>
                            <!--li>
                            <template_field class="template_field_disabled" name="ajax_chat_link">ajax_chat_link</template_field>
                            </li-->
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- / comunica -->
                <!-- strumenti -->
                <div id="submenu_tools" class="sottomenu sottomenu_off">
                    <div id="_toolscontent">
                        <ul>
                            <li>
                                <a href="#" onclick='openMessenger("../comunica/list_events.php",800,600);'>
                                    <i18n>agenda</i18n>
                                </a>
                            </li>
                            <li>
                                <a href="mylog.php">
                                    <i18n>diario</i18n>
                                </a>
                            </li>
                            <li>
                                <a href="history.php">
                                    <i18n>cronologia</i18n>
                                </a>
                            </li>
                            <li>
                                <a href="lemming.php">
                                    <i18n>lessico</i18n>
                                </a>
                            </li>
                            <li>
                                <a href="search.php">
                                    <i18n>cerca</i18n>
                                </a>
                            </li>
                            <li>
                            <template_field class="template_field" name="go_print">go_print</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="bookmarks">bookmarks</template_field>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- / strumenti -->
                <!-- azioni -->
                <div id="submenu_actions" class="sottomenu sottomenu_off">
                    <div id="_actionscontent">
                        <ul>
                            <li>
                                <a href="edit_user.php">
                                    <i18n>Modifica il tuo profilo</i18n>
                                </a>
                            </li>
                            <li>
                            <template_field class="template_field" name="send_media">send_media</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="add_bookmark">add_bookmark</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="add_node">add_node</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="add_word">add_word</template_field>
                            </li>                            <li>
                            <template_field class="template_field" name="edit_node">edit_node</template_field>
                            </li>
                            <li>
                            <li>
                                <a href="#" onclick="<template_field class="template_field" name="delete_node">delete_node</template_field>">
                                    <i18n>Elimina nodo</i18n>
                                </a>
                            </li>
                            </li>
                            <li>
                            <template_field class="template_field" name="add_exercise">add_exercise</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="add_note">add_note</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="add_private_note">add_private_note</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="edit_note">edit_note</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="delete_note">delete_note</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="publish_note">publish_note</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="go_XML">go_XML</template_field>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- / azioni -->
                <!-- puntoint -->
                <div id="submenu_question_mark" class="sottomenu  sottomenu_off">
                    <div id="_question_markcontent">
                        <ul>
                            <li>
                            <template_field class="template_field" name="help">help</template_field>
                            </li>
                            <li>
                                <a href="../help.php" target="_blank">
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
                </div>
                <!-- / puntoint -->
            </div>
            <!--/tendina -->

        </div> 
        <!-- / menu a tendina -->
        <!-- pannello video -->
        <div id="rightpanel" class="sottomenu_off rightpanel_view">
            <div id="toprightpanel">
            </div>
            <div id="rightpanelcontent">
                <ul>
                    <li class="close">
                        <a href="#" onClick="hideElement('rightpanel', 'right');">
                            <i18n>chiudi</i18n>
                        </a>
                    </li>
                    <li id="flvplayer">
                    </li>
                </ul>
            </div>
            <div id="bottomrightpanel">
            </div>
        </div>
        <!-- / pannello video -->
        <!-- piede -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>
