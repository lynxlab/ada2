<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
   <body>
      <a name="top"></a>
      <div id="pagecontainer">
         <!-- testata -->
         <div id="header">
            <template_field class="microtemplate_field" name="header">header</template_field>
         </div>
         <!-- /testata -->
         <!-- menu -->
         <template_field class="microtemplate_field" name="adamenu">adamenu</template_field>
         <!-- / menu -->
         <!-- contenitore -->
         <div id="container">
            <!-- PERCORSO -->
            <div id="journey" class="ui tertiary inverted teal segment">
               <i18n>dove sei: </i18n>
               <span>
                  <template_field class="template_field" name="title">title</template_field>
               </span>
            </div>
            <div id="user_wrap">
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
                     <i18n>livello</i18n>
                     :
                     <span>
                        <template_field class="template_field" name="user_level">user_level</template_field>
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
            </div>
            <!-- contenuto -->
            <div id="content">
               <div id="contentcontent">
                  <div class="first">
                     <template_field class="template_field" name="data">data</template_field>
                     <div class="ui basic segment" id="mainPage">
                        <table id="repositoryList" class="hover row-border display ui table" cellspacing="0" width="100%">
                           <thead>
                              <tr>
                                 <th data-priority="1">
                                    <i18n>Titolo</i18n>
                                 </th>
                                 <th data-priority="5">
                                    <i18n>Titolo corso</i18n>
                                 </th>
                                 <th data-priority="3">
                                    <i18n>Descrizione</i18n>
                                 </th>
                                 <th data-priority="4">
                                    <i18n>Data Creazione</i18n>
                                 </th>
                                 <th data-priority="5">
                                    <i18n>Provider</i18n>
                                 </th>
                                 <th data-priority="2">
                                    <i18n>Azioni</i18n>
                                 </th>
                              </tr>
                           </thead>
                        </table>
                     </div>
                  </div>
                  <!-- /first -->
               </div>
               <!-- /contentcontent -->
            </div>
            <!-- /content -->
            <div id="bottomcont"></div>
         </div>
         <!--  /container -->
         <div id="push"></div>
      </div>
      <!-- / pagecontainer -->
      <!-- piede -->
      <div id="footer">
         <template_field class="microtemplate_field" name="footer">footer</template_field>
      </div>
      <!-- / piede -->
      <template_field class="microtemplate_field" name="mainloader">mainloader</template_field>
      <template_field class="microtemplate_field" name="smallmodal">smallmodal</template_field>
      <span id="unknownErrorMSG" style="display:none;"><i18n>Errore sconosciuto</i18n></span>
   </body>
</html>
