<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
</head>
<body >
<a name="top">
</a>
<!-- testata -->
<div id="header">
		 <template_field class="microtemplate_field" name="header">header</template_field>
</div> 
<!-- / testata -->
<!-- contenitore -->
<div id="container">
<!-- contenuto -->
<div id="content">
<div id="topcont">
    <div id="submenubanner">
<!-- <template_field class="template_field" name="infomsg">infomsg</template_field> -->
&nbsp;
</div>

</div>	 
<div id="contentcontent">
         <div class="first">
            <div class="sx">
                <div class="column">
                    <!-- <div class="portlet">
                        <div class"portlet-header"><i18n>messaggi</i18n></div>
                        <template_field class="template_field" name="message">message</template_field>
                    </div>
                    -->
                    <div class="portlet">
                        <div class="portlet-header"><i18n>News</i18n></div>
                        <div class="portlet-content">
                             <template_field class="template_field" name="newsmsg">newsmsg</template_field>
                        </div>
                    </div>    
                </div>
                <div class="column">
                  <div class="lynxRSS portlet">
                    <div class="portlet-header">RSS Feeds</div>
                    <div class="portlet-content">
                        <template_field class="template_field" name="lynxRSS">lynxRSS</template_field>
                    </div>
                  </div>
                </div>
            </div>
            <div class="dx">
                <div class="column">
                <div class="login portlet">
                    <div class="portlet-header"><i18n>login</i18n></div>
                    <div class="portlet-content">
                        <template_field class="template_field" name="form">form</template_field>
                    </div>
    		</div>
                </div>    
                
		<div class="helpcont column">
                    <div class="portlet">
                        <div class="portlet-header">&nbsp;</div>
                    <div class="portlet-content">
                      <template_field class="template_field" name="helpmsg">helpmsg</template_field>
                    </div>  
                    </div>  
                </div>    

                <div class="helpcont column">
                    <div class="portlet">
                        <div class="portlet-header"><i18n>facebook</i18n></div>
                        <div class="portlet-content">
                            <template_field class="template_field" name="fbRSS">fbRSS</template_field>       
                        </div>
                    </div>    
                </div>
		<div class="helpcont column">
                  <div class="TwitterTimeLine portlet">
                    <div class="portlet-header">Twitter</div>
                    <div class="portlet-content">
                    <template_field class="template_field" name="twitterTimeLine">twitterTimeLine</template_field>
                  </div>
                  </div>
                </div>
  
            </div>
         </div>
         </div>
<br class="clearfix">
</div>

<div id="newscont" class="column">
   <div class="portlet">
     <div class="portlet-header"><i18n>Ultime news</i18n></div>
        <div class="portlet-content">
	  <template_field class="template_field" name="bottomnews">bottomnews</template_field> 	
	</div>
   </div>	
</div>
<br class="clearfix">
<div id="bottomcont"></div>
</div> <!--  / contenuto -->
</div> 
<!-- / contenitore -->
<!-- MENU A TENDINA -->
<div id="mainmenu">
<ul id="menu">
		<li id="actions" class="unselectedactions">
				<a href="browsing/registration.php">
					 <i18n>registrati</i18n>
				</a>
		</li>
		<li id="tools" class="unselectedtools">
				<a href="info.php">
           			 <i18n>Informazioni</i18n>
			        </a>
	 </li>
        <li id="question_mark" class="unselectedquestion_mark">
				<a href="help.php" target="_blank">
					 <i18n>help</i18n>
				</a>
        </li>
	<!--li id="language_choose" class="language_choose">
		| <a href="index.php?lang=bg">Български</a> | <a href="index.php?lang=en">English</a> | <a href="index.php?lang=es">Español</a> |  
		 <a href="index.php?lang=is">Íslenska</a> | <a href="index.php?lang=it">Italiano</a> | <a href="index.php?lang=ro">Română</a> 
	</li-->
	<br />
	<li id="help_main" class="help_main">
			<!--i18n>Explore the web site information or register and ask for a practitioner<i18n-->
		 	<template_field class="template_field" name="status">status</template_field> 
	</li>
</ul> <!-- / menu -->
</div> 
<!-- / MENU A TENDINA -->
<!-- PIEDE -->
<div id="footer_login">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->

</body>
</html>
