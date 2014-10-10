<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/services/default.css" type="text/css">
    </head>
    <body>
        <a name="top">
        </a>

        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div> <!-- / testata -->
        <!-- menu -->
            <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>  
        <!-- / menu -->
        <!-- contenitore -->
        <div id="container">
            <!-- percorso -->
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
            </div>
            <!-- / percorso -->
            <div id="status_bar">
            <!--dati utente-->
            <div id="user_data" class="user_data_view">
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
            </div> <!-- / dati utente -->
            <!-- label -->
            <div id="labelview">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
                                     <ul>
                                        <li>
                                        <template_field class="template_field" name="title">title</template_field>
                                        <span>, </span>
                                        <i18n>versione: </i18n>
                                        <span>
                                            <template_field class="template_field" name="version">version</template_field>
                                        </span>
                                        <i18n>del</i18n>
                                        <span>
                                            <template_field class="template_field" name="date">date</template_field>
                                        </span>
                                        </li>
                                        <!--li>
                          		 			<i18n>autore:</i18n>
                          		 			<span>
                          		 		 		<template_field class="template_field_disabled" name="author">author</template_field>
                          		 		 	</span>
                					    </li-->
                                        <li>
                                        <i18n>livello nodo:</i18n>
                                        <span>
                                            <template_field class="template_field" name="node_level">node_level</template_field>
                                        </span>
                                        </li>
                                        <li>
                                        <i18n>keywords: </i18n>
                                        <span class="keywords">
                                            <template_field class="template_field" name="keywords">keywords</template_field>
                                        </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /label -->
            </div>
            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent" class="contentcontent_note">
                    <div class="firstnode">
                        <div id="text">
                            <template_field class="template_field" name="head">head</template_field>
                            <template_field class="template_field" name="node_links">node_links</template_field>                         
                        </div>
                        <p>
                        <template_field class="template_field" name="form">form</template_field>
                        </p>
                    </div>
                </div>
                <div id="bottomcont_note">
                </div>
            </div> <!--  / contenuto -->
        </div> <!-- / contenitore -->

        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div> <!-- / piede -->
    </body>
</html>