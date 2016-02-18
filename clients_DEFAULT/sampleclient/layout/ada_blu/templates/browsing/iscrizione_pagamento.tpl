<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/browsing/default.css" type="text/css">
    </head>
    <body>
        <a name="top"></a>
        <!-- testata -->
        <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
        </div>
        <!-- / testata -->
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
            <!-- menudestra -->
            <div id="menuright" class="sottomenu_off menuright_default">
                <div id="topmenur">
                </div>
                <div id="menurightcontent">
                    <ul>
                        <li class="close">
                            <a href="#" onClick="toggleElementVisibility('menuright', 'right');">
                                <i18n>chiudi</i18n>
                            </a>
                        </li>
                        <li class="_menu">
                            <a href="main_index.php">
                                <i18n>indice</i18n>
                            </a>
                        </li>
                        <li class="_menu">
                        <template_field class="template_field" name="go_map">go_map</template_field>
                        </li>
                    </ul>
                </div>
                <div id="bottommenur">
                </div>
            </div>
            <!-- / menudestra  -->
        </div>
        <!-- / contenitore -->

        <!-- MENU A TENDINA -->
        <div id="mainmenu">
            <ul id="menu">
                <li id="home">
                    <a href="user.php">
                        <i18n>home</i18n>
                    </a>
                </li>
                <li id="com" class="unselectedcom" onClick="toggleElementVisibility('submenu_com','up')">
                    <a>
                        <i18n>comunica</i18n>
                    </a>
                </li>
                <li id="tools" class="unselectedtools" onClick="toggleElementVisibility('submenu_tools','up')">
                    <a>
                        <i18n>strumenti</i18n>
                    </a>
                </li>
                <li id="actions" class="unselectedactions" onClick="toggleElementVisibility('submenu_actions','up')">
                    <a>
                        <i18n>agisci</i18n>
                    </a>
                </li>
                <li id="ancora_menuright">
                    <a href="../info.php">
                        <i18n>corsi</i18n>
                    </a>
                </li>
<!--
                <li id="ancora_menuright" onClick="toggleElementVisibility('menuright', 'right');">
                    <a>
                        <i18n>Naviga</i18n>
                    </a>
                </li>
-->
                <li id="question_mark" class="unselectedquestion_mark" onClick="toggleElementVisibility('submenu_question_mark','up'); return false;">
                    <a>
                        <i18n>Help</i18n>
                    </a>
                </li>
                <li id="esc">
                    <a href="../index.php">
                        <i18n>esci</i18n>
                    </a>
                </li>
            </ul>
            <!-- / menu -->

            <!-- notifiche eventi -->
            <template_field class="template_field" name="events">events</template_field>
            <!-- / notifiche eventi -->
            <!-- tendina -->
            <div id="dropdownmenu">
                <!-- comunica -->
                <div id="submenu_com" class="sottomenu sottomenu_off">
                    <div id="_comcontent">
                        <ul>
                            <li>
                                <a href="#" onclick='openMessenger("../comunica/list_messages.php",800,600);'>
                                    <i18n>messaggeria</i18n>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- / comunica -->
                <!-- strumenti -->
                <div id="submenu_tools" class="sottomenu sottomenu_off">
                    <div id="_toolscontent">
                        <ul>
                            <li>
                                <a href="#" onclick='openMessenger("../comunica/list_events.php",800,600);'>
                                    <i18n>agenda</i18n>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- /strumenti -->
                <!-- azioni -->
                <div id="submenu_actions" class="sottomenu sottomenu_off">
                    <div id="_actionscontent">
                        <ul>
                            <li>
                                <a href="edit_user.php">
                                    <i18n>Modifica il tuo profilo</i18n>
                                </a>
                            </li>
                            <template_field class="template_field" name="submenu_actions">submenu_actions</template_field>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- / azioni -->
                <!-- puntoint -->
                <div id="submenu_question_mark" class="sottomenu  sottomenu_off">
                    <div id="_question_markcontent">
                        <ul>
                            <li>
                            <template_field class="template_field" name="help">help</template_field>
                            </li>
                            <li>
                                <a href="../help.php" target="_blank">
                                    <i18n>informazioni</i18n>
                                </a>
                            </li>
                            <li>
                                <a href="../credits.php">
                                    <i18n>credits</i18n>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="bottomsubmenu">
                    </div>
                </div>
                <!-- / puntoint -->
            </div>
            <!-- /tendina-->
        </div>
        <!-- / MENU A TENDINA -->
        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>