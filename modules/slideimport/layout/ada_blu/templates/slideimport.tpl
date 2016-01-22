<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="../../css/switcher/default.css" type="text/css">
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
            <div id="status_bar">
            <!--dati utente-->
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
            </div>
            <!-- / dati utente -->
            </div>

            <!-- contenuto -->
            <div id="content">
                <div id="contentcontent">
                    <div class="first">
	                    <!-- steps -->
						<div class="ui center aligned basic segment stepcontainer">
							<div class="ui four steps">
							  <div class="ui active step" data-step="1">
							    <i class="upload large icon"></i><i18n>Carica un file</i18n>
							  </div>
							  <div class="ui step" data-step="2">
							    <i class="basic docs large icon"></i><i18n>Seleziona le pagine</i18n>
							  </div>
							  <div class="ui step" data-step="3">
							    <i class="settings large icon"></i><i18n>Configura importazione</i18n>
							  </div>
							  <div class="ui step" data-step="4">
							    <i class="check large icon"></i><i18n>Fatto</i18n>!
							  </div>
							</div>
						</div>
						<!-- /steps -->

                    	<!-- dropzone -->
                    	<div id="slideImportContainer" class="ui center aligned basic segment">
                    		<h2 class="ui header">
								<i class="upload big icon"></i>
								<div class="content">
									<i18n>Carica un file</i18n>
									<div class="sub header">
									<i18n>Carica un file Office o PDF da importare</i18n>
									</div>
								</div>
							</h2>
	                        <form id="slideImportDZ">
	                        	<span class="dz-message"><i18n>Trascina qui il file o clicca per importare</i18n></span>
	                        </form>
                        </div>
                        <!-- /dropzone -->
                        
                        <!-- loader -->
                    	<div id="importLoader">
			                 <div class="ui active inline text large loader">
			                     <i18n>Elaborazione file</i18n>...
			                  </div>
			             </div>
			             <!-- /loader -->
                        
                        <!-- preview box -->
                        <div id="previewBox" class="ui center aligned basic segment">
                        	<h2 class="ui header">
								<i class="docs basic  big icon"></i>
								<div class="content">
									<i18n>Seleziona le pagine</i18n>
									<div class="sub header">
									<i18n>Attendi l'antemprima e seleziona le pagine</i18n>
									</div>
								</div>
							</h2>
							<div class="ui basic segment">
	                            <button class="ui medium labeled icon right floated teal proceed disabled button">
	                            	<i18n>Avanti</i18n><i class="right arrow icon"></i>
	                            </button>
	  							<button type="button" class="ui medium labeled icon selectall button">
	  								<i class="icon checkmark"></i><i18n>Seleziona tutti</i18n>
	  							</button>
	  							<button type="button" class="ui medium labeled icon deselectall button ">
	  								<i class="icon checkbox minus sign"></i><i18n>Deseleziona tutti</i18n>
	  							</button>
	                        	<div class="clearfix"></div>
	                        	
		                        <!-- filled by js -->
		                        <div id="previewContainer" class="ui six column stackable grid"></div>
		                        
		                        <button class="ui medium labeled icon right floated teal proceed disabled button">
	                            	<i18n>Avanti</i18n><i class="right arrow icon"></i>
	                            </button>
		                        <button type="button" class="ui medium labeled icon selectall button">
	  								<i class="icon checkmark "></i><i18n>Seleziona tutti</i18n>
	  							</button>
	  							<button type="button" class="ui medium labeled icon deselectall button ">
	  								<i class="icon checkbox minus sign"></i><i18n>Deseleziona tutti</i18n>
	  							</button>
  							</div>
                        </div>
                        <!-- /preview box -->
                        <div class="clearfix"></div>
                        
                        <!-- import settings -->
						<div id="selectCourseContainer" class="ui center aligned basic segment">
							<h2 class="ui header">
								<i class="settings large icon"></i>
								<div class="content">
									<i18n>Configura importazione</i18n>
									<div class="sub header"><i18n>Scegli come e dove importare</i18n></div>
								</div>
							</h2>
							
							<div class="ui segment importsettings">
								<h3 class="ui header"><i18n>Come importare</i18n></h3>
								<div class="ui divider"></div>
								<div class="ui form">
								  <div class="inline fields">
								    <div class="field">
								      <div class="ui radio checkbox">
								        <input type="radio" name="importSlideshow" checked="" tabindex="0" class="hidden" value="1">
								        <label><i18n>In unico nodo, con slideshow</i18n></label>
								      </div>
								    </div>	     
								    <div class="field">
								      <div class="ui radio checkbox">
								        <input type="radio" name="importSlideshow" tabindex="0" class="hidden" value="0">
								        <label><i18n>Un nodo per ogni pagina selezionata</i18n></label>
								      </div>
								    </div>        
								  </div>
								</div>
							</div>
							
							<div id="whereImport" class="ui segment">
								<h3 class="ui header"><i18n>Dove importare</i18n></h3>
								<div class="ui divider"></div>
								<div id="importToCourse">
									<h4 class="ui header"><i18n>In un nuovo corso</i18n></h4>
									<div class="ui action input">
										<input id="newCourseName" type="text" style="width:50rem;" maxlength="255" placeholder="Titolo nuovo corso">
										<button type="button" onclick="doImport(true);" class="ui teal labeled icon button">
											<i18n>Crea corso e importa</i18n><i class="add icon"></i>
										</button>
									</div>
										<p><i18n>Verrà creato un corso di livello servizio <strong>Corso OnLine</strong> e con le
										   impostazioni più comuni che potrai modificare dalla gestione dei corsi.</i18n></p>
								</div>	
								<div class="ui horizontal divider"><i18n>OPPURE</i18n></div>
								<div id="importToNode">
									<h4 class="ui header"><i18n>Nel corso e nodo selezionati</i18n></h4>
									<span id="selCourse"></span>
									<span id="selNode"></span>
									<template_field class="template_field" name="course_select">course_select</template_field>
									<div id="courseTree"></div>
								 	<button onclick="doImport(false);" class="ui teal labeled icon right floated button">
                            			<i class="add icon"></i><i18n>Importa</i18n>
                            		</button>
								</div>
								<div class="ui dimmer">
    								<div class="ui text loader"><i18n>Carico la struttura del corso</i18n></div>
  							  	</div>
							</div>
							<button onclick="javascript:$j('#selectCourseContainer').fadeOut('slow',function() { displayPreview(); });" class="ui red labeled icon left floated button">
                           		<i class="left arrow icon"></i><i18n>Indietro</i18n>
                           	</button>
					</div>
					<!-- /import settings -->
					<div class="clearfix"></div>
                        
                        <template_field class="template_field" name="data">data</template_field>
                    </div> <!-- /first -->
                </div> <!-- /contentcontent -->

                <div id="bottomcont">
                </div>
            </div>
            <!--  / contenuto -->

        </div>
        <!-- / contenitore -->
        <div id="push"></div>
        </div>
        
        <!-- piede -->
        <div id="footer">
            <template_field class="microtemplate_field" name="footer">footer</template_field>
        </div>
        <!-- / piede -->
        
        <!-- used by js to fill #previewContainer -->
        <div id="previewPageTemplate">
	        <div class="column">
				<h5 class="ui teal inverted top attached header"><i18n>Pagina</i18n>&nbsp;<span class="pagenumber"></span></h5>
				<div class="ui segment">
					<img class="ui image preview" src="layout/blank.gif"/>
				</div>
		      	<h5 class="ui teal inverted bottom attached header"><label><input name="selectedPages[]" type="checkbox"><i18n>Importa</i18n></label></h5>
		    </div>
        </div>
        
        <!-- used by js to have messages translations -->
        <div id="messagesContainer">
        	<span id="errortitle"><i class="basic error icon"></i><i18n>Errore</i18n></span>
			<span id="infotitle"><i class="basic info icon"></i><i18n>Info</i18n></span>
			<span id="emptycoursename"><i18n>Il nome del corso non può essere vuoto</i18n></span>
			<span id="nonodeselected"><i18n>Selezionare un nodo per l'importazione</i18n></span>
		</div>

    </body>
</html>
