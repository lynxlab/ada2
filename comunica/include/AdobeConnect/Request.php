<?php
namespace AdobeConnect;

/**
 * Provides a well formed endpoint url for Adobe Connect's API.
 *
 * @author Gustavo Burgi <gustavoburgi@gmail.com>
 */
class Request
{
    /** @var string     Action to call */
    protected $action;

    /** @var string     Parameters to attach with the action */
    protected $params;

    /** @var string     Final URI to call */
    protected $uri;

    /** @var string     Final URL to call */
    protected $url;

    public function __construct($host, $action, $params = array())
    {
        $this->action = $action;
        $this->params = array_map(function ($param) {
            return urlencode($param);
        }, $params);
        $this->uri = sprintf('?%s', http_build_query(array_merge(array('action' => $action), $params), null, '&'));
        $this->url = sprintf('https://%s/api/xml%s', $host, $this->uri);
    }

    /** @return string */
    public function __toString()
    {
        return $this->url;
    }

    /** @return string */
    public function getAction()
    {
        return $this->action;
    }

    /** @return string */
    public function getParams()
    {
        return $this->params;
    }

    /** @return string */
    public function getUri()
    {
        return $this->uri;
    }

    /** @return string */
    public function getUrl()
    {
        return $this->url;
    }
} 