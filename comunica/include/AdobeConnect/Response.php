<?php
namespace AdobeConnect;

/**
 * Make the call to Adobe Connect's API and check if it status is ok.
 *
 * @author Gustavo Burgi <gustavoburgi@gmail.com>
 */
class Response
{
    const STATUS_OK = 'ok';

    /** @var \SimpleXMLElement */
    protected $xmlResponse;

    public function __construct(Request $request)
    {
        $this->xmlResponse = CurlCall::call($request);

        if (! $this->isStatusOk()) {
            throw new \Exception(sprintf(
                'The AdobeConnect\'s Server is returning an invalid status code.
                Request: %s
                Response: %s',
                preg_replace('/password\=(.*)&/', 'password=***', $request->getUrl()),
                json_encode($this->xmlResponse)
            ));
        }
    }

    public function getXmlResponse()
    {
        return $this->xmlResponse;
    }

    public function isStatusOk()
    {
        return self::STATUS_OK == $this->xmlResponse->status->attributes()->code;
    }
}