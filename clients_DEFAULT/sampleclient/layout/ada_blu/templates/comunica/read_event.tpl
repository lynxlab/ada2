<html>
<head>
  <!-- link rel="stylesheet" href="../../../css/comunica/default/default.css" type="text/css" -->
  <link rel="stylesheet" href="../../../css/comunica/claire/default.css" type="text/css">
</head>

<body>
<a name="top">
</a>
<div id="header">
		 <template_field class="microtemplate_field" name="header_com">header_com</template_field>
</div> 
<!-- contenitore -->
<div id="container">
<div id="user_wrap">

<div id="label">
		 <div class="topleft">
         <div class="topright">
            <div class="bottomleft">
               <div class="bottomright">
                  <div class="contentlabel">
                		  <h1><i18n>appuntamenti</i18n></h1>
									</div>
							</div>
						</div>
					</div>
			</div>		
</div>
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
				 <!-- label -->


</div>
</div>
<div id="content">	 
<div id="contentcontent">
  <div class="first">
    	<div class="page_label">
    		<i18n>Da: </i18n>
      	<span>
      	<template_field class="template_field" name="mittente">mittente</template_field>
      	</span>
  		</div>
  		<div class="page_label">
      	<i18n>Destinatario: </i18n>
      	<span>
      			 <template_field class="template_field" name="destinatario">destinatario</template_field>
      	</span>
    	</div>
    	<div class="page_label">
      	<i18n>Data: </i18n>
      	<span>
         	   <template_field class="template_field" name="Data_messaggio">Data_messaggio</template_field>
      	</span>
      </div>
  		<div class="page_label">
    		<i18n>Oggetto: </i18n>
      	<span>
      			 <template_field class="template_field" name="oggetto">oggetto</template_field>
      	</span>
    	</div>
    	<div class="page_label">
        <i18n>Testo: </i18n><br />
      	<span>
      			 <template_field class="template_field" name="message_text">message_text</template_field>
      	</span>
    	</div>
  </div>
</div>
<div id="bottomcont">
</div>
</div> <!--  / contenuto -->
</div> <!-- / contenitore -->
<!-- MENU -->
<div id="mainmenucom">
<ul id="menu">
		<li id="selfclose">
				<template_field class="template_field" name="go_back">go_back</template_field> 
		</li>
		<li id="list">
				<a href="list_events.php">
			 		<i18n>lista eventi</i18n>
				</a>
		</li>
		<!--
		<li id="actions_read" class="unselectedactions_read" onclick="toggleElementVisibility('submenu_actions_read','up')">
				<a>
					 <i18n>azioni</i18n>
				</a>
		</li>
		-->
</ul> <!-- / menu -->
<! -- PERCORSO -->
<div id="journey" class="ui tertiary inverted teal segment">
		 <i18n>dove sei: </i18n>
		 <span>
		 			 <i18n>agenda</i18n>
		 </span>
	</div> <!-- / percorso -->
<!-- tendina -->
<div id="dropdownmenu">
<! -- azioni -->
<div id="submenu_actions_read" class="sottomenu sottomenu_off">
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
												<li>		
														<template_field class="template_field" name="menu_04">menu_04</template_field>
												</li>
										</ul>
</div>
<div class="bottomsubmenu">
</div>
</div><!-- / azioni -->
</div> <! --/tendina -->
</div> <!-- / MENU -->
<!-- PIEDE -->
<div id="footer">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede --> 
</body>
</html>
