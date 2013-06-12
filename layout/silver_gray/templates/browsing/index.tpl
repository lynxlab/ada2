<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/browsing/default.css" type="text/css">
    </head>
    <body>
        <a name="top">
        </a>
        <!--testata-->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div>
        <!--/testata-->
        <!--contenutore-->
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
            <!--/dati utente-->
            <!-- label -->
            <!--
            <div id="label">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
                                    <h1><i18n>Indice dei contenuti del corso</i18n></h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            -->
            <!-- /label -->
            </div>
            <!--contenuto-->
            <div id="content">
                <div id="contentcontent" class="contentcontent_default">
                    <div class="first">
                    </div>
                    <template_field class="template_field" name="title">title</template_field>
                    <template_field class="template_field" name="index">index</template_field>
                </div>
                <div id="bottomcont">
                </div>
            </div>
            <!-- contenuto -->
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
            <!--menudestra-->
            
            <div id="menuright" class="sottomenu_off menuright_default">
            <div id="topmenur">
            </div>
            <div id="menurightcontent">
              		 <ul>
              		 		 <li class="close">
              		 		 		 <a href="#" onclick="toggleElementVisibility('menuright', 'right');">
              					   		</i18n>chiudi</i18n>
              		 				 </a>
              				 </li>
                     <li class="_menu">
                        <template_field class="template_field" name="search_form">search_form</template_field>
                        </li>
                        <li class="_menu">
                        <template_field class="template_field" name="go_map">go_map</template_field>
                        </li>
              		 </ul>
            </div>
            <div id="bottommenur">
            </div>
            </div>

            <!--menudestra-->
        </div>
        <!--contenitore-->

        <!--MENU A TENDINA-->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="user.php">
                        <i18n>home</i18n>
                    </a>
                </li>
                <!--li id="com" class="unselectedcom" onclick="toggleElementVisibility('submenu_com','up')">
				<a>
					 <i18n>comunica</i18n>
				</a>
		</li>
		<li id="tools" class="unselectedtools" onclick="toggleElementVisibility('submenu_tools','up')">
				<a>
					 <i18n>strumenti</i18n>
				</a>
		</li>
		<li id="actions" class="unselectedactions" onclick="toggleElementVisibility('submenu_actions','up')">
				<a>
					 <i18n>agisci</i18n>
				</a>
		</li-->
                
		<li id="ancora_menuright" onclick="toggleElementVisibility('menuright', 'right');">
				<a>
					 <i18n>Naviga</i18n>
		 		</a>
		</li>

                <li id="question_mark" class="unselectedquestion_mark" onclick="toggleElementVisibility('submenu_question_mark','up'); return false;">
                    <a>
                        <i18n>help</i18n>
                    </a>
                </li>
                <li id="esc">
                    <a href="../index.php">
                        <i18n>esci</i18n>
                    </a>
                </li>
            </ul> <!-- / menu -->
            <!-- notifiche eventi -->
            <template_field class="template_field" name="events">events</template_field>
            <!-- / notifiche eventi -->
            <!-- tendina -->
            <div id="dropdownmenu">
                <!-- comunica -->
                <!--
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
                  				<template_field class="disabled" name="ajax_chat_link">ajax_chat_link</template_field>
                  		</li>
                  		<li>
                  		 		<a href="../user/index.php?module=download.php">
                          	 <i18n>collabora</i18n>
                  				</a>
                  		</li>
                                    </ul>
                </div>
                <div class="bottomsubmenu">
                </div>
                </div>
                -->
                <!--/comunica-->
                <!-- strumenti -->
                <!--
                <div id="submenu_tools" class="sottomenu sottomenu_off">
                <div id="_toolscontent">
                                    <ul>
                  	<li>
                  				<a href="#" onclick='openMessenger("../comunica/list_events.php",800,600);'>
                  					 <i18n>agenda</i18n>
                  				</a>
                  		</li>
                  		<li>
                  				<a href="../user/index.php?module=mylog.php">
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
                  				<template_field class="disabled" name="go_print">go_print</template_field>
                  		</li>
                  		<li>
                  				<template_field class="disabled" name="bookmarks">bookmarks</template_field>
                  		</li>
                                    </ul>
                </div>
                <div class="bottomsubmenu">
                </div>
                </div>
                -->
                <!--/strumenti-->
                <!-- azioni -->
                <!--
                <div id="submenu_actions" class="sottomenu sottomenu_off">
                <div id="_actionscontent">
                                    <ul>
										 <li>

                		 </li>
                                    </ul>
                </div>
                <div class="bottomsubmenu">
                </div>
                </div>
                -->
                <!--azioni-->
                <!-- puntoint -->
                <div id="submenu_question_mark" class="sottomenu  sottomenu_off">
                    <div id="_question_markcontent">
                        <ul>
                            <li>
                            <template_field class="template_field" name="help">help</template_field>
                            </li>
                            <li>
                                <a href="../info.php">
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
                </div><!--/puntoint-->
            </div> <!--/tendina -->
        </div> <!-- / MENU A TENDINA -->
        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div> <!-- / piede -->
    </body>
</html>