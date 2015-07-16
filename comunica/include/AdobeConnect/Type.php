<?php
namespace AdobeConnect;

/**
 * Provides constants for Adobe Connect's Types.
 *
 * @link   http://help.adobe.com/en_US/connect/9.0/webservices/WS8d7bb3e8da6fb92f73b3823d121e63182fe-8000_SP1.html#WS5b3ccc516d4fbf351e63e3d11a171ddf77-7f9c_SP1
 *
 * A return element or attribute defining the type of a SCO or principal on the server.
 * The allowed values for type are different for SCOs and principals.
 *
 * @author Gustavo Burgi <gustavoburgi@gmail.com>
 */
class Type
{
    // ---------------------------------------------------------- SCO's Types --------------------------------------- //
    // A SCO can be content, a meeting, an event, a curriculum, a folder or tree, or other object on Adobe Connect.
    // Most SCOs can have any of the following values for type:

    // A viewable file uploaded to the server, for example, an FLV file, an HTML file, an image, a pod, and so on.
    const CONTENT = 'content';

    // A curriculum.
    const CURRICULUM = 'curriculum';

    // A event.
    const EVENT = 'event';

    // A folder on the server’s hard disk that contains content.
    const FOLDER = 'folder';

    // A reference to another SCO. These links are used by curriculums to link to other SCOs. When content is added to a
    // curriculum, a link is created from the curriculum to the content.
    const LINK = 'link';

    // An Adobe Connect meeting.
    const MEETING = 'meeting';

    // One occurrence of a recurring Adobe Connect meeting.
    const SESSION = 'session';

    // The root of a folder hierarchy. A tree’s root is treated as an independent hierarchy; you can’t determine the
    // parent folder of a tree from inside the tree.
    const TREE = 'tree';

    // --- Content objects returned by some actions (for example, report-bulk-objects) have the following type values:

    // meeting ...
    // curriculum ...

    // An archived copy of a live Adobe Connect meeting or presentation.
    const ARCHIVE = 'archive';

    // A piece of content uploaded as an attachment.
    const ATTACHMENT = 'attachment';

    // A piece of multimedia content created with Macromedia Authorware from Adobe.
    const AUTHORWARE = 'authorware';

    // A demo or movie authored in Adobe Captivate.
    const CAPTIVATE = 'captivate';

    // An external training that can be added to a curriculum.
    const EXTERNAL_EVENT = 'external-event';

    // A media file in the FLV file format.
    const FLV = 'flv';

    // An image, for example, in GIF or JPEG format.
    const IMAGE = 'image';

    // A presentation.
    const PRESENTATION = 'presentation';

    // A SWF file.
    const SWF = 'swf';

    // ---------------------------------------------------------- Principal's Types --------------------------------- //

    // The built-in group Administrators, for Adobe Connect server Administrators.
    const ADMINS = 'admins';

    // The built-in group Authors, for authors.
    const AUTHORS = 'authors';

    // The built-in group Training Managers, for training managers.
    const COURSE_ADMINS = 'course-admins';

    // The built-in group Event Managers, for anyone who can create an Adobe Connect meeting.
    const EVENT_ADMINS = 'event-admins';

    // The group of users invited to an event.
    const EVENT_GROUP = 'event-group';

    // All Adobe Connect users.
    const EVERYONE = 'everyone';

    // A group authenticated from an external network.
    const EXTERNAL_GROUP = 'external-group';

    // A user authenticated from an external network.
    const EXTERNAL_USER = 'external-user';

    // A group that a user or Administrator creates.
    const GROUP = 'group';

    // A non-registered user who enters an Adobe Connect meeting room.
    const GUEST = 'guest';

    // The built-in group learners, for users who take courses.
    const LEARNERS = 'learners';

    // The built-in group Meeting Hosts, for Adobe Connect meeting hosts.
    const LIVE_ADMINS = 'live-admins';

    // The built-in group Seminar Hosts, for seminar hosts.
    const SEMINAR_ADMINS = 'seminar-admins';

    // A registered user on the server.
    const USER = 'user';

    // ---------------------------------------------------------- Custom field types -------------------------------- //
    // When used with a custom field, type can have any of the following values.

    // A required custom field for the account.
    const REQUIRED = 'required';

    // An optional field that is displayed during self-registration.
    const OPTIONAL = 'optional';

    // An optional field that is hidden during self-registration.
    const OPTIONAL_NO_SELF_REG = 'optional-no-self-reg';
}