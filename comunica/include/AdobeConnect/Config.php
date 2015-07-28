<?php
namespace AdobeConnect;

/**
 * Provides a configuration object for connect with an Adobe Connect account.
 *
 * @author Gustavo Burgi <gustavoburgi@gmail.com>
 */
class Config
{
    /** @var    string      AdobeConnect URL */
    protected $host;

    /** @var    string      AdobeConnect username */
    protected $username;

    /** @var    string      AdobeConnect password */
    protected $password;

    /** @var    string      Cookie name for AdobeConnect's session */
    protected $cookieName;

    /** @var    string      Secret value to encrypt the cookie */
    protected $secret;

    /**
     * @param   string $host
     * @param   string $username
     * @param   string $password
     * @param   string $cookieName
     * @param   string $secret
     */
    public function __construct($host, $username, $password, $cookieName = null, $secret = null)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->cookieName = $cookieName ? $cookieName : 'AC';
        $this->secret = $secret ? $secret : 'gseac';
    }

    /**
     * @param string $cookieName
     *
     * @return Config
     */
    public function setCookieName($cookieName)
    {
        $this->cookieName = $cookieName;

        return $this;
    }

    /**
     * @return string
     */
    public function getCookieName()
    {
        return $this->cookieName;
    }

    /**
     * @param string $host
     *
     * @return Config
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $password
     *
     * @return Config
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $secret
     *
     * @return Config
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $username
     *
     * @return Config
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
} 