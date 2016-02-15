<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
  <head>
    <!-- link rel="stylesheet" href="../../../css/comunica/default/default.css" type="text/css" -->
    <link rel="stylesheet" href="../../../css/comunica/claire/default.css" type="text/css">
  </head>
<body>
	<a name="top">
	</a>
<div id="header">
	<template_field class="microtemplate_field" name="header">header</template_field>
</div> 
<!-- menu -->
    <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>  
<!-- / menu --> 
<!-- contenitore -->
<div id="container">
                     <! -- PERCORSO -->
            <div id="journey">
            		 <i18n>dove sei: </i18n>
            		 <span>
            		 			 <i18n>chat</i18n>
            		 </span>
            	</div> <!-- / percorso -->
<div id="user_wrap">
<div id="label">
 <div class="topleft">
   <div class="topright">
      <div class="bottomleft">
         <div class="bottomright">
            <div class="contentlabel">
          		 			 <h1>
											 		 <i18n>chatroom</i18n>
											 </h1>
							</div>
					</div>
				</div>
			</div>
	</div>		
</div>
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
          
          <!-- label -->
          <!-- /label -->
          
          <!-- contenuto -->
          <div id="content">	 
          		 <div id="contentcontent">
                          <div class="first">
                                  <template_field class="template_field" name="chat">chat</template_field>
                  								
                          </div>
							 </div>
            <div id="bottomcont">
            </div>
          </div> <!--  / contenuto -->
</div> <!-- / contenitore -->
</div>
<!-- MENU A TENDINA -->

</div> <!-- / MENU A TENDINA -->

<!-- PIEDE -->
<div id="footer">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->

</body>
</html>