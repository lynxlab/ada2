<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <!-- link rel="stylesheet" href="../../../css/browsing/default/default.css" type="text/css" -->
	<link rel="stylesheet" href="../../../css/admin/claire/default.css" type="text/css">
</head>

<body>
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
<!-- PERCORSO -->
<div id="journey" class="ui tertiary inverted teal segment">
		 <i18n>dove sei: </i18n>
		 <span>
		 <template_field class="template_field" name="module">module</template_field>
		 </span>
	</div> <!-- / percorso -->
	<!--dati utente-->
	<div id="status_bar">
		<div class="user_data_default status_bar">
		<template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
		</div>
	</div>
	<!-- / dati utente -->
  </div>
  <!-- label -->
  <div id="label">
  		 <div class="topleft">
           <div class="topright">
              <div class="bottomleft">
                 <div class="bottomright">
                    <div class="contentlabel">
                  		 			 <h1>
  								<template_field class="template_field" name="label">label</template_field>
  													 </h1>
  									</div>
  							</div>
  						</div>
  					</div>
  			</div>
  </div><!-- /label -->

  <!-- contenuto -->
  <div id="content">
    <div id="contentcontent">
        <div class="first">
          <template_field class="template_field" name="help">help</template_field>
            <template_field class="template_field" name="data">data</template_field>

       </div>
    </div>
    <div id="bottomcont">
    </div>
	</div><!-- /contenuto -->


  <!-- menudestra -->
  <!--
  <div id="menuright" class="sottomenu_off menuright_default">
    <div id="topmenur">
    </div>
    <div id="menurightcontent">
                  		 <ul>
                  		 		 <li class="close">
                  		 		 		 <a href="#" onclick="toggleElementVisibility('menuright', 'right');">
                  					   		</i18n>chiudi</i18n>
                  		 				 </a>
                  				 </li>
													 <li class="_menu">
													 		 <template_field class="disabled" name="menu_nav">menu_nav</template_field>
													 </li>
													 <li class="_menu">
													 		<a href="admin_config.php?mode=0">
													 		 <i18n>configurazione</i18n>
					 										</a>
													 </li>
													 <li class="_menu">
													 		<a href="admin_report.php">
													 		 <i18n>monitoraggio</i18n>
					 										</a>
													 </li>
													 <li class="_menu">
													 		<a href="search_user.php">
													 			<i18n>utenti</i18n>
					 										</a>
													 </li>
													 <li class="_menu">
													 		<a href="search_course.php">
																 <i18n>corsi</i18n>
															</a>
													 </li>
													 <hr>
													 <li class="_menu">
													 		 <a href="../user/index.php?module=edit_info.php">
															 		<i18n>modifica informazioni</i18n>
															 </a>
													 </li>
													 <li class="_menu">
													 		 <a href="../user/index.php?module=translation.php">
															 		<i18n>modifica traduzioni</i18n>
															 </a>
													 </li>
													 <li class="_menu">
													 		 <a href="tpl_list.php">
															 		<i18n>templates</i18n>
															 </a>
													 </li>

                  			</ul>
    </div>
    <div id="bottommenur">
    </div>
  </div>
  -->
  <!-- /menudestra  -->

</div> <!-- /contenitore -->
	<div id="push"></div>
	</div>
        <!-- com_tools -->
        <div class="clearfix"></div>
        <div id="com_tools">
            <div id="com_toolscontent">
                <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
            </div>
        </div>
        <!-- /com_tools -->
<!-- PIEDE -->
<div id="footer">
		 <template_field class="microtemplate_field" name="footer">footer</template_field>
</div> <!-- / piede -->

</body>
</html>
