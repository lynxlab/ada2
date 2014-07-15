<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>
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
                <i18n>utente: </i18n>
                <span>
                    <template_field class="template_field" name="user_name">user_name</template_field>
                </span>
                <i18n>tipo: </i18n>
                <span>
                    <template_field class="template_field" name="user_type">user_type</template_field>
                </span>
                <i18n>livello:</i18n>
                <span>
                    <template_field class="template_field" name="level">level</template_field>
                </span>
            </div> <!-- / dati utente -->
            <!-- label -->
            <div id="label">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
                                    <!--h1><i18n>cerca</i18n></h1-->
                                    <h1>
                                        <div id="labelSimple_search">
                                            <template_field class="template_field" name="labelSimple_search">labelSimple_search</template_field>
                                        </div>
                                    </h1>   
                                    <h1>
                                        <div id="labelAdvanced_search">
                                            <template_field class="template_field" name="labelAdvanced_search">labelAdvanced_search</template_field>
                                        </div>
                                    </h1>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /label -->
            </div>
            <!-- contenuto -->
            <div id="contentSearch">
                <div id="contentcontent" class="contentcontent_default">
                    <div id="div_advancedSearch_form">
                        <div class="search_formAdvanced">
                            <template_field class="template_field" name="advancedSearch_form">advancedSearch_form</template_field>
                        </div>
                        <div id="div_menuAdvanced">
                            <template_field class="template_field" name="menuAdvanced_search">menuAdvanced_search</template_field>
                                <span>
                                    <template_field class="template_field" name="simpleSearchLink">simpleSearchLink</template_field>
                                </span>
                                    <div id="result_AdvancedSearch">
                                        <i18n>Risultati:</i18n>
                                            <div id="results">
                                                <template_field class="template_field" name="result_AdvancedSearch">result_AdvancedSearch</template_field>
                                            </div>
                                    </div>
                      </div>
                  </div>
                <div id="div_form">
                    <div class="first">
                        <div class="search">
                            <div id="label_result">
                                    <i18n>Risultati:</i18n>
                            </div> 
                            <!--i18n>Ricerca semplice </i18n-->
                                <template_field class="template_field" name="form">form</template_field>
                        </div>
                        </div>
                 </div>
                        <div class="search_results">
                    <div id="div_menu">
                            <template_field class="template_field" name="menu">menu</template_field>
                    </div>
                    <div id="advanced_searchLink">
                                <span>
                        <template_field class="template_field" name="advanced_searchLink">advanced_searchLink</template_field>
                    </span>
                </div>
                <div class="table_result">
                    <div id="div_Result">
                        <span>
                                    <template_field class="template_field" name="results">results</template_field>
                                </span>
                            </div>
                        </div>
                    </div>
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
                    <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
                </div>
                <div id="bottomcom_t">
                </div>
            </div> <!-- /com_tools -->
            <!-- menudestra -->
            <div id="menuright" class="sottomenu_off menuright_default">
                <div id="topmenur">
                </div>
                <div id="menurightcontent">
                    <ul>
                        <li class="close">
                            <a href="#" onClick="toggleElementVisibility('menuright', 'right');">
                                </i18n>chiudi</i18n>
                            </a>
                        </li>
                        <li class="_menu">
                            <a href="main_index.php">
                                <i18n>indice</i18n>
                            </a>
                        </li>
                        <li class="_menu">
                        <template_field class="template_field" name="go_map">go_map</template_field>
                        </li>
                    </ul>
                </div>
                <div id="bottommenur">
                </div>
            </div> <!-- / menudestra  -->
        </div> <!-- / contenitore -->

        <!-- MENU A TENDINA -->
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
		<li id="tools" class="unselectedtools" onClick="toggleElementVisibility('submenu_tools','up')">
				<a>
					 <i18n>strumenti</i18n>
				</a>
		</li>
		<li id="actions" class="unselectedactions" onClick="toggleElementVisibility('submenu_actions','up')">
				<a>
					 <i18n>agisci</i18n>
				</a>
		</li-->
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
            </ul> <!-- / menu -->

            <!-- notifiche eventi -->
            <template_field class="template_field" name="events">events</template_field>
            <!-- / notifiche eventi -->
            <!-- tendina -->
            <div id="dropdownmenu">
           
                <!-- puntoint -->
                <div id="submenu_question_mark" class="sottomenu  sottomenu_off">
                    <div id="_question_markcontent">
                        <ul>
                            <li>
                            <!--template_field class="template_field" name="help">help</template_field-->
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
                </div> <!-- / puntoint -->
            </div> <!-- /tendina-->
        </div> <!-- / MENU A TENDINA -->
        <!-- PIEDE -->
        <div class="clearfix"></div>
            
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div> <!-- / piede -->
    </body></html>
