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
            <div id="journey" class="ui tertiary inverted teal segment">
                <i18n>dove sei: </i18n>
                <span>
                    <template_field class="template_field" name="course_title">course_title</template_field>
                </span>
                <span> > </span>
                <span>
                    <template_field class="template_field" name="path">path</template_field>
                </span>
            </div>
            <!-- / percorso -->

            <!-- dati utente -->
            <div id="status_bar">
                <!--dati utente-->
                <div class="user_data_default status_bar">
                    <template_field class="microtemplate_field" name="user_data_micro">user_data_micro</template_field>
                    <span>
                        <template_field class="template_field" name="message">message</template_field>
                    </span>
                </div>
            </div>
            <!-- / dati utente -->

            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent" class="contentcontent_default">
                    <div class="first">
                        <div id="help">
                            <template_field class="template_field" name="help">help</template_field>
                        </div>
                        <!--template_field class="template_field" name="data">data</template_field-->
                    </div>

                    <!--Ricerca avanzata -->
                    <div id="div_advancedSearch">
                        <div id="align_leftAdvanced">
                            <div class="search_formAdvanced">
                                <template_field class="template_field" name="advancedSearch_form">advancedSearch_form</template_field>
                            </div>
                            <template_field class="template_field" name="menuAdvanced_search">menuAdvanced_search</template_field>
                            <span>
                                <template_field class="template_field" name="simpleSearchLink">simpleSearchLink</template_field>
                            </span>
                        </div>

                        <div id="result_AdvancedSearch">
                            <template_field class="template_field" name="result_AdvancedSearch">result_AdvancedSearch</template_field>
                        </div>
                    </div>
                    <div class="clearfix"></div>

                    <!--Ricerca semplice -->
                    <div id="div_simpleSearch">
                        <div id="align_leftSimple">
                            <div class="search_SimpleForm">
                                <template_field class="template_field" name="form">form</template_field>
                            </div>
                            <template_field class="template_field" name="menu">menu</template_field>
                            <span>
                                <template_field class="template_field" name="advanced_searchLink">advanced_searchLink</template_field>
                            </span>
                        </div>

                        <div id="result_SimpleSearch">
                            <template_field class="template_field" name="results">results</template_field>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->
        </div>
        <!--  /contenitore -->
        <div id="push"></div>
    </div>

    <!-- PIEDE -->
    <!-- com_tools -->
    <div class="clearfix"></div>
    <div id="com_tools" style="visibility:hidden;">
        <div id="com_toolscontent">
            <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
        </div>
    </div>
    <!-- /com_tools -->

    <div id="footer">
        <template_field class="microtemplate_field" name="footer">footer</template_field>
    </div>
    <!-- / piede -->
</body>

</html>
