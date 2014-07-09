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
                    
                    
					<div class="navbar_top">
						<div class="previous_arrow">
							<div class="go_prev">
								<a href='javascript:history.back();'><i18n>Torna</i18n></a>
							</div>

						</div>
						
						<div class="next_arrow">				
	                    	<div class="go_next">
								<template_field class="template_field" name="go_next">go_next</template_field>
							</div>							
						</div>
					</div>
					
					
                    
                    <div class="firstnode">
                        <template_field class="template_field" name="text">text</template_field>
						
						<!-- 
						<div class="stabilo_viola">testo stabilo viola</div>
						<span class="didascalia">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla tortor nunc, luctus sit amet metus at, fringilla pellentesque dolor. Nam in est dui. Quisque sagittis at enim quis rhoncus. Duis sit amet enim non nunc hendrerit porta. Etiam non metus vel erat tristique interdum. Cras nec elit enim. Sed porttitor tempus elit ac bibendum. Nam sit amet ipsum vitae lacus tincidunt tincidunt. Vestibulum non felis tellus.<BR> <BR>
						
						<div class="titolo_viola">testo TITOLO viola</div>

Pellentesque venenatis facilisis velit, ut hendrerit eros tincidunt lacinia. Nullam consequat odio vitae ullamcorper congue. Vestibulum a eros at risus lobortis eleifend. Mauris at pellentesque arcu. Sed in purus ligula. Vestibulum velit est, dictum non porta accumsan, vehicula in mi. In et porta mi. Etiam eget lacus consectetur, suscipit quam eu, egestas dui. Morbi iaculis fermentum dui at molestie. Curabitur in justo varius, dictum sem sed, mattis odio. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Donec blandit mauris ut ornare volutpat. Suspendisse vel est porttitor, consectetur nisi sed, mollis diam. Etiam malesuada lacinia leo, a iaculis enim pellentesque ut.<BR> <BR>

Curabitur mollis egestas leo. Sed fermentum quam urna, in cursus metus posuere eget. Phasellus laoreet fermentum orci, ac cursus velit porta sit amet. Morbi a odio in libero pretium vehicula. Donec et arcu sed metus fringilla commodo id vel neque. Donec scelerisque leo metus, sed ultricies justo consectetur vel. Vestibulum placerat nibh in ante molestie vestibulum. Phasellus vel libero ut justo porta consequat nec non neque. Nulla lobortis magna eget enim blandit aliquam. Sed ultrices tellus vel arcu commodo egestas. Donec vel fermentum metus. Quisque hendrerit iaculis leo, sed blandit justo pulvinar interdum. Suspendisse leo elit, varius at elementum vel, auctor vel dui. Maecenas congue, tellus quis tincidunt blandit, dolor augue ultrices nibh, vel commodo nibh neque quis nunc. </span>
						
						
						<div class="stabilo_rosso">testo STABILO rosso</div>
						<div class="titolo_rosso">testo TITOLO rosso</div>
						
						<span class="didascalia">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla tortor nunc, luctus sit amet metus at, fringilla pellentesque dolor. Nam in est dui. Quisque sagittis at enim quis rhoncus. Duis sit amet enim non nunc hendrerit porta. Etiam non metus vel erat tristique interdum. Cras nec elit enim. Sed porttitor tempus elit ac bibendum. Nam sit amet ipsum vitae lacus tincidunt tincidunt. Vestibulum non felis tellus.</span>
						
						<div class="stabilo_giallo">testo STABILO giallo</div>
						<div class="titolo_giallo">testo TITOLO giallo</div>
						
						<span class="didascalia">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla tortor nunc, luctus sit amet metus at, fringilla pellentesque dolor. Nam in est dui. Quisque sagittis at enim quis rhoncus. Duis sit amet enim non nunc hendrerit porta. Etiam non metus vel erat tristique interdum. Cras nec elit enim. Sed porttitor tempus elit ac bibendum. Nam sit amet ipsum vitae lacus tincidunt tincidunt. Vestibulum non felis tellus.</span>
						
						<div class="stabilo_verde">testo STABILO verde</div>
						<div class="titolo_verde">testo TITOLO verde</div>
<span class="didascalia">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla tortor nunc, luctus sit amet metus at, fringilla pellentesque dolor. Nam in est dui. Quisque sagittis at enim quis rhoncus. Duis sit amet enim non nunc hendrerit porta. Etiam non metus vel erat tristique interdum. Cras nec elit enim. Sed porttitor tempus elit ac bibendum. Nam sit amet ipsum vitae lacus tincidunt tincidunt. Vestibulum non felis tellus.</span>
						
						
						-->
						
                    </div>

					<div class="navbar_bottom">
						<div class="previous_arrow">
							<div class="go_prev">
								<a href='javascript:history.back();'><i18n>Torna</i18n></a>
							</div>
						</div>
						
						<div class="next_arrow">
							<div class="go_next">
								<template_field class="template_field" name="go_next">go_next</template_field>
							</div>				

						</div>
					</div>

		    <!-- <hr>
		    <div id="index_in_text">
		      <h3><i18n>note di classe</i18n></h3>
                            <template_field class="template_field" name="notes">notes</template_field> -->
			    <!--h3><i18n>Approfondimenti:</i18n></h3-->
			    <!--template_field class="template_field" name="index">index</template_field-->
				
		   <!-- </div>
		  <div id="exercises_in_text">
		      <h3><i18n>note personali</i18n></h3>
		       <template_field class="template_field" name="personal">personal</template_field>   -->
			<!--template_field class="template_field" name="exercises">exercises</template_field-->
		  
		  <!-- </div> -->

                </div>
				<!-- 
                <div id="bottomcont">
                </div> 
				-->
            </div>
            <!--  / contenuto -->
            <!-- com_tools -->
            <!--<div id="com_tools">
                <div id="topcom_t">
                </div>
                <div id="com_toolscontent">
                    <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
                </div>
                <div id="bottomcom_t">
                </div>
            </div>-->
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
                        <!--<li class="_menu">
							<template_field class="template_field" name="go_map">go_map</template_field>
                        </li>-->
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
                        <!--<li class="_name">
                        <i18n>esercizi</i18n>
                        </li>
                        <ul>
                            <li>
                            <template_field class="template_field" name="exercises">exercises</template_field>
                            </li>
                        </ul>-->
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

        <!-- menu -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="user.php">
                        <i18n>home</i18n>
                    </a>
                </li>
                <!--<li id="com" class="unselectedcom" onClick="toggleElementVisibility('submenu_com','up')">
                    <a>
                        <i18n>comunica</i18n>
                    </a>
                </li>-->
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
               <!-- <div id="submenu_com" class="sottomenu sottomenu_off">
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
                            
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>-->
                <!-- / comunica -->
                <!-- strumenti -->
                <div id="submenu_tools" class="sottomenu sottomenu_off">
                    <div id="_toolscontent">
                        <ul>
                         <!--   <li>
                                <a href="#" onclick='openMessenger("../comunica/list_events.php",800,600);'> 
                                    <i18n>agenda</i18n>
                                </a>
                            </li>-->
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
                            
                            
                       <!-- <li>
                            <a href="../browsing/main_index.php?op=glossary"> 
                           
                                <i18n>glossario</i18n> 
                            </a>
                         </li>-->
							<!--<li>
                                <template_field class="template_field" name="exercise_history">exercise_history</template_field>
                            </li>-->
							<li>
                                <template_field class="template_field" name="test_history">test_history</template_field>
                            </li>
                            
                            				<li>
                                <template_field class="template_field" name="survey_history">survey_history</template_field>
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
                            <!--<li>
                                <a href="edit_user.php">
                                    <i18n>Modifica il tuo profilo</i18n>
                                </a>
                            </li>-->
                           <!-- <li>
                            <template_field class="template_field" name="send_media">send_media</template_field>
                            </li>
                            <li>-->
                           <li>
                              <template_field class="template_field" name="edit_user">edit_user</template_field>
                            </li>
                            <template_field class="template_field" name="add_bookmark">add_bookmark</template_field>
                            </li>
                           <li>
                            <template_field class="template_field" name="add_node">add_node</template_field>
                            </li>
                            <li>
                           <template_field class="template_field" name="add_word">add_word</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="edit_node">edit_node</template_field>
                            </li>
                            <!-- versione con JS di controllo... ma dov'è?
                            <li>
                                <a href="#" onclick="<template_field class="template_field_disabled" name="delete_node">delete_node</template_field>">
                                    <i18n>Elimina nodo</i18n>
                                </a>
                            </li>
                            -->
                            <!-- version direttaa.... -->
                            <li>
                            <template_field class="template_field" name="delete_node">delete_node</template_field>
                            </li>

                            <li>
                            <template_field class="template_field" name="add_exercise">add_exercise</template_field>
                            </li>
							<li>
                            <template_field class="template_field" name="add_test">add_test</template_field>
                            </li>
							<li>
                            <template_field class="template_field" name="add_survey">add_survey</template_field>
							</li>
                           <!-- <li>
                            <template_field class="template_field" name="add_note">add_note</template_field>
                            </li>
                            <li>
                            <template_field class="template_field" name="add_private_note">add_private_note</template_field>
                            </li>
                            <li>-->
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
