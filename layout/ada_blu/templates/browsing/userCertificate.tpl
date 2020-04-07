<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/switcher/default.css" type="text/css">
    </head>
    <body>
        <a name="top"></a>
        <!-- testata -->
        <template_field class="template_field" name="logoProvider">logoProvider</template_field>
        <template_field class="template_field" name="logo">logo</template_field>

        <div class="header_Certificate">
        </div>
		<div class="clearfix"></div>
        <!-- / testata -->

        <!-- contenitore -->

        <div class="title_Certificate">
            <template_field class="template_field" name="title">title</template_field>
        </div>
        <div class="header_Certificate">
            <i18n> Si attesta che </i18n>
        </div>
        <div class="user_data" style="
        max-height:3.8em; overflow: hidden; text-align:center;
        margin: 2.5em 0; font-weight:bold; font-size:2.2em; text-transform:capitalize;">
            <template_field class="template_field" name="userFullName">userFullName</template_field>
            <template_field class="template_field" name="birthSentence">birthSentence</template_field>
            <template_field class="template_field" name="CodeFiscSentence">CodeFiscSentence</template_field>
        </div>

        <div class="user_data">
            in data <template_field class="template_field" name="creditsdate">creditsdate</template_field>  ha completato con<br/>successo il corso di formazione a distanza
        </div>
        <div class="course_title" style="
        max-height: 5em; overflow: hidden; margin: 1.5em 0; font-weight:bold; text-transform:capitalize;">
            <template_field class="template_field" name="mainSentence">mainSentence</template_field>
        </div>
        <div class="user_data">
            superando positivamente il questionario finale
        </div>

        <div style="position:absolute; width:100%; bottom:4.8em; text-align: center; page-break-after: auto;">
            <template_field class="template_field" name="timbroFirma">timbroFirma</template_field>
        </div>

		<div style="position:absolute; width:100%; bottom:2em; text-align: left; font-size:small;">
            <template_field class="template_field" name="placeAndDate">placeAndDate</template_field>
     	</div>

        <!-- contenitore -->
    </body>
</html>
