<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/switcher/default.css" type="text/css">
    </head>
    <body>
        <a name="top"></a>
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div> <!-- / testata -->

        <!-- contenitore -->
        <div id="container">



			<!-- testata ICON -->
        <div id="testataICON">
		</div>


		        <!-- menu a tendina -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="index.php">
                        <i18n>home</i18n>
                    </a>
                </li>
                <li id="question_mark" class="unselectedquestion_mark">
                    <a href="help.php" target="_blank">
                        <i18n>aiuto</i18n>
                    </a>
                </li>
                <li id="esc">
                    <a href="index.php">
                        <i18n>esci</i18n>
                    </a>
                </li>
            </ul>
			       </div>
        <!-- / menu a tendina -->



            <!-- PERCORSO -->
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
            </div>
            <div id="user_wrap">
                <div id="status_bar">
                    <!--dati utente-->
                    <div id="user_data" class="user_data_default">
                        <i18n>utente: </i18n>
                        <span>
                            <template_field class="template_field" name="user_name">user_name</template_field>
                        </span>
<!--

			  <i18n>tipo: </i18n>
                        <span>
                            <template_field class="template_field_disabled" name="user_type">user_type</template_field>
                        </span>
                        <div class="status">
                            <i18n>status: </i18n>
                            <span>
                                <template_field class="template_field_disabled" name="status">status</template_field>
                            </span>
			  </div>
-->
                      </div>
                    <!-- / dati utente -->
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
                    </div>
                    <!-- /label -->
                </div>
            </div>

            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent">
                    <div class="first">
                        <div id="help">
                            <template_field class="template_field" name="help">help</template_field>
                        </div>
                        <div id="left">
                            <div id="login_div">
                                <i18n>Se sei già registrato, fai login.</i18n>
                                <div id="login_form">
                                    <template_field class="template_field" name="data">data</template_field>
                                </div>
                            </div>
                        </div>
                        <div id="right">
                            <div id="registration_div">
                                <i18n>Se ancora non sei registrato, registrati ora.</i18n>
                                <div id="registration_form">
                                    <template_field class="template_field" name="registration_data">registration_data</template_field>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->

            <!-- com_tools -->
            <div id="com_tools">
                <div id="topcom_t">
                </div>
                <div id="com_toolscontent">
                    <!--template_field class="microtemplate_field_disabled" name="com_tools">com_tools</template_field-->
                </div>
                <div id="bottomcom_t">
                </div>
            </div>
            <!-- /com_tools -->
        </div>
        <!-- / contenitore -->


            <!-- tendina -->
            <div id="dropdownmenu">
            </div>
            <!--/tendina -->



        <!-- piede -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->

    </body>
</html>