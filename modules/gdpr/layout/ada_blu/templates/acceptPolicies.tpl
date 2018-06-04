<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
    </head>
    <body>
        <a name="top"></a>
		<div id="pagecontainer">
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div> <!-- / testata -->
<!-- menu -->
    <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>
<!-- / menu -->
        <!-- contenitore -->
        <div id="container">
           <div id="user_wrap">
                <div id="status_bar">
                    <!--dati utente-->
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
                            </span> </div>
                     </div>
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
            </div>
            <!--  / contenuto -->
        </div>
        <!-- / contenitore -->
		<div id="push"></div>
		</div>
        <!-- piede -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->

    </body>
</html>
