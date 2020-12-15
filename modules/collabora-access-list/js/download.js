/**
 * @package     collabora-access-list module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2020, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

function initCollabora(params) {
    const acl = new collaboraaclAPI.GrantAccess(params.url || null);

    $j('button.aclButton').click(function () {
        acl.GrantAccessForm(
            $j(this).data(),
            (fileAclId) => {
                $j(this).attr('data-file-acl-id', parseInt(fileAclId)).data('file-acl-id', parseInt(fileAclId));
            }
        );
    });
}
