<?php
namespace AdobeConnect;

/**
 * Provides a PHP Client to interact with the Adobe Connect's API
 *
 * @link   http://help.adobe.com/en_US/connect/9.0/webservices/WS26a970dc1da1c212717c4d5b12183254583-8000_SP1.html
 *
 * @author Gustavo Burgi <gustavoburgi@gmail.com>
 */


class ApiClient
{
    /** @var Connection */
    protected $connection;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->connection = new Connection($config);
        $this->connection->connect();
        $this->connection->login();
    }

    /**
     * @param string $action
     * @param array  $params
     *
     * @return \SimpleXMLElement
     *
     * @see Connection::callAction
     */
    public function call($action, $params = array())
    {
        return $this->connection->callAction($action, $params);
    }

    // ---------------------------------------------------------- Matching to Adobe Connect's API actions ----------- //

    /**
     * Basic information about the current user and Adobe Connect account
     *
     * @return \SimpleXMLElement
     */
    public function commonInfo()
    {
        $response = $this->call('common-info');

        return current($response->xpath('/results/common'));
    }

    // ------------------------------------ User Accounts --- //

    /**
     * Provides a list of the accounts a user belongs to.
     * The user-accounts action is only used when a user belongs to more than one account on the server and uses the
     * same login ID and password for each. In that case, a user’s login is likely to fail with a status message of
     * too-much-data. This action is useful when you want to retrieve a list of a user’s accounts and give the user a
     * choice of which account to log in to.
     *
     * @param string $login    The user's login name, which may be the user’s e-mail address.
     * @param string $password The user’s password.
     *
     * @return array    An array with the list of user's accounts.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function userAccounts($login, $password)
    {
        $response = $this->call('user-accounts', array(
            'login' => $login,
            'password' => $password,
        ));

        return $response->xpath('/results/users/user');
    }

    /**
     * Changes a user’s password. A password can be changed in either of these cases:
     *  - By an Administrator logged in to the account, with or without the user’s old password
     *  - By any Adobe Connect Server user, with the user’s principal-id number, login name, and old password
     * An Administrator can create rules for valid passwords on the server. These rules might include, for example,
     * the number and types of characters a password must contain. If a user submits a new password that does not adhere
     * to the rules, Adobe Connect would throw an error showing that the new password is invalid.
     *
     * @param int    $user_id      The ID of the user.
     * @param string $password     The new password.
     * @param string $password_old The user's current password. Required for regular users, but not for Administrator users.
     *
     * @return bool
     */
    public function userUpdatePwd($user_id, $password, $password_old = null)
    {
        $params = array(
            'user-id' => $user_id,
            'password' => $password,
            'password-verify' => $password,
        );

        if ($password_old) {
            $params['password-old'] = $password_old;
        }

        $this->call('user-update-pwd', $params);

        return true;
    }

    public function login($login, $password)
    {
        $params = array(
            'login' => $login,
            'password' => $password,
        );

        $this->call('login', $params);
    }

    // ------------------------------------ Principals --- //

    /**
     * Provides a complete list of users and groups, including primary groups.
     *
     * @param array $filters
     *
     * @return array    An array with the list of users who matched the filters.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function principalList(array $filters = array())
    {
        $response = $this->call('principal-list', $filters);

        return $response->xpath('/results/principal-list/principal');
    }

    /**
     * Creates a principal (a user or group) or updates a standard field for a principal.
     * The principal is created or updated in the same account as the user making the call.
     *
     * @param array $principal_data
     *
     * @return \SimpleXMLElement
     */
    public function principalUpdate(array $principal_data)
    {
        $response = $this->call('principal-update', $principal_data);

        return current($response->xpath('/results/principal'));
    }

    /**
     * Provides information about one principal, either a user or a group.
     *
     * @param int $principal_id The ID of a user or group you want information about.
     *                          You can get the ID by calling principal-list.
     *
     * @return array    An array with the principal information. Each element is a \SimpleXMLElement
     *                  - contact: Information about the contact person for a principal.
     *                             If the principal is a user, usually the same as information in principal.
     *                  - manager: Information describing a user's manager, who is also a principal.
     *                  - preferences: Information about the principal’s preferences.
     *                  - principal: Information describing the principal.
     */
    public function principalInfo($principal_id)
    {
        $response = $this->call('principal-info', array(
            'principal-id' => $principal_id,
        ));

        return array(
            'contact' => current($response->xpath('/results/contact')),
            'manager' => current($response->xpath('/results/manager')),
            'preferences' => current($response->xpath('/results/preferences')),
            'principal' => current($response->xpath('/results/principal')),
        );
    }

    /**
     * Removes one or more principals, either users or groups. To delete, you must have Administrator privilege.
     *
     * @param int $principal_id The ID of a user or group you want to delete.
     *
     * @return bool
     */
    public function principalDelete($principal_id)
    {
        $this->call('principals-delete', array(
            'principal-id' => $principal_id,
        ));

        return true;
    }

    // ------------------------------------ Permissions --- //

    /**
     * Returns the list of principals (users or groups) who have permissions to act on a SCO, principal, or account.
     * To call permissions-info, you must specify an acl-id, which is the ID of a SCO, principal, or account
     * that can be acted on. ACL stands for access control list, and means the list of entities who have permission.
     * With just an acl-id, permissions-info returns a list of all principals in the account, showing each principal's
     * permission on the principal or SCO specified in the acl-id.
     *
     * @param int   $acl_id       The ID of a SCO, account, or principal that a principal has permission to act on.
     *                            The acl-id is a sco-id, principal-id, or account-id in other calls.
     * @param int   $principal_id The ID of a principal who has a permission (even if denied) to act on an object.
     *                            The acl-id is a sco-id, principal-id, or account-id in other calls.
     * @param array $filters
     *
     * @return array    An array with two elements
     *          - array principals: each element is a \SimpleXMLElement
     *          - \SimpleXMLElement permission: information about the permission one principal has on a SCO, account,
     *            or principal. If empty, no permission is defined.
     */
    public function permissionsInfo($acl_id, $principal_id = null, array $filters = array())
    {
        $params = array_merge(array('acl-id' => $acl_id,), $filters);
        if ($principal_id) {
            $params['principal-id'] = $principal_id;
        }

        $response = $this->call('permissions-info', $params);

        return array(
            'principals' => $response->xpath('/results/permissions/principal'),
            'permission' => current($response->xpath('/results/permission')),
        );
    }

    /**
     * Resets all permissions any principals have on a SCO to the permissions of its parent SCO.
     * If the parent has no permissions set, the child SCO will also have no permissions.
     *
     * @param int $acl_id The ID of a SCO that has permissions you want to reset.
     *
     * @return bool
     */
    public function permissionsReset($acl_id)
    {
        $this->call('permissions-reset', array('acl-id' => $acl_id,));

        return true;
    }

    /**
     * Updates the permissions a principal has to access a SCO, using a trio of principal-id, acl-id, and permission-id.
     *
     * @TODO: To update permissions for multiple principals or objects, specify multiple trios. You can update more than
     *      200 permissions in a single call to permissions-update.
     *
     * Call permissions-update to give a user access to a Adobe Connect meeting, course, curriculum, or other SCO.
     * For example, you can use permissions-update to:
     *  - Invite a user to a meeting as participant, presenter, or host (with a permission-id of view, mini-host, or host, respectively)
     *  - Remove a user's participant, presenter, or host access to a meeting (with a permission-id of remove)
     *  - Enroll users in courses (with a permission-id of view)
     * If you use multiple trios and any of them have invalid information (for example, an incorrect acl-id or principal-id),
     * permissions-update returns an ok status, the correct trios execute, and the invalid ones do not.
     *
     * @param int    $acl_id        The ID of a SCO (a sco-id) for which you want to update permissions.
     * @param int    $principal_id  The ID of a principal, either a user or group.
     * @param string $permission_id The permission to assign. (See \AdobeConnect\Permission)
     *
     * @return bool
     */
    public function permissionsUpdate($acl_id, $principal_id, $permission_id)
    {
        $this->call('permissions-update', array(
            'acl-id' => $acl_id,
            'principal-id' => $principal_id,
            'permission-id' => $permission_id,
        ));

        return true;
    }

    // ------------------------------------ Meetings --- //

    /**
     * Returns a list of Adobe Connect meetings that are currently in progress,
     * including the number of minutes the meeting has been active.
     *
     * @return array    And array with all active meetings (meetings with more than one user in room right now).
     *                  Each element is a \SimpleXMLElement object.
     */
    public function reportActiveMeetings()
    {
        $response = $this->call('report-active-meetings');

        return $response->xpath('/results/report-active-meetings/sco');
    }

    /**
     * Returns a list of users who attended a Adobe Connect meeting. The data is returned in row elements, one for each
     * person who attended. If the meeting hasn't started or had no attendees, the response contains no rows.
     * The response does not include meeting hosts or users who were invited but did not attend.
     *
     * @param int   $sco_id  The sco-id of a meeting
     * @param array $filters A filter to reduce the volume of the response.
     *
     * @return array    An array with the list of users who attended a AdobeConnect meeting.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function reportMeetingAttendance($sco_id, $filters = array())
    {
        $response = $this->call('report-meeting-attendance', array_merge(
            array('sco-id' => $sco_id,),
            $filters
        ));

        return $response->xpath('/results/report-meeting-attendance/row');
    }

    /**
     * Returns the maximum number of users in Adobe Connect meetings concurrently in the last $length days,
     * and the number of times the maximum has been reached.
     * The maximum is the peak number of users in any meetings at a single moment, whether one meeting,
     * multiple concurrent meetings, or multiple overlapping meetings.
     *
     * @param int $length The number of days in the time period to check for concurrent meeting usage.
     *                    Use a value greater than 30. The default value is 30.
     *
     * @return \SimpleXMLElement
     */
    public function reportMeetingConcurrentUsers($length = 30)
    {
        $response = $this->call('report-meeting-concurrent-users', array(
            'length' => $length,
        ));

        return current($response->xpath('/results/report-meeting-concurrent-users'));
    }

    /**
     * Provides information about all the sessions of a Adobe Connect meeting. A session is created when a participant
     * enters an empty meeting. As more participants join the meeting, they join the session. The session ends when all
     * attendees leave the meeting. When a new participant enters the now-empty meeting, a new session starts.
     * For example, a recurring weekly meeting has a session each week when the meeting is held.
     * You can call report-meeting-sessions on past meetings, active meetings, or future meetings, but future meetings
     * are not likely to have sessions.
     *
     * @param int   $sco_id  The ID of a meeting for which you want session information.
     * @param array $filters A filter to reduce the volume of the response.
     *
     * @return array    An array with the list of sessions of the meeting.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function reportMeetingSessions($sco_id, array $filters = array())
    {
        $response = $this->call('report-meeting-sessions', array_merge(
            array('sco-id' => $sco_id,),
            $filters
        ));

        return $response->xpath('/results/report-meeting-sessions/row');
    }

    /**
     * Returns summary information about a specific Adobe Connect meeting. The results indicate how many users were
     * invited, how many invited participants and guests attended, and other information about the meeting.
     *
     * @param int $sco_id The ID of a meeting for which you want session information.
     *
     * @return \SimpleXMLElement
     */
    public function reportMeetingSummary($sco_id)
    {
        $response = $this->call('report-meeting-summary', array(
            'sco-id' => $sco_id,
        ));

        return current($response->xpath('/results/report-meeting-summary'));
    }

    /**
     * Provides information about all Adobe Connect meetings for which the user is a host, invited participant,
     * or registered guest. The meeting can be scheduled in the past, present, or future.
     *
     * @param array $filters A filter to reduce the volume of the response.
     *
     * @return array    An array with the list of sessions of the current user.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function reportMyMeetings(array $filters = array())
    {
        $response = $this->call('report-my-meetings', $filters);

        return $response->xpath('/results/my-meetings/meeting');
    }

    // ------------------------------------ Quotas --- //

    /**
     * Returns information about the quotas that apply to your Adobe Connect license or Adobe Connect hosted account.
     * Adobe Connect enforces various quotas, for example, the number of concurrent users in training, the number of
     * downloads, the number of authors, and so on.
     *
     * @return array    An array with the list of quotas for de Adobe Connect account.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function reportQuotas()
    {
        $response = $this->call('report-quotas');

        return $response->xpath('/results/report-quotas/quota');
    }

    // ------------------------------------ SCOs --- //

    /**
     * Indicates how many times, and how recently, a SCO was viewed.
     *
     * @param int $sco_id The unique ID of a SCO to check for views.
     *
     * @return \SimpleXMLElement
     */
    public function reportScoViews($sco_id)
    {
        $response = $this->call('report-sco-views', array(
            'sco-id' => $sco_id,
        ));

        return current($response->xpath('/results/report-sco-views'));
    }

    /**
     * Returns information about a SCO at a specified URL path. The URL path is the unique identifier after the domain
     * name in the URL to the SCO. For example, if you have a meeting with the custom URL http://example.com/teammeeting,
     * the URL path is /teammeeting. If you pass the full URL path, Connect returns the status code "no data".
     *
     * @param string $sco_url_path The unique identifier after the domain name in the URL to the SCO.
     *
     * @return array    An array with two \SimpleXMLElemnt [owner-principal, sco]
     */
    public function scoByUrl($sco_url_path)
    {
        $response = $this->call('sco-by-url', array(
            'url-path' => $sco_url_path,
        ));

        return array(
            'owner-principal' => current($response->xpath('/results/owner-principal')),
            'sco' => current($response->xpath('/results/sco')),
        );
    }

    /**
     * Returns a list of SCOs within another SCO. The enclosing SCO can be a folder, meeting, or curriculum.
     *
     * @param int   $sco_id  The unique ID of a folder for which you want to list contents.
     *                       You can get the sco-id by calling sco-shortcuts.
     * @param array $filters A filter to reduce the volume of the response.
     *
     * @return array    An array with a list of SCOs within another SCO.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function scoContents($sco_id, array $filters = array())
    {
        $response = $this->call('sco-contents', array_merge(array(
            'sco-id' => $sco_id,
        ), $filters));

        return $response->xpath('/results/scos/sco');
    }

    /**
     * Lists all of the SCOs in a folder, including the contents of subfolders, and any type of enclosing SCO.
     *
     * @param int   $sco_id  The unique ID of a folder for which you want to list contents.
     * @param array $filters A filter to reduce the volume of the response.
     *
     * @return array    An array with a list of SCOs within another SCO.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function scoExpandedContents($sco_id, array $filters = array())
    {
        $response = $this->call('sco-expanded-contents', array_merge(array(
            'sco-id' => $sco_id,
        ), $filters));

        return $response->xpath('/results/expanded-scos/sco');
    }

    /**
     * Provides information about a SCO on Adobe Connect. The object can have any valid SCO type.
     *
     * @param int $sco_id The unique ID of a SCO on the server.
     *
     * @return array    An array with two elements
     *          - \SimpleXMLElement sco,
     *          - array source-sco each element is a \SimpleXMLElement
     */
    public function scoInfo($sco_id)
    {
        $response = $this->call('sco-info', array(
            'sco-id' => $sco_id,
        ));

        return array(
            'sco' => current($response->xpath('/results/sco')),
            'source-sco' => $response->xpath('/results/source-sco/source-sco'),
        );
    }

    /**
     * Moves a SCO from one folder to another.
     *
     * @param int $sco_id    The unique ID of the SCO to move.
     * @param int $folder_id The ID of the destination folder.
     *
     * @return bool
     */
    public function scoMove($sco_id, $folder_id)
    {
        $this->call('sco-move', array(
            'sco-id' => $sco_id,
            'folder-id' => $folder_id,
        ));

        return true;
    }

    /**
     * Describes the folder hierarchy that contains a SCO.
     *
     * @param int $sco_id The unique ID of the SCO to move.
     *
     * @return array    An array with the folder hierarchy that contains the SCO.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function scoNav($sco_id)
    {
        $response = $this->call('sco-nav', array(
            'sco-id' => $sco_id,
        ));

        return $response->xpath('/results/sco-nav/sco');
    }

    /**
     * Provides a list of all SCOs that have content matching the search text.
     *
     * @param string $query A string to search for. To use any of these special characters in the query string,
     *                      escape them with a backslash before the character: + - && || ! ( ) { } [ ] ^ " ~ * ? : \
     *                      The query string is not case-sensitive and allows wildcard characters
     *                      * and ? at the end of the query string.
     *
     * @return array    An array with a list of SCOs that have content matching the query.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function scoSearch($query)
    {
        $response = $this->call('sco-search', array(
            'query' => $query,
        ));

        return $response->xpath('/results/sco-search-info/sco');
    }

    /**
     * Provides a list of all SCOs matching the search text within the specified field.
     * This action allows you to search for objects in the database based on the SCO’s name,
     * description, or author, or all three of those fields.
     *
     * @param string $query   The term to search for within the specified field. The query is case-insensitive.
     * @param string $field   The field to search. Accepts four possible values: name (default), description, author, or allfields.
     * @param array  $filters A filter to reduce the volume of the response.
     *
     * @return array    An array with a list of SCOs that have content matching the query.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function scoSearchByField($query, $field = 'name', array $filters = array())
    {
        $response = $this->call('sco-search-by-field', array_merge(array(
            'query' => $query,
            'field' => $field,
        )), $filters);

        return $response->xpath('/results/sco-search-by-field-info/sco');
    }

    /**
     * Provides information about the folders relevant to the current user. These include a folder for the user’s
     * current meetings, a folder for the user’s content, as well as folders above them in the navigation hierarchy.
     *
     * @return array    An array with a list of SCOs.
     *                  Each element is a \SimpleXMLElement object.
     */
    public function scoShortcuts()
    {
        $response = $this->call('sco-shortcuts');

        return $response->xpath('/results/shortcuts/sco');
    }

    /**
     * Creates metadata for a SCO, or updates existing metadata describing a SCO. Call sco-update to create metadata
     * only for SCOs that represent content, including meetings. You also need to upload content files with either
     * sco-upload or Connect Central.
     * You must provide a folder-id or a sco-id, but not both. If you pass a folder-id, sco-update creates a new SCO and
     * returns a sco-id. If the SCO already exists and you pass a sco-id, sco-update updates the metadata describing the SCO.
     * After you create a new SCO with sco-update, call permissions-update to specify which users and groups can access it.
     *
     * @param array $sco_data
     *
     * @return \SimpleXMLElement
     */
    public function scoUpdate(array $sco_data)
    {
        $response = $this->call('sco-update', $sco_data);

        return current($response->xpath('/results/sco'));
    }

    /**
     * Deletes a SCO. If the sco-id you specify is for a folder, all the contents of the specified folder are deleted.
     *
     * @param int $sco_id The unique ID of a folder for which you want to delete.
     *
     * @return bool
     */
    public function scoDelete($sco_id)
    {
        $this->call('sco-delete', array(
            'sco-id' => $sco_id,
        ));

        return true;
    }

    /**
     * Get logged in.
     *
     * @return bool
     */
    public function getLoggedIn()
    {
        return $this->connection->getLoggedIn();
    }
   
    public function unsetCookie() {
        return $this->connection->unsetCookie();
    }
    
    public function getConnection() {
        return $this->connection;
    }
            
    
}