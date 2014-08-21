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
		<!-- menu -->
		    <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>  
		<!-- / menu -->
		
<!-- modal shown on empty search -->
<div class="ui small modal">  
  <div class="content">
    <div class="right">
      <i18n>Scrivi qualcosa da cercare</i18n>!
    </div>
  </div>
  <div class="actions">
    <div class="ui button">OK</div>
  </div>
</div>
<!-- / modal shown on empty search -->
        
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
                        <template_field class="template_field" name="text">text</template_field>
                    </div>

                    <div id="go_next">
						<template_field class="template_field" name="go_next">go_next</template_field>
					</div>

		    <hr>
		    <div id="index_in_text">
		      <h3><i18n>note di classe</i18n></h3>
                            <template_field class="template_field" name="notes">notes</template_field>
			    <!--h3><i18n>Approfondimenti:</i18n></h3-->
			    <!--template_field class="template_field" name="index">index</template_field-->
		  </div>
		  <div id="exercises_in_text">
		      <h3><i18n>note personali</i18n></h3>
		       <template_field class="template_field" name="personal">personal</template_field>
			<!--template_field class="template_field" name="exercises">exercises</template_field-->
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
            <!-- menudestra -->
            <!-- <div id="menuright" class="sottomenu_off menuright_view "> -->
            <div id="menuright" class="menuright_view ui wide right sidebar">
                <div id="topmenur">
                </div>
                <div id="menurightcontent">
                    <ul>
                        <!-- <li class="close">
                            <a href="#" onClick="toggleElementVisibility('menuright', 'right');">
                                <i18n>chiudi</i18n>
                            </a>
                        </li> -->
                        <!--li class="_menu">
                                <template_field class="template_field_disabled" name="main_index">main_index</template_field>
                        </li-->
                        <li class="_menu">
                                <template_field class="template_field" name="main_index_text">main_index_text</template_field>
                        </li>
                        <li class="_menu">
							<template_field class="template_field" name="search_form">search_form</template_field>
                        </li>
                        <!--li class="_menu">
							<template_field class="template_field" name="go_map">go_map</template_field>
                        </li-->
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
                        <li class="_name">
                        <i18n>risorse</i18n>
                        </li>
                        <ul>
                            <li>
                            <template_field class="template_field" name="media">media</template_field>
                            </li>
                        </ul>
                        <!-- li class="_name">
                        <i18n>media di classe</i18n>
                        </li -->
                        <!-- ul>
                            <li>
                            <template_field class="template_field_disabled" name="user_media">user_media</template_field>
                            </li>
                        </ul -->
                        <!-- "li class="_name">
                        <i18n>note di classe</i18n>
                        </li -->
                        <!-- ul>
                            <li>
                            <template_field class="template_field_disabled" name="notes">notes</template_field>
                            </li>
                        </ul -->
                        <!-- li class="_name">
                        <i18n>note personali</i18n>
                        </li -->
                        <!-- ul>
                            <li>
                            <template_field class="template_field_disabled" name="personal">personal</template_field>
                            </li>
                        </ul -->
                    </ul>
                </div>
                <div id="bottommenur">
                </div>
            </div>
            <!-- / menudestra  -->
        </div>
        <!-- / contenitore -->

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
