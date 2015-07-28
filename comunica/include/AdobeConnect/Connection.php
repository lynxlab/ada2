<?php
namespace AdobeConnect;

/**
 * Provides a connection with an Adobe Connect's Host, log in the admin user, and provides the method to call actions.
 *
 * @author Gustavo Burgi <gustavoburgi@gmail.com>
 */
class Connection
{
    /** @var    Config      Connection Configuration */
    protected $config;

    /** @var    string      The value of the session cookie at AdobeConnect */
    protected $cookie;

    /** @var    bool        Is the connection established? */
    protected $connected = false;

    /** @var    bool        Is the user logged in? */
    protected $loggedIn = false;

    /**
     * @param   Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $action
     * @param array  $params
     *
     * @return \SimpleXMLElement
     *
     * @see doRequest
     */
    public function callAction($action, $params = array())
    {
        return $this->doRequest($action, $params)->getXmlResponse();
    }

    /**
     * Establish a connection to AdobeConnect's server
     */
    public function connect()
    {
        if (! $this->getCookie()) {
            $response = $this->doRequest('common-info');

            $this->setCookie($response->getXmlResponse()->common->cookie);
        }

        $this->connected = true;
    }

    public function login()
    {
        $this->checkIfIsConnected();

        $this->doRequest('login', array(
            'login' => $this->config->getUsername(),
            'password' => $this->config->getPassword(),
        ));

        $this->loggedIn = true;
    }

    /** @return Config */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return bool     true if is already connected
     *
     * @throws  \Exception      If is not connected yet
     */
    protected function checkIfIsConnected()
    {
        if (! $this->connected) {
            throw new \Exception(sprintf('First you have to establish a connection.'));
        }

        return true;
    }

    /**
     * @return bool     true if is already logged in
     *
     * @throws  \Exception      If is not logged in yet
     */
    protected function checkIfIsLoggedIn()
    {
        if (! $this->loggedIn) {
            throw new \Exception(sprintf('First you have to log in a admin user.'));
        }

        return true;
    }

    /**
     * Call to an action from AdobeConnect's Server
     *
     * @param string $action
     * @param array  $params
     *
     * @return Response
     */
    protected function doRequest($action, $params = array())
    {
        if ('common-info' != $action && 'login' != $action) {
            $this->checkIfIsConnected();
            $this->checkIfIsLoggedIn();
        }

        if ($this->cookie && ! isset($params['session'])) {
            $params['session'] = $this->getCookie();
        }

        return new Response(new Request($this->config->getHost(), $action, $params));
    }

    /**
     * @param string $cookie
     *
     * @return Connection
     */
    protected function setCookie($cookie)
    {
        $this->cookie = trim(base64_encode(mcrypt_encrypt(
            MCRYPT_RIJNDAEL_256, '7e2d20j23a21db9f', $cookie, MCRYPT_MODE_ECB, $this->config->getSecret()
        )));

        setcookie($this->config->getCookieName(), $this->cookie, (time() + 60 * 5), null, null, null, true);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCookie()
    {
        if (! $this->cookie && isset($_COOKIE[$this->config->getCookieName()])) {
            $this->cookie = $_COOKIE[$this->config->getCookieName()];
        }
        if ($this->cookie) {
            return trim(utf8_encode(mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256,
                '7e2d20j23a21db9f',
                base64_decode($this->cookie),
                MCRYPT_MODE_ECB,
                $this->config->getSecret()
            )));
        }

        return null;
    }
    
     /**
     * @return bool     true if is already logged in / false if is not logged in
     *
     * @throws  \Exception      If is not logged in yet
     */
    public function getLoggedIn()
    {
        if ($this->loggedIn) {
            return true;
        }

        return false;
    }
    
    public function unsetCookie() {
        unset($_COOKIE[$this->config->getCookieName()]);
        unset($this->cookie);
    }
    
    
}
