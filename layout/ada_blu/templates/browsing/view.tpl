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
        <!-- help -->
            <template_field class="template_field" name="help">help</template_field>  
        <!-- / help -->     
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
					<!-- PULSANTI AVANTI E INDIETRO -->
						<template_field class="template_field" name="navigation_bar">navigation_bar</template_field>
					<!-- end - PULSANTI AVANTI E INDIETRO -->

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
              <h3 class="ui teal block dividing center aligned  header"><i class="globe icon"></i><i18n>Naviga</i18n></h3>
                <div id="menurightcontent">
                  <div class="ui right labeled icon mini fluid top attached button"  onclick="javascript: hideSideBarFromSideBar();">
                    <i class="close icon"></i><i18n>Chiudi</i18n>
                  </div>
                  <!-- accordion -->
                  <div class="ui attached segment accordion">
                  
			       <div class="title" onClick="showIndex();">
			         <i class="icon dropdown"></i>
			         <i18n>indice</i18n><i class="sitemap icon" style="float:right;"></i>
			       </div>
			       <div class="index content field">
			         <div id="show_index">
			             <div class="loader-wrapper">
			                 <div class="ui active inline mini text loader">
			                     <i18n>Caricamento</i18n>...
			                  </div>
			             </div>
                     </div>
			       </div>
 
                   <div class="active title">
                     <i class="icon dropdown"></i>
                     <i18n>azioni</i18n> <i class="edit sign icon"></i> 
                   </div>
                   <div class="active actions content field">
                     <template_field class="template_field" name="edit_node">edit_node</template_field>
                     <template_field class="template_field" name="delete_node">delete_node</template_field>
                     <!-- 
                       <template_field class="template_field_disabled" name="add_exercise">add_exercise</template_field>
                      -->  
                     <template_field class="template_field" name="add_note">add_note</template_field>
                     <template_field class="template_field" name="add_private_note">add_private_note</template_field>
                     <template_field class="template_field" name="edit_note">edit_note</template_field>
                     <template_field class="template_field" name="delete_note">delete_note</template_field>
                     <template_field class="template_field" name="publish_note">publish_note</template_field>
                     <!--
                       <template_field class="template_field_disabled" name="go_XML">go_XML</template_field> 
                     -->
                      <template_field class="template_field" name="go_map">go_map</template_field>
                   </div>
                      
                   <div class="title">
                     <i class="icon dropdown"></i>
                     <i18n>approfondimenti</i18n><i class="pin icon"></i>
                   </div>
                   <div class="deepenings content field">
                     <template_field class="template_field" name="index">index</template_field>
                   </div>
                   
                   <div class="title">
                     <i class="icon dropdown"></i>
                     <i18n>collegamenti</i18n><i class="url icon"></i>
                   </div>
                   <div class="links content field">
                       <template_field class="template_field" name="link">link</template_field>
                   </div>
                   
                   <div class="title">
                     <i class="icon dropdown"></i>
                     <i18n>esercizi</i18n><i class="text file outline icon"></i>
                   </div>
                   <div class="exercises content field">
                     <template_field class="template_field" name="exercises">exercises</template_field>
                   </div>
                   
                   <div class="title">
                     <i class="icon dropdown"></i>
                     <i18n>risorse</i18n><i class="browser icon"></i>
                   </div>
                   <div class="resources content field">
                     <template_field class="template_field" name="media">media</template_field>
                   </div>
                  </div>
                  <!-- /accordion -->  
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
                    <li class="loader-wrapper">
                             <div class="ui medium inline text loader">
                                 <i18n>Caricamento</i18n>...
                              </div>
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
