<?php

namespace Min\Http\Middleware;

use Min\AbstractLayer;
use Min\AbstractMessage;
use Min\Http\Error;

class AcceptParser extends AbstractLayer
{
    public function process(AbstractMessage $req)
    {
        if (isset($req->headers['Accept'])) {
            // parse `Accept`
            $acceptedContentType = isset($req->headers['Accept'])
                ? static::parse($req->headers['Accept'])
                : array('*/*' => 1);

            // parse `Accept-Encoding`
            $acceptedEncoding = isset($req->headers['Accept-Encoding'])
                ? static::parse($req->headers['Accept-Encoding'])
                : array('*' => 1);

            // parse `Accept-Language`
            $acceptedLanguage = isset($req->headers['Accept-Encoding'])
                ? static::parse($req->headers['Accept-Language'])
                : array('*' => 1);
        }

        $res = $this->next->process($req);

        $contentType = isset($res->headers['Content-Type']) ? $res->headers['Content-Type'] : 'text/html';

        if (!isset($acceptedContentType[$contentType]) && !isset($acceptedContentType['*/*'])) {
            throw new Error('The remote does not support the response mime type: ' . $contentType);
        }

        return $res;
    }

    /**
     * Parses the value of an Accept-style request header into a hash of
     * acceptable values and their respective quality factors (qvalues).
     */
    public static function parse($header)
    {
        $qValues = array();
        $items = str_trim_split($header, ',');

        foreach ($items as $item) {
            preg_match('/^([^\s,]+?)(?:\s*;\s*q\s*=\s*(\d+(?:\.\d+)?))?$/', $item, $matches);
            if ($matches) {
                $name = strtolower($matches[1]);
                $qValue = static::normalizeQValue(floatval(isset($matches[2]) ? $matches[2] : 1));
                $qValues[$name] = $qValue;
            } else {
                throw new Error('Invalid header value: ' . json_encode($item, true));
            }
        }

        return $qValues;
    }

    /**
     * Converts 1.0 and 0.0 qvalues to 1 and 0 respectively. Used to maintain
     * consistency across qvalue methods.
     */
    public static function normalizeQValue($qValue)
    {
        return (($qValue === 1 || $qValue === 0) && is_numeric($qValue)) ? intval($qValue) : $qValue;
    }
}
