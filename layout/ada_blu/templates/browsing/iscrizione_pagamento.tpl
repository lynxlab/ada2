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
                <div class="status">
                <i18n>status: </i18n>
                <span>
                    <template_field class="template_field" name="status">status</template_field>
                </span>
                </div>
                                <i18n>ultimo accesso: </i18n>
                <span>
                    <template_field class="template_field" name="last_visit">last_visit</template_field>
                </span>
            </div>
            <!-- / dati utente -->
            <!-- label -->
            <div id="label">
                <div class="topleft">
                    <div class="topright">
                        <div class="bottomleft">
                            <div class="bottomright">
                                <div class="contentlabel">
                                    <template_field class="template_field" name="message">message</template_field>
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
                <div id="contentcontent" class="contentcontent_default">
                    <div class="first">
                        <div id="help">
                            <template_field class="template_field" name="help">help</template_field>
                        </div>

            <strong>
            <i18n>Sei stato pre-iscritto al corso: </i18n>
            <template_field class="template_field" name="titolo_corso">titolo_corso</template_field>
            <br />
            <i18n>Il costo del corso è di euro </i18n>
            <template_field class="template_field" name="price">price</template_field>
             <BR />
            <i18n>per completare l'iscrizione è necessario effettuare il pagamento.</i18n>
            </strong>
            <BR /><BR />
            <!-- CARTA DI CREDITO PAYPAL -->
            <div id="paypal">
            <i18n>Puoi effettuare il pagamento con uno dei seguenti metodi:</i18n>
            <BR />
            <ul>
            <li><strong><i18n>Carta di credito o account PayPal.</i18n></strong><br />
            <i18n>In entrambi i casi sarai direzionato sul sito di PayPal dove potrai inserire i tuoi dati in maniera sicura attraverso una connessione cripata.<BR />
            Al termine del pagamento sarai nuovamente indirizzato su questo sito. <BR />
            Una volta effettuato il pagamento avrai accesso immediato al corso.</i18n>
            <table border="0" cellpadding="10" cellspacing="0">
            <tr>
            <td>
            <!-- PayPal Logo -->
            <a href="#" onclick="javascript:window.open('https://www.paypal.com/it/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside','olcwhatispaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=350');"><img  src="https://www.paypalobjects.com/WEBSCR-640-20101108-1/it_IT/IT/i/bnr/bnr_horizontal_solution_PP_178wx80h.gif" border="0" alt="Che cos'&egrave; PayPal"></a>
            <!-- PayPal Logo -->
            </td>
            <td class="pay_submit">
                <template_field class="template_field" name="data">data</template_field>
            </td>
            </tr>
            </table>
            </li>
            </ul>
            </div>
            <!-- FINE CARTA DI CREDITO PAYPAL -->
            <hr>
            <!-- BONIFICO -->
            <div id="bonifico">
            <ul>
            <li>
            <strong><i18n>Bonifico bancario</i18n></strong>.<br />
            <i18n>Nella causale del bonifico devi indicare: il tuo nome e cognome e il titolo del corso.<br />
            Una volta effettuato il bonifico, puoi mandare una comunicazione alla segreteria (info@altrascuola.it) con il dati del bonifico.<br />
            Ti verrà immediatamente attivata l'iscrizione.</i18n>
            <br /><br />
             <a href="../browsing/student.php">Torna alla home</a>
            </li>
            </ul>
            </div>
            <!-- FINE   BONIFICO -->
            <hr>

            <!-- ANNULLA -->
            <div id="annulla_iscrizione">
               <ul>
                 <li>
                    <template_field class="template_field" name="annulla_iscrizione">annulla_iscrizione</template_field>
                     <br /><br />
                     <i18n>IL PRESENTE TESTO E' CONTENUTO NEL TEMPLATE: iscrizione_pagamento.tpl nella directory</i18n> layout/clear/templates/browsing/
                 </li>
               </ul>
            </div>
            <!-- FINE ANNULLA -->
            <hr>






                    </div>
                </div>
                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->
            <!-- menudestra -->
           
            <!-- / menudestra  -->
        </div>
        <!-- / contenitore -->
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
        </div>
        <!-- / piede -->
    </body>
</html>