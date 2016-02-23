<html>
    <head>
        <!-- link rel="stylesheet" href="../../../css/tutor/default/default.css" type="text/css" -->
        <link rel="stylesheet" href="../../../css/tutor/masterstudio/default.css" type="text/css">
    </head>
    <body>
        <a name="top"></a>
        <div id="pagecontainer">
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div> <!-- / testata -->

        <!-- contenitore -->
        <div id="container">

             <!-- PERCORSO -->
                <div id="journey" class="ui tertiary inverted teal segment">
                    <i18n>dove sei: </i18n>
                    <span>
                        <template_field class="template_field" name="course_title">course_title</template_field>
                    </span>
                    <span>
                        <template_field class="template_field" name="path">path</template_field>
                    </span>
                    <i18n>livello: </i18n>
                    <span>
                        <template_field class="template_field" name="level">level</template_field>
                    </span>
                </div> <!-- / percorso -->



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
                </div>
                <div id="label">
                <!--    <div class="topleft">
                        <div class="topright">
                            <div class="bottomleft">
                                <div class="bottomright">

                                    <div class="contentlabel">
                -->
                                        <h1><template_field class="template_field" name="label">label</template_field></h1>
                <!--                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                -->
                </div>
            </div>
            
            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent">
                    <div class="first">
                        <div id="help">
                        <template_field class="template_field" name="help">help</template_field>
                        </div>
                        <div>
                            <i18n>corso: </i18n>
                            <template_field class="template_field" name="course_title">course_title</template_field>
                        </div>
                        <div>
                            <i18n>livello: </i18n>
                            <template_field class="template_field" name="level">level</template_field>
                            <div>
                                <i18n>studente: </i18n>
                                <template_field class="template_field" name="student">student</template_field>
                            </div>
                            <!--div>
                                <i18n>chi c'è in chat</i18n>
                                <template_field class="template_field_disabled" name="chat">chat</template_field>
                            </div>
                            <div>
                                <template_field class="template_field_disabled" name="back">back</template_field>
                            </div-->
                        </div>
                        <div id="data">
                        <template_field class="template_field" name="data">data</template_field>
                        </div>
                    </div>
                    <div id="bottomcont">
                    </div>
                </div>
                <!--  / contenuto -->
            </div>
            <!-- / contenitore -->
           </div>
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
        
        <!-- piede -->
            <div id="footer">
                <template_field class="microtemplate_field" name="footer">footer</template_field>
            </div> <!-- / piede -->

    </body>
</html>
