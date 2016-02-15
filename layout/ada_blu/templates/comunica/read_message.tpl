<html>
<head>
  <!-- link rel="stylesheet" href="../../../css/comunica/default/default.css" type="text/css" -->
  <link rel="stylesheet" href="../../../css/comunica/claire/default.css" type="text/css">
</head>

<body>
<a name="top"></a>
<div id="pagecontainer">
<div id="header">
    <template_field class="microtemplate_field" name="header_com">header_com</template_field>
</div> 
<!-- menu -->
    <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>  
<!-- / menu --> 
<!-- PERCORSO -->
<div id="journey">
<i18n>dove sei: </i18n>
    <span>
        <i18n>messaggeria</i18n>
    </span>
</div> 
<!-- / percorso -->
<!-- contenitore -->
<div id="container">
<div id="user_wrap">

<div id="label">
		 <div class="topleft">
         <div class="topright">
            <div class="bottomleft">
               <div class="bottomright">
                  <div class="contentlabel">
                		  <h1><i18n>messaggi</i18n></h1>
									</div>
							</div>
						</div>
					</div>
			</div>		
</div>
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
				 <!-- label -->


</div>
</div>
    <!-- contenuto -->
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
    		</div> <!-- /first --> 
    </div> <!-- /contentcontent -->
    <div id="bottomcont">
    </div>
    </div> <!--  / contenuto -->
</div> <!-- / user_wrap -->
		</div>

		<div id="push"></div>
		</div>

<!-- PIEDE -->
<div id="footer">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->     
</body>
</html>
