<html>
<head>
  <!-- link rel="stylesheet" href="../../../css/tutor/default/default.css" type="text/css" -->
  <link rel="stylesheet" href="../../../css/tutor/claire/default.css" type="text/css">
</head>
<body>
<a name="top">
</a> 
<!-- testata -->
<div id="header">
		 <template_field class="microtemplate_field" name="header">header</template_field>
</div> <!-- / testata -->
<!-- contenitore -->
<div id="container">
<div id="user_wrap">
    <!-- label -->
    <div id="label">
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
    </div>
    <!-- /label -->
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
  </div><!-- contenuto -->
<div id="content">	 
<div id="contentcontent">
                		 <div class="first">
										 			<template_field class="template_field" name="help">help</template_field>
                          <template_field class="template_field" name="class">class</template_field>
													<div> 
                            <i18n>Corsista:</i18n>
                            <template_field class="template_field" name="student">student</template_field>
													</div>
                          <div>
  													<i18n>Corso:</i18n>
                            <template_field class="template_field" name="course_title">course_title</template_field>
													</div>
													<div>
															 <template_field class="template_field" name="history">history</template_field> 
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
				<a href="tutor.php">
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
		</ul> <!-- / menu -->
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
                    				<a href="../browsing/main_index.php?op=forum">
                    						<i18n>forum</i18n>
                    				</a>
                    		</li>
                    		<li>
                    				<template_field class="template_field" name="ajax_chat_link">ajax_chat_link</template_field>
                    		</li>
                    </ul> 
</div>
<div class="bottomsubmenu">
</div>
</div><!-- / comunica -->
<!-- strumenti -->
<div id="submenu_tools" class="sottomenu sottomenu_off">
<div id="_toolscontent">
                  <ul>		
                  		<li>
                  				<a href="#" onclick='openMessenger("../comunica/list_events.php",800,600);'>
                  					 <i18n>agenda</i18n>
                  				</a>
                  		</li>
                  </ul> 
</div>
<div class="bottomsubmenu">
</div>
</div><!-- / strumenti -->
<! -- azioni -->
<div id="submenu_actions" class="sottomenu sottomenu_off">
<div id="_actionscontent">
                    <ul>
                    		<li>
                    				<template_field class="template_field" name="menu_01">menu_01</template_field>
                    		</li>
                    		<li>
                    				<template_field class="template_field" name="menu_02">menu_02</template_field>
                    		</li>
                    		<li>
                    				<template_field class="template_field" name="menu_03">menu_03</template_field>
                    		</li>
                    </ul> 
</div>
<div class="bottomsubmenu">
</div>
</div><!-- / azioni -->
<!-- puntoint -->
<div id="submenu_question_mark" class="sottomenu  sottomenu_off">
<div id="_question_markcontent">
                    <ul>
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
</div> <!-- / puntoint -->
</div> <! --/tendina -->
<! -- PERCORSO -->
<div id="journey" class="ui tertiary inverted teal segment">
		 <i18n>dove sei: </i18n>
		 <span>
		 			 <template_field class="template_field" name="course_title">course_title</template_field>
		 </span>
		 <span>
		 			 <template_field class="template_field" name="path">path</template_field>
		 </span>
	</div> <!-- / percorso -->
</div> <!-- / MENU A TENDINA -->

<!-- PIEDE -->
<div id="footer">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->
</body>
</html>
