<?php
namespace AdobeConnect;

/**
 * Provides constants for Adobe Connect's permissions.
 *
 * @link   http://help.adobe.com/en_US/connect/9.0/webservices/WS8d7bb3e8da6fb92f73b3823d121e63182fe-8000_SP1.html#WS5b3ccc516d4fbf351e63e3d11a171ddf77-7fe9_SP1
 *
 * Special permissions
 * -------------------
 * The server defines a special principal, public-access, which combines with values of permission-id to create special
 * access permissions to meetings:
 *
 * - principal-id=public-access and permission-id=view-hidden means the Adobe Connect meeting is public, and anyone who
 *   has the URL for the meeting can enter the room.
 * - principal-id=public-access and permission-id=remove means the meeting is protected, and only registered users and
 *   accepted guests can enter the room.
 * - principal-id=public-access and permission-id=denied means the meeting is private, and only registered users and
 *   participants can enter the room.
 *
 * @author Gustavo Burgi <gustavoburgi@gmail.com>
 */
class Permission
{
    // The principal can view, but cannot modify, the SCO. The principal can take a course, attend a meeting as
    // participant, or view a folder’s content.
    const VIEW = 'view';

    // Available for meetings only. The principal is host of a meeting and can create the meeting or act as presenter,
    // even without view permission on the meeting’s parent folder.
    const HOST = 'host';

    // Available for meetings only. The principal is presenter of a meeting and can present content, share a screen, send
    // text messages, moderate questions, create text notes, broadcast audio and video, and push content from web links.
    const MINI_HOST = 'mini-host';

    // Available for meetings only. The principal does not have participant, presenter or host permission to attend the
    // meeting. If a user is already attending a live meeting, the user is not removed from the meeting until the
    // session times out.
    const REMOVE = 'remove';

    // Available for SCOs other than meetings. The principal can publish or update the SCO. The publish permission
    // includes view and allows the principal to view reports related to the SCO. On a folder, publish does not allow
    // the principal to create new subfolders or set permissions.
    const PUBLISH = 'publish';

    // Available for SCOs other than meetings or courses. The principal can view, delete, move, edit, or set permissions
    // on the SCO. On a folder, the principal can create subfolders or view reports on folder content.
    const MANAGE = 'manage';

    // Available for SCOs other than meetings. The principal cannot view, access, or manage the SCO.
    const DENIED = 'denied';
} 