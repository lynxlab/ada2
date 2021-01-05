<?php

/**
 * @package     collabora-access-list module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2020, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\CollaboraACL;

if (!defined('FilesTable')) define('FilesTable', AMACollaboraACLDataHandler::PREFIX . 'files');
if (!defined('FilesUtenteRel')) define('FilesUtenteRel', AMACollaboraACLDataHandler::PREFIX . 'files_utente');

class FileACL extends CollaboraACLBase
{
    /**
     * table name for this class
     *
     * @var string
     */
    const table = FilesTable;

    /**
     * table name for groups/utente relation
     */
    const utenteRelTable = FilesUtenteRel;

    protected $id;
    protected $filepath;
    protected $id_corso;
    protected $id_istanza;
    protected $id_nodo;
    protected $id_owner;

    /**
     * array of int, users allowred to access the file
     *
     * @var array
     */
    protected $allowedUsers = [];

    public function __construct($data = array())
    {
        parent::__construct($data);
    }

    public static function loadJoined()
    {
        return [
            'allowedUsers' => [
                'reltable' => self::utenteRelTable,
                'key' => [
                    'name' => 'file_id',
                    'getter' => self::GETTERPREFIX . 'Id'
                ],
                'extkey' => 'utente_id',
                'relproperties' => [ 'permissions' ],
            ],
        ];
    }

    public static function isAllowed(array $filesACL = [], $userId = null, $filepath = null, $permissions = CollaboraACLActions::READ_FILE) {
        if (!is_null($userId) && !is_null($filepath) && count($filesACL)>0) {
            $aclCount = count($filesACL);
            $found = false;
            for ($i=0; !$found && $i<$aclCount; $i++) {
              $found = ($filesACL[$i]->getFilepath() == $filepath);
            }
            // if $filepath is not in the passed file access list, then it's a public file and everyone is allowed
            if (!$found) {
                return true;
            } else {
                // $i-1 is the found filesACL index
                --$i;
                if ($filesACL[$i]->getId_owner() == $userId) {
                    return true;
                }
                foreach($filesACL[$i]->getAllowedUsers() as $allowedAr) {
                    if ($allowedAr['utente_id'] == $userId) {
                        return ($allowedAr['permissions'] & $permissions);
                    }
                }
                return false;
            }
        }
        return true; // is a public file
    }

    public static function getObjectById(array $filesACL, $id) {
        $retval = array_filter($filesACL, function($acl) use ($id){
            return $acl->getId() == $id;
        });

        if (is_array($retval) && count($retval)==1) {
            $retval = reset($retval);
        } else {
            $retval = null;
        }
        return $retval;
    }

    public static function getIdFromFileName(array $filesACL = [], $filepath = '') {
        $fileACL = array_filter($filesACL, function($el) use ($filepath) {
            $elPath = str_replace(ROOT_DIR. DIRECTORY_SEPARATOR, '', $filepath);
            return $el->getFilepath() == $elPath;
        });
        if (is_array($fileACL) && count($fileACL)==1) {
            $fileACL = reset($fileACL);
            if ($fileACL instanceof self) {
                return $fileACL->getId();
            }
        }
        return null;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of filepath
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * Set the value of filepath
     *
     * @return  self
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * Get the value of id_nodo
     */
    public function getId_nodo()
    {
        return $this->id_nodo;
    }

    /**
     * Set the value of id_nodo
     *
     * @return  self
     */
    public function setId_nodo($id_nodo)
    {
        $this->id_nodo = $id_nodo;

        return $this;
    }

    /**
     * Get array of \ADAUser objects
     *
     * @return  array
     */
    public function getAllowedUsers()
    {
        return $this->allowedUsers;
    }

    /**
     * Set array of \ADAUser objects
     *
     * @param  array  $allowedUsers  array of \ADAUser objects
     *
     * @return  self
     */
    public function setAllowedUsers(array $allowedUsers)
    {
        $this->allowedUsers = $allowedUsers;

        return $this;
    }

    public function addAllowedUser($allowedUser)
    {
        $this->allowedUsers[] = $allowedUser;

        return $this;
    }

    /**
     * Get the value of id_corso
     */
    public function getId_corso()
    {
        return $this->id_corso;
    }

    /**
     * Set the value of id_corso
     *
     * @return  self
     */
    public function setId_corso($id_corso)
    {
        $this->id_corso = $id_corso;

        return $this;
    }

    /**
     * Get the value of id_istanza
     */
    public function getId_istanza()
    {
        return $this->id_istanza;
    }

    /**
     * Set the value of id_istanza
     *
     * @return  self
     */
    public function setId_istanza($id_istanza)
    {
        $this->id_istanza = $id_istanza;

        return $this;
    }

    /**
     * Get the value of id_owner
     */
    public function getId_owner()
    {
        return $this->id_owner;
    }

    /**
     * Set the value of id_owner
     *
     * @return  self
     */
    public function setId_owner($id_owner)
    {
        $this->id_owner = $id_owner;

        return $this;
    }
}
