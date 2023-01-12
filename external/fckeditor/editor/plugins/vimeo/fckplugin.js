/*
 * Vimeo Video PLUGIN 1.0 FOR FCKeditor 2.x
 * Copyright (C) 2008 VINCENZO PUCARELLI http://www.ollie10.it
 * Copyright (C) 2022 GIORGIO CONSORTI <g.consorti@lynxlab.com>
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 */


// REGISTER THE RELATED COMMAND
FCKCommands.RegisterCommand('Vimeo', new FCKDialogCommand(FCKLang['DlgVimeoTitle'], FCKLang['DlgVimeoTitle'], FCKConfig.PluginsPath + 'vimeo/vimeo.html', 450, 350));

// CREATE THE "Vimeo" TOOLBAR BUTTON
var oFindItem = new FCKToolbarButton('Vimeo', FCKLang['VimeoTip']);
oFindItem.IconPath = FCKConfig.PluginsPath + 'vimeo/vimeo.gif';

FCKToolbarItems.RegisterItem('Vimeo', oFindItem);
// 'Vimeo' is the name used in the Toolbar config
// ADD THIS LINE TO YOUR fckconfig.js FILE
// FCKConfig.Plugins.Add('vimeo', 'en,ja,it') ;