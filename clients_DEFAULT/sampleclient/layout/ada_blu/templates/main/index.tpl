<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
			<link rel="stylesheet" href="../../../css/main/masterstudio_stabile/default.css" type="text/css">
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
<!--
    <div id="submenubanner">
        <template_field class="template_field_disabled" name="infomsg">infomsg</template_field>
    &nbsp;
    </div>
-->

</div>
<div id="contentcontent">
         <div class="first">
            <div class="sx">
                <div class="wellcome">
                <template_field class="template_field" name="message">message</template_field>
                <template_field class="template_field" name="newsmsg">newsmsg</template_field>
                </div>
            </div>
            <div class="dx">
                <div class="login">
                <template_field class="template_field" name="form">form</template_field>
		    		<!--div class="forget">
				<a href="browsing/forget.php">
					 <i18n>Did you forget your password?</i18n>
				</a>

                </div-->
		</div>
		<div class="helpcont">
		  <template_field class="template_field" name="helpmsg">helpmsg</template_field>
		  <!-- widget sample
		  <template_field class="template_field" name="widgetSample">widgetSample</template_field>
		   -->
            </div>
         </div>
         </div>
<br class="clearfix">
</div>

<div id="newscont">
	<template_field class="template_field" name="bottomnews">bottomnews</template_field>
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
           			 <i18n>informazioni</i18n>
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
