<?php
namespace AdobeConnect;

/**
 * ApiClient's extra functionalities.
 *
 * @see    ApiClient
 *
 * @author Gustavo Burgi <gustavoburgi@gmail.com>
 */
class ExtraApiClient extends ApiClient
{
    /**
     * @param string $email
     * @param bool   $just_id TRUE if just want the principal-id of the user
     *
     * @return  \SimpleXMLElement|int|null
     */
    public function getUserByLoginEmail($email, $just_id = false)
    {
        $user = current($this->principalList(array(
            'filter-type' => 'user',
            'filter-login' => $email,
        )));

        if (! $user) {
            return null;
        }

        if ($just_id) {
            return (int) $user->attributes()->{'principal-id'};
        }

        return $user;
    }

    /**
     * Update an user by email, or create it if not exist.
     *
     * @param array $data
     *
     * @return int  User's principal-id
     *
     * @throws \Exception   If is not present the login data.
     */
    public function createOrUpdateUserByLoginEmail($data)
    {
        if (! isset($data['login'])) {
            throw new \Exception(sprintf('Have to specify $data[login].'));
        }

        $data['principal-id'] = $this->getUserByLoginEmail($data['login'], true);

        return $this->createOrUpdateUser($data);
    }

    /**
     * Create or Update an user according if principal-id is present on the data
     *
     * @param array $data
     *
     * @return int  User's principal-id
     */
    public function createOrUpdateUser($data)
    {
        if (! isset($data['login']) && isset($data['email'])) {
            $data['login'] = $data['email'];
        } elseif (! isset($data['email']) && isset($data['login'])) {
            $data['email'] = $data['login'];
        }

        if (isset($data['principal-id']) && $data['principal-id']) {
            return $this->updateUser($data);
        } else {
            return $this->createUser($data);
        }
    }

    /**
     * @param array $data The new user's data
     *
     * @return int  principal-id of the new user
     */
    public function createUser($data)
    {
        $data['type'] = 'user';
        $data['has-children'] = false;
        $principal = $this->principalUpdate($data);

        return (int) $principal->attributes()->{'principal-id'};
    }

    /**
     * @param array $data the data to update
     *
     * @return int  principal-id of the user updated
     */
    public function updateUser($data)
    {
        $this->principalUpdate($data);

        return (int) $data['principal-id'];
    }
} 