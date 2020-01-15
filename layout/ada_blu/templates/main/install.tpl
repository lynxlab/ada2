<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
</head>
<body >
<a name="top"></a>
<div id="pagecontainer">
<!-- testata -->
<div id="header">
    <template_field class="microtemplate_field" name="header">header</template_field>
</div>
<!-- / testata -->
<!-- contenitore -->
<div id="container">
    <!-- contenuto -->
    <div id="content">
        <div id="contentcontent">
            <div class="first">
                <div class="ui icon attached message">
                    <i class="info icon"></i>
                    <div class="content">
                        <div class="header">
                            <i18n>Installazione Piattaforma ADA</i18n>
                        </div>
                        <p>
                            <i18n>Questo script di installazione è realizzato per una installazione base della piattaforma ADA</i18n>
                        </p>
                        <p>
                            <i18n>L'installazione sarà fatta in modalità <strong>multiprovider</strong> ed è possibile attivare solo due providers</i18n>
                        </p>
                        <span style="font-size:small; opacity:0.5;">
                            <i18n>Per installazioni di tipo più complesso, consultare il file README.md</i18n>
                        </span>

                    </div>
                </div>
                <form class="ui form attached fluid segment" name="installform" method="POST" target="installResults">

                    <!-- MYSQL/MARIADB -->
                    <h2 class="ui black block header"><i18n>Configurazione MySQL/MariaDB</i18n></h2>
                    <div class="ui three fields">
                        <div class="field">
                            <label><i18n>Host - Porta di Default 3306</i18n><i class="small icon red asterisk"></i></label>
                            <input type="text" name="MYSQL_HOST" placeholder="localhost" value="localhost"
                            data-semantic-validate-type="empty" data-semantic-validate-prompt="<i18n>Inserire l'indirizzo del server MySQL/MariaDB</i18n>">
                        </div>
                        <div class="field">
                            <label><i18n>username</i18n><i class="small icon red asterisk"></i></label>
                            <input type="text" name="MYSQL_USER" placeholder="<i18n>MySQL/MariaDB username</i18n>"
                            data-semantic-validate-type="empty" data-semantic-validate-prompt="<i18n>Inserire l'username per il server MySQL/MariaDB</i18n>">
                        </div>
                        <div class="field">
                            <label><i18n>password</i18n><i class="small icon red asterisk"></i></label>
                            <input type="text" name="MYSQL_PASSWORD" placeholder="<i18n>MySQL/MariaDB password</i18n>"
                            data-semantic-validate-type="empty" data-semantic-validate-prompt="<i18n>Inserire la password per il server MySQL/MariaDB</i18n>">
                        </div>
                    </div>

                    <!-- DATABASE -->
                    <h2 class="ui black block header"><i18n>Configurazione DataBase</i18n></h2>
                    <div class="ui two fields">
                        <div class="field">
                            <label><i18n>Nome provider zero</i18n><i class="small icon red asterisk"></i></label>
                            <input type="text" name="PROVIDER[0][NAME]" placeholder="<i18n>Nome provider zero</i18n>"
                            data-semantic-validate-type="empty" data-semantic-validate-prompt="<i18n>Inserire il nome del provider0</i18n>">
                        </div>
                        <div class="field">
                            <label><i18n>Nome database provider zero</i18n><i class="small icon red asterisk"></i></label>
                            <input type="text" name="PROVIDER[0][DB]" placeholder="<i18n>Nome database provider zero</i18n>"
                            data-semantic-validate-type="empty" data-semantic-validate-prompt="<i18n>Inserire il nome del database per il provider0</i18n>">
                        </div>
                    </div>
                    <div class="ui two fields">
                        <div class="field">
                            <label><i18n>Nome provider uno</i18n><i class="small icon red asterisk"></i></label>
                            <input type="text" name="PROVIDER[1][NAME]" placeholder="<i18n>Nome provider uno</i18n>"
                            data-semantic-validate-type="empty" data-semantic-validate-prompt="<i18n>Inserire il nome del provider1</i18n>">
                        </div>
                        <div class="field">
                            <label><i18n>Nome database provider uno</i18n><i class="small icon red asterisk"></i></label>
                            <input type="text" name="PROVIDER[1][DB]" placeholder="<i18n>Nome database provider uno</i18n>"
                            data-semantic-validate-type="empty" data-semantic-validate-prompt="<i18n>Inserire il nome del database per il provider1</i18n>">
                        </div>
                    </div>
                    <div class="ui two fields">
                        <div class="field">
                            <label><i18n>Provider di default</i18n><i class="small icon red asterisk"></i></label>
                            <div class="ui selection dropdown">
                                <input type="hidden" name="DEFAULT_PROVIDER"
                                data-semantic-validate-type="empty" data-semantic-validate-prompt="<i18n>Selezionare un provider di default</i18n>">
                                <div class="default text"><i18n>Selezionare un provider</i18n></div>
                                <i class="dropdown icon"></i>
                                <div class="menu">
                                    <div class="item" data-value="0"><i18n>Provider zero</i18n></div>
                                    <div class="item" data-value="1"><i18n>Provider uno</i18n></div>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label><i18n>Nome database common</i18n><i class="small icon red asterisk"></i></label>
                            <input type="text" name="COMMONDB" placeholder="<i18n>Nome database common</i18n>"
                            data-semantic-validate-type="empty" data-semantic-validate-prompt="<i18n>Inserire il nome del database common</i18n>">
                        </div>
                    </div>
                    <div class="ui two fields">
                        <div class="field">
                        </div>
                        <div class="field">
                            <span style="font-size:small; opacity:0.5;">
                                <i18n>Se i database non esistono e l'utente MySQL ne ha i permessi, l'installazione proverà a crearli</i18n>
                            </span>
                        </div>
                    </div>

                    <!-- ADA -->
                    <h2 class="ui black block header"><i18n>Configurazione ADA</i18n></h2>
                    <div class="field">
                        <label><i18n>URL base</i18n><i class="small icon red asterisk"></i></label>
                        <input type="text" name="HTTP_ROOT_DIR" placeholder="<i18n>URL base</i18n>"
                        data-semantic-validate-type="url" data-semantic-validate-prompt="<i18n>Inserire l'url di base per l'installazione</i18n>">
                    </div>
                    <div class="field">
                        <label style="text-transform:none;"><i18n>Moduli da non abilitare</i18n> <i18n>(elenco separato da virgola)</i18n></label>
                        <input type="text" name="MODULES_DISABLE" placeholder="<i18n>Moduli da non abilitare</i18n>" value="<template_field class="template_field" name="modsdisabled">modsdisabled</template_field>">
                    </div>
                    <div class="field">
                        <span style="font-size:small; opacity:0.5;">
                            <template_field class="template_field" name="modsavailable">modsavailable</template_field>
                        </span>
                    </div>
                    <div class="ui two fields">
                        <div class="field">
                            <label><i18n>Titolo pagina</i18n></label>
                            <input type="text" name="PORTAL_NAME" placeholder="<i18n>Titolo pagina</i18n>">
                        </div>
                        <div class="field">
                            <label style="text-transform:none;"><i18n>Password utente adminAda</i18n><i class="small icon red asterisk"></i></label>
                            <input type="text" name="ADMIN_PASSWORD" placeholder="<i18n>Password utente adminAda</i18n>"
                            data-semantic-validate-type="empty" data-semantic-validate-prompt="<i18n>Inserire la password per l'utente adminAda</i18n>">
                        </div>
                    </div>
                    <div class="ui two fields">
                        <div class="field">
                            <label><i18n>Email amministratore sistema ADA</i18n></label>
                            <input type="text" name="ADA_ADMIN_MAIL_ADDRESS" placeholder="<i18n>Email amministratore sistema ADA</i18n>">
                        </div>
                        <div class="field">
                            <label><i18n>Email &quot;noreply&quot; ADA</i18n></label>
                            <input type="text" name="ADA_NOREPLY_MAIL_ADDRESS" placeholder="<i18n>Email &quot;noreply&quot; ADA</i18n>">
                        </div>
                    </div>
                    <div class="ui error message"></div>
                    <div style="text-align:right;">
                        <div class="ui green submit button"><i18n>Installa ADA</i18n></div>
                    </div>
                </form>
                <div class="clearfix"></div>

                <iframe id="installResults" style="background-color:#000" datas="false" role="false" name="installResults"></iframe>
                <div class="clearfix"></div>
                <div id="retryButton-cnt" style="text-align:right;">
                    <div id="retryButton" class="ui purple button"><i18n>Riprova</i18n></div>
                </div>

                <!-- PIEDE -->
                <div id="footer_login">
                    <div class="clearfix"></div>
                    <div class="footerright" style="float:right">
                        <div class="copyright">ADA <i18n>&egrave; un software opensource rilasciato sotto licenza GPL</i18n>&nbsp;
                            <a href="http://www.lynxlab.com" target="_blank">&copy; Lynx s.r.l. - Roma</a>
                        </div>
                    </div>
                </div>
                <!-- / piede -->
            </div> <!-- first -->
        </div>
        <br class="clearfix">
    </div>

    <br class="clearfix">
    </div> <!--  / contenuto -->
    <div id="push"></div>
</div>
<!-- / contenitore -->

</body>
</html>
