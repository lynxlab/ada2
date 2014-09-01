<html>
    <head>
        <link rel="stylesheet" href="../../css/tutor/default.css" type="text/css">
    </head>
    <body>
        <a name="top">
        </a>
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
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="path">path</template_field>
                </span>
            </div>
            <!-- / percorso -->

            
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
                <div id="contentcontent" class="contentcontent_default">
                    <!--<div class="first">-->
                    <div id="help">
                        <template_field class="template_field" name="help">help</template_field>
                    </div>
                    <div id="data">
                        <template_field class="template_field" name="data">data</template_field>
                    </div>
                    <!--</div>-->
                </div>
                <div id="bottomcont">
                </div>
            </div> <!--  / contenuto -->

            <!-- com_tools -->
            <div id="com_tools">
                <div id="topcom_t">
                </div>
                <div id="com_toolscontent">
                    <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
                </div>
                <div id="bottomcom_t">
                </div>
            </div> <!-- /com_tools -->

            <!-- menudestra -->
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
                    </ul>
                </div>
                <div id="bottommenur">
                </div>
            </div> <!-- / menudestra  -->

        </div> <!-- / contenitore -->

        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div> <!-- / piede -->


    </body>
</html>
