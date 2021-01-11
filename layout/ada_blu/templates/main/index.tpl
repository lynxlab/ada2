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
<!--
    <div id="submenubanner">
    <template_field class="template_field_disabled" name="infomsg">infomsg</template_field>
&nbsp;
</div>
-->
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
                <div class="ada-column">
                  <div class="lynxRSS portlet">
                    <div class="portlet-header">RSS Feeds</div>
                    <div class="portlet-content">
                        <template_field class="template_field" name="lynxRSS">lynxRSS</template_field>
                    </div>
                  </div>
                </div>
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
                        <div class="portlet-header"><i18n>Contatti</i18n></div>
                    <div class="portlet-content">
                      <template_field class="template_field" name="helpmsg">helpmsg</template_field>
                    </div>
                    </div>
                </div>

                <div class="helpcont ada-column">
                    <div class="portlet">
                        <div class="portlet-header"><i18n>facebook</i18n></div>
                        <div class="portlet-content">
                            <div class="fb-page" data-href="https://www.facebook.com/FoliasCooperativaSociale" data-tabs="timeline" data-width="500" data-height="" data-small-header="true" data-adapt-container-width="true" data-hide-cover="true" data-show-facepile="false">
                                <blockquote cite="https://www.facebook.com/FoliasCooperativaSociale" class="fb-xfbml-parse-ignore">
                                    <a href="https://www.facebook.com/FoliasCooperativaSociale">ADHR FORMAZIONE</a>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>
		<div class="helpcont ada-column">
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

<div id="newscont" class="ada-column">
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
		<div id="push"></div>
		</div>
<!-- PIEDE -->
<div id="footer_login">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/it_IT/sdk.js#xfbml=1&version=v6.0"></script>
</body>
</html>
