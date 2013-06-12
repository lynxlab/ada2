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
        </div>
        <!-- / testata -->

        <!-- contenitore -->
        <div id="container">
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
            </div>
            <!-- / dati utente -->

            <!-- label -->
            <div id="labelview">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
                                    <h1 class="<template_field class="template_field" name="icon">icon</template_field>">
                                        <template_field class="template_field" name="title">title</template_field>
                                    </h1>
                                    <ul>
                                        <li>
                                        <i18n>Versione: </i18n>
                                        <span>
                                            <template_field class="template_field" name="version">version</template_field>
                                        </span>
                                        <i18n>del</i18n>
                                        <span>
                                            <template_field class="template_field" name="date">date</template_field>
                                        </span>
                                        </li>
                                        <li>
                                        <i18n>autore:</i18n>
                                        <span>
                                            <template_field class="template_field" name="author">author</template_field>
                                        </span>
                                        </li>
                                        <li>
                                        <i18n>livello nodo:</i18n>
                                        <span>
                                            <template_field class="template_field" name="node_level">node_level</template_field>
                                        </span>
                                        </li>
                                        <li>
                                        <i18n>Keywords: </i18n>
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
            </div>
            <!-- /label -->
            </div>
            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent" class="contentcontent_view">
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

                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->

            <!-- com_tools -->
            <div id="com_tools">
                <div id="topcom_t">
                </div>
                <div id="com_toolscontent">
                    <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
                </div>
                <div id="bottomcom_t">
                </div>
            </div>
            <!-- /com_tools -->
        </div>
        <!-- / contenitore -->


        <!-- MENU A TENDINA -->
        <div id="mainmenu">
            <!-- menu -->
            <ul id="menu">
                <li id="back">
                    <a href="<template_field class="template_field" name="back">back</template_field>">
                        <i18n>Torna</i18n>
                    </a>
                </li>
            </ul>
            <!-- / menu -->

            <! -- PERCORSO -->
            <div id="journey">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="path">path</template_field>
                </span>
            </div>
            <!-- / percorso -->
        </div>
        <!-- / MENU A TENDINA -->

        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->

    </body>
</html>