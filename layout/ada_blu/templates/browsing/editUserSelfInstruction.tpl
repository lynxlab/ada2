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
                <span>  </span>
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
                                <i18n>ultimo accesso: </i18n>
                <span>
                    <template_field class="template_field" name="last_visit">last_visit</template_field>
                </span>
                                
                 <i18n>livello</i18n>: 
                 <span>
                    <template_field class="template_field" name="user_level">user_level</template_field>
                 </span>
                
            </div>
            <!-- / dati utente -->
            <!-- label -->
            <div id="label">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
                                   <template_field class="template_field" name="message">message</template_field>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /label -->
            </div>

            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent" class="contentcontent_default">
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

            <!-- menudestra -->
           
            <!-- / menudestra  -->
            <!--glossario-->
            <!--<template_field class="template_field" name="title">title</template_field>-->
            <template_field class="template_field" name="index">index</template_field>
        </div>
        
   
        
            <div id="menuright" class="sottomenu_off menuright_default">
                <div id="topmenur">
                </div>
                <div id="menurightcontent">
                    <ul>
                        <li class="close">
                            <a href="#" onClick="toggleElementVisibility('menuright', 'right');">
                                <i18n>chiudi</i18n>
                            </a>
                        </li>
                        
                        <li class="_menu">
                    <template_field class="template_field" name="search_form">search_form</template_field>
                        </li>
                                
                      
                    </ul>
                </div>
                <div id="bottommenur">
                </div>
            </div>
        
        
    
         <!--/glossario-->
         
         <!--search-->
         
       
         
         <!--/search-->
         
         
         
         
         
         
        <!-- / contenitore -->

        <!-- MENU A TENDINA -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="user.php">
                        <i18n>home</i18n>
                    </a>
                </li>
              
             

               <!--<li id="actions" class="unselectedactions" onClick="toggleElementVisibility('submenu_actions','up')">
                    <a>
                        <i18n>agisci</i18n>
                    </a>
                </li>-->
               
               <li id="actions" class="unselectedactions" onClick="toggleElementVisibility('submenu_actions','up')">
                 <template_field class="template_field" name="agisci">agisci</template_field>
               </li>
               
              <!-- <li id="ancora_menuright">
                    <a href="../info.php">
                        <i18n>corsi</i18n>
                    </a>
                </li>-->

              <!-- <li id="ancora_menuright" onclick="toggleElementVisibility('menuright', 'right');">
				<a>
					 <i18n>Naviga</i18n>
		 		</a>
		</li>-->
              <li id="ancora_menuright">
                <template_field class="template_field" name="naviga">naviga</template_field>
              </li>
              
               <li id="ancora_menuright">
                <template_field class="template_field" name="corsi">corsi</template_field>
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


                <!-- azioni -->
                <div id="submenu_actions" class="sottomenu sottomenu_off">
                    <div id="_actionscontent">
                        <ul>
                            <!--<li>
                                <a href="edit_user.php">
                                    <i18n>Modifica il tuo profilo</i18n>
                                </a>
                            </li>-->
                            
                            <li>
                              <template_field class="template_field" name="edit_user">edit_user</template_field>
                            </li>
                            <template_field class="template_field" name="submenu_actions">submenu_actions</template_field>
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
                </div>
                <!-- / puntoint -->
            </div>
            <!-- /tendina-->
        </div>
        <!-- / MENU A TENDINA -->
        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>