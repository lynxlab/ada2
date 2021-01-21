<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <!--link rel="stylesheet" href="../../../css/comunica/default/default.css" type="text/css"-->
    <link rel="stylesheet" href="../../../css/comunica/masterstudio_stabile/default.css" type="text/css">
</head>

<body>
    <a name="top">
    </a>
    <!-- contenitore -->
    <div id="container">
        <div id="header">
            <template_field class="microtemplate_field" name="header_com">header_com</template_field>
        </div>
        <!--dati utente-->
        <div id="status_bar">
            <div class="user_data_default status_bar">
                <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
                <span>
					<template_field class="template_field" name="message">message</template_field>
				</span>
            </div>
            <!-- / dati utente -->
        </div>

        <!-- contenuto -->
        <div id="content">
            <div id="contentcontent">
                <div class="first">
                    <template_field class="template_field" name="data">data</template_field>
                </div>
            </div>
            <div id="bottomcont">
            </div>
        </div>
        <!--  / contenuto -->
    </div>
    <!-- / contenitore -->
    <div id="push"></div>
    </div>
    <!-- MENU -->
    <div id="mainmenucom">
        <ul id="menu">
            <li id="selfclose">
                <a href="#" onClick="closeMeAndReloadParent();">
                    <i18n>chiudi</i18n>
                </a>
            </li>
            <!--		<li id="list">
				<a href="list_events.php">
    		 <i18n>appuntamenti</i18n>
    	  </a>
		</li>
-->
        </ul>
        <!-- / menu -->
        <!-- PERCORSO -->
        <div id="journey" class="ui tertiary inverted teal segment">
            <i18n>dove sei: </i18n>
            <span>
		 			 <i18n>agenda</i18n>
		 </span>
        </div>
        <!-- / percorso -->
    </div>
    <!-- / MAINMENU -->
    <!-- PIEDE -->
    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->
</body>

</html>
