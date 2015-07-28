<?php
namespace AdobeConnect;

/**
 * Provides a method to execute a curl call and turn the xml response to an \SimpleXMLElement object.
 *
 * @author Gustavo Burgi <gustavoburgi@gmail.com>
 */
class CurlCall
{
    /**
     * @param string $url URL of the API endpoint
     *
     * @return  \SimpleXMLElement
     *
     * @throws \Exception   if the endpoint return an empty result
     */
    public static function call($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 25);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        

        $result = curl_exec($ch);
        
        if (! $result) {
            throw new \Exception(sprintf('The endpoint "%s" is not returning anything.', $url));
        }

        return simplexml_load_string($result);
    }
} 