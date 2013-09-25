<ul id="menu">
    <li id="home">
        <a href="tutor.php">
            <i18n>home</i18n>
        </a>
    </li>
    <li id="com" class="unselectedcom" onclick="toggleElementVisibility('submenu_com','up')">
        <a>
            <i18n>comunica</i18n>
        </a>
    </li>
    <li id="tools" class="unselectedtools" onclick="toggleElementVisibility('submenu_tools','up')">
        <a>
            <i18n>strumenti</i18n>
        </a>
    </li>
    <li id="actions" class="unselectedactions" onclick="toggleElementVisibility('submenu_actions','up')">
        <a>
            <i18n>agisci</i18n>
        </a>
    </li>
    <li id="ancora_menuright" onclick="toggleElementVisibility('menuright', 'right');">
        <a>
            <i18n>Naviga</i18n>
        </a>
    </li>
    <li id="question_mark" class="unselectedquestion_mark" onclick="toggleElementVisibility('submenu_question_mark','up'); return false;">
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
                <!--   		<li>
                    				<a href="../browsing/main_index.php?op=forum">
                    						<i18n>forum</i18n>
                    				</a>
                    		</li>
                -->
                <li>
                    <template_field class="template_field" name="ajax_chat_link">ajax_chat_link</template_field>
                </li>
                <!--   		<li>
                    		 		<a href="../user/index.php?module=download.php">
                            	 <i18n>collabora</i18n>
                    				</a>
                    		</li>
				-->
                <li>
                    <template_field class="template_field" name="mychat">mychat</template_field>
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
                <!--                  		<li>
                  				<a href="../browsing/mylog.php">
                  					 <i18n>diario</i18n>
                  				</a>
                  		</li>
                  		<li>
                  				<a href="../browsing/history.php">
                  						<i18n>cronologia</i18n>
                  				</a>
                  		</li>
                  		<li>
                   				<a href="../browsing/lemming.php">
                  						 <i18n>lessico</i18n>
                  			  </a>
                                      </li>
                  		<li>
                  				<a href="../browsing/search.php">
                  						 <i18n>cerca</i18n>
                  				</a>
                  		</li>
                -->
                <li>
                <template_field class="template_field" name="go_print">go_print</template_field>
                </li>
                <li>
                <template_field class="template_field" name="bookmarks">bookmarks</template_field>
                </li>
            </ul>
        </div>
        <div class="bottomsubmenu">
        </div>
    </div>
    <!-- / strumenti -->
    <!-- azioni -->
    <div id="submenu_actions" class="sottomenu sottomenu_off">
        <div id="_actionscontent">
            <ul>
                <li>
                 <template_field class="template_field" name="menu_01">menu_01</template_field>
                </li>
                <li>
                <template_field class="template_field" name="menu_02">menu_02</template_field>
                </li>
                <li>
                <template_field class="template_field" name="menu_03">menu_03</template_field>
                </li>
                <li>
                <template_field class="template_field" name="menu_04">menu_04</template_field>
                </li>
                <li>
                <template_field class="template_field" name="menu_05">menu_05</template_field>
                </li>
                <li>
                <template_field class="template_field" name="menu_06">menu_06</template_field>
                </li>
                <li>
                <template_field class="template_field" name="menu_07">menu_07</template_field>
                </li>
                <li>
                <template_field class="template_field" name="menu_08">menu_08</template_field>
                </li>
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
<!--/tendina -->

<!-- notifiche eventi -->
<template_field class="template_field" name="events">events</template_field>
<!-- / notifiche eventi -->