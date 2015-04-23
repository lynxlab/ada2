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
                    <!-- start tre blocchi grafici homepage -->

			<div id="trebox">
				<!-- blocco1 -->
				<div id="blocco_uno">
                                    <div id="cosacedinuovo">
                                        <i18n>Cosa c'&egrave; di nuovo?</i18n>
                                        <img src="img/news_nodes_user.png" class="icon-block-user">
                                    </div>
					<ul id="lista_blocco_uno">
						<li id="new_nodes"><i18n>Contenuti aggiornati</i18n>:&nbsp;<template_field class="template_field" name="new_nodes_links">new_nodes_links</template_field></li>						
					</ul>
				</div>
				<!-- blocco1 end -->
				<!-- blocco2 -->
				<div id="blocco_due">
					<div id="corsodilingua">
                                            <i18n>Il corso</i18n>
                                            <img src="img/course_news_user.png" class="icon-block-user">
                                        </div>
						<ul id="lista_blocco_due">
							<li id="gostart"><template_field class="template_field" name="gostart">gostart</template_field></li>		
							<!--li id="gocontinue"><template_field class="template_field" name="gocontinue">gocontinue</template_field></li-->		
							<li id="goindex"><template_field class="template_field" name="goindex">goindex</template_field></li>
						</ul>
				</div>
				<!-- blocco2 end -->
				<!-- blocco3 -->
					<div id="blocco_tre">	
					<div id="laclasse">
                                            <i18n>La classe</i18n>
                                            <img src="img/classroom_info_user.png" class="icon-block-user">
                                        </div>	
						<ul id="lista_blocco_tre">
							<li id="goclasse"><template_field class="template_field" name="goclasse">goclasse</template_field></li>		
							<li id="goforum"><template_field class="template_field" name="goforum">goforum</template_field></li>
							<!--li id="msg_forum"><i18n>Nuove note</i18n>:&nbsp;<template_field class="template_field" name="msg_forum">msg_forum</template_field></li-->															
						</ul>
					</div>
				<!-- blocco3 end -->
			</div>
			<!-- end tre blocchi grafici homepage -->
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
           
            <!-- / menudestra  -->
        </div>
</div>
        <!-- / contenitore -->

        <!-- PIEDE -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
    </body>
</html>