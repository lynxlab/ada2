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
        </div> <!-- / testata -->
        <!-- menu -->
            <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>
        <!-- / menu -->
        <div id="help">
            <template_field class="template_field" name="help">help</template_field>
        </div>
        <!-- contenitore -->
        <div id="container">
            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent" class="contentcontent_default">
                    <!--div id="help">
                        <template_field class="template_field" name="help">help</template_field>
                    </div-->
                    <div id="data" class="first">
                        <template_field class="template_field" name="data">data</template_field>
                    </div>
                </div>
                <div id="bottomcont">
                </div>
            </div> <!--  / contenuto -->
        </div> <!-- / contenitore -->
		<div id="push"></div>
		</div>
       	<!-- com_tools -->
        <div class="clearfix"></div>
        <div id="com_tools" style="visibility:hidden;">
            <div id="com_toolscontent">
                <template_field class="microtemplate_field" name="com_tools">com_tools</template_field>
            </div>
        </div>
        <!-- /com_tools -->
        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div> <!-- / piede -->
        <span id="unameCheckProgress" style="display:none;">
            <div class="ui label"><i class="loading icon"></i><i18n>Controllo username in corso</i18n></div>
        </span>
        <span id="unameCheckOk" style="display:none;">
            <div class="ui green label"><i class="checkmark icon"></i><i18n>username valido</i18n></div>
        </span>
        <span id="unameCheckExists" style="display:none;">
            <div class="ui red label"><i class="ban circle icon"></i><i18n>username utilizzato o non valido, riprovare con username diverso</i18n></div>
        </span>
        <span id="unameCheckFail" style="display:none;">
            <div class="ui red label"><i class="attention icon"></i><i18n>Errore nel controllo username</i18n></div>
        </span>
    </body>
</html>
