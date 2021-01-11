<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
</head>
<body >
<a name="top"></a>
<div id="pagecontainer">
<!-- testata -->
<div id="header">
		 <template_field class="microtemplate_field" name="header">header</template_field>
</div>
<!-- / testata -->
<!-- menu -->
    <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>
<!-- / menu -->
<!-- contenitore -->
<div id="container">
<!-- contenuto -->
<div id="content">
<div id="topcont">
<template_field class="template_field" name="message">message</template_field>
</div>
<div id="contentcontent">
         <div class="first ui two column stackable grid">
            <div class="sx ui eleven wide column">
                <div class="ada-column">
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
                <!-- div class="ada-column">
                  <div class="lynxRSS portlet">
                    <div class="portlet-header">RSS Feeds</div>
                    <div class="portlet-content">
                        <template_field class="template_field_disabled" name="lynxRSS">lynxRSS</template_field>
                    </div>
                  </div>
                </div -->
            </div>
            <div class="dx ui five wide column">
                <div class="ada-column">
                <div class="login portlet" id="loginform">
                    <div class="portlet-header"><i18n>login</i18n></div>
                    <div class="portlet-content">
                        <template_field class="template_field" name="form">form</template_field>
                        <!--dati utente-->
                            <template_field class="microtemplate_field" name="user_data_micro_index">user_data_micro_index</template_field>
                        <!-- / dati utente -->
                    </div>
    		</div>
                </div>

		<div class="helpcont ada-column">
                    <div class="portlet">
                        <div class="portlet-header">&nbsp;</div>
                    <div class="portlet-content">
                      <template_field class="template_field" name="helpmsg">helpmsg</template_field>
                    </div>
                    </div>
                </div>

                <!-- div class="helpcont ada-column">
                    <div class="portlet">
                        <div class="portlet-header"><i18n>facebook</i18n></div>
                        <div class="portlet-content">
                            <template_field class="template_field_disabled" name="fbRSS">fbRSS</template_field>
                        </div>
                    </div>
                </div -->
		        <!-- div class="helpcont ada-column">
                  <div class="TwitterTimeLine portlet">
                    <div class="portlet-header">Twitter</div>
                    <div class="portlet-content">
                    <template_field class="template_field_disabled" name="twitterTimeLine">twitterTimeLine</template_field>
                  </div>
                  </div>
                </div -->

            </div>
         </div>
         </div>
<br class="clearfix">
</div>

<!-- div id="newscont" class="ada-column">
   <div class="portlet">
     <div class="portlet-header"><i18n>Ultime news</i18n></div>
        <div class="portlet-content">
	  <template_field class="template_field_disabled" name="bottomnews">bottomnews</template_field>
	</div>
   </div>
</div -->
<br class="clearfix">
<div id="bottomcont"></div>
</div> <!--  / contenuto -->
</div>
<!-- / contenitore -->
		<div id="push"></div>
		</div>
<!-- PIEDE -->
<div id="footer_login">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->

</body>
</html>
