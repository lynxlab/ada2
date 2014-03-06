/**
 * APPS MODULE.
 *
 * @package        apps module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           oauth2
 * @version		   0.1
 */

function initDoc()
{
	$j('#getButton').button({
		icons : {
			primary : 'ui-icon-key'
		},
	});
}

function getAppSecretAndID(userID) {
	
	$j.ajax({
		beforeSend: function() { $j('#outputtoken').fadeOut(); },
		type	: 'POST',
		url		: HTTP_ROOT_DIR+ '/modules/apps/ajax/generateClient.php',
		data	: { userID: userID },
		dataType: 'html'
	})
	.done(function (html) {
		$j('#outputtoken').html(html).fadeIn();
	} );	
}
