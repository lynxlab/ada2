<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/browsing/default.css" type="text/css">
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
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
                <span>
                    <template_field class="template_field" name="path">path</template_field>
                </span>
            </div>
            <!-- / percorso -->
            <!--dati utente-->
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
                <i18n>livello: </i18n>
                <span>
                    <template_field class="template_field" name="user_level">user_level</template_field>
                </span>
            </div>
            <!-- / dati utente -->
            <div id="labelview">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
									<ul>
                                        <li>
											<template_field class="template_field" name="title">title</template_field>
                                        </li>
                                        <li>
                                        <!--i18n>autore:</i18n-->
                                        <!--span>
                                            <template_field class="template_field_disabled" name="author">author</template_field>
                                        </span-->
                                        </li>
                                        <!--li>
                                        <i18n>livello nodo:</i18n>
                                        <span>
                                            <template_field class="template_field_disabled" name="node_level">node_level</template_field>
                                        </span>
                                        </li-->
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /label -->
            </div>
            <!-- contenuto -->
            <div id="content_view">
                <div id="contentcontent" class="contentcontent_view">
                    <div class="first">
                        <template_field class="template_field" name="media">media</template_field>
                        <template_field class="template_field" name="text">text</template_field>
                    </div>
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
