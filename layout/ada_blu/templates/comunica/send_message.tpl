<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
    <!-- link rel="stylesheet" href="../../../css/main/default/default.css" type="text/css" -->
    <link rel="stylesheet" href="../../../css/main/default/default.css" type="text/css">
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
        <div id="journey" class="ui tertiary inverted teal segment">
            <i18n>dove sei:</i18n>
            <span>
           <i18n>messaggeria</i18n>
    </span>
        </div>
        <!-- / percorso -->

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
                                        <h1>
                                            <i18n>nuovo messaggio</i18n>
                                        </h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /label -->
                <!-- dati utente -->
                <div id="status_bar">
					<div class="user_data_default status_bar">
						<template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
						<span>
							<template_field class="template_field" name="message">message</template_field>
						</span>
					</div>
                </div>
                <!-- / dati utente -->

            </div>

            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent">
                    <div class="first">
                        <template_field class="template_field" name="status">status</template_field>
                        <form name="form" method="post" accept-charset="UTF-8" action="send_message.php">
                            <div class="edit">
                                <div>
                                    <i18n>Destinatari: </i18n>
                                    <div id="js_destinatari_sel" name="js_destinatari_sel">
                                        <template_field class="template_field" name="destinatari">destinatari</template_field>
                                    </div>
                                </div>
                                <i18n>Oggetto: </i18n>
                                <div>
                                    <input id="oggetto" type="text" name="titolo" maxlength="255" size="60" value="<template_field class=" template_field " name="titolo ">titolo</template_field>">
                                </div>
                                <div>
                                    <i18n>Testo: </i18n>
                                    <div>
                                        <textarea name="testo" cols="60" rows="10" WRAP="physical"><template_field class="template_field" name="testo">testo</template_field></textarea>
                                    </div>
                                </div>
                                <p>
                                    <input type="submit" name="spedisci" value="<i18n>Spedisci</i18n>">
                                    <input type="reset" name="pulisci" value="<i18n>Annulla</i18n>">
                                </p>
                            </div>
                            <div class="menur">
                                <!--
				<div>
	            	<i18n>Priorita': </i18n>
	                <select name="priorita">
	                    <option value="2" selected>Normale</option>
	                    <option value="1">High</option>
	                	<option value="3">Low</option>
	            	</select>
				</div>
-->
                                <p>
                                    <i18n>Modo: </i18n>
                                    <select name="modo">
	                	<option value="M" selected><i18n>E-mail message</i18n></option>
	                    <option value="S"><i18n>ADA message</i18n></option>
	            	</select>
                                </p>
                                <div>
                                    <div>
                                        <p>
                                            <template_field class="template_field" name="rubrica">rubrica</template_field>
                                        </p>
                                        <div>
                                            <template_field class="template_field" name="student_button">student_button</template_field>
                                            <template_field class="template_field" name="tutor_button">tutor_button</template_field>
                                            <template_field class="template_field" name="author_button">author_button</template_field>
                                            <template_field class="template_field" name="admin_button">admin_button</template_field>
                                        </div>
                                        <p>
                                            <template_field class="template_field" name="indirizzi">indirizzi</template_field>
                                        </p>
                                    </div>
                                    <!--  <p>
	            	<input type="submit" name="conferma" value="Conferma">
	        	</p>
	        	-->
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- /first -->
                </div>
                <!-- contentcontent -->
                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->
        </div>
        <!-- / contenitore -->
        <div id="push"></div>
    </div>

    <!-- PIEDE -->
    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->
</body>

</html>
