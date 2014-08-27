<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <link rel="stylesheet" href="../../../css/comunica/masterstudio_stabile/default.css" type="text/css">
</head>

<body>
<a name="top">
</a>
<div id="header">
		 <template_field class="microtemplate_field" name="header_com">header_com</template_field>
</div>
<!-- menu -->
    <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>  
<!-- / menu --> 
<!-- contenitore -->
<div id="container">
<!--dati utente-->
<div id="user_wrap">
<!-- label -->
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
<!-- /label -->
<!-- / dati utente -->
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
</div>
 <!-- / dati utente -->

<!-- contenuto -->
<div id="content">	 
<div id="contentcontent">
  <div class="first">
  		 <i18n>appuntamenti: </i18n>
  		 <div>
			 			<template_field class="template_field" name="messages">messages</template_field>
			 </div>
			 <div>
			 			<template_field class="template_field" name="menu_02">menu_02</template_field>  
			 </div>
  </div> 
</div>
<div id="bottomcont">
</div>
</div> <!--  / contenuto --> 
</div> <!-- / contenitore -->
<!-- PERCORSO -->
<div id="journey">
		 <i18n>dove sei: </i18n>
		 <span>
		 			 <i18n>agenda</i18n>
		 </span>
	</div> <!-- / percorso -->
</div> <!-- / MAINMENU -->
<!-- PIEDE -->
<div id="footer">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->
</body>
</html>
