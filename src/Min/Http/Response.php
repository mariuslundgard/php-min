<?php

namespace Min\Http;

use Min\AbstractMessage;

// For 4.3.0 <= PHP <= 5.4.0
if (!function_exists('http_response_code')) {
    function http_response_code($newcode = null) {
        static $code = 200;

        if ($newcode !== null) {
            header('X-PHP-Response-Code: '.$newcode, true, $newcode);

            if (!headers_sent()) {
                $code = $newcode;
            }
        }

        return $code;
    }
}

class Response extends AbstractMessage
{
    protected $headersSent;

    public function __construct(array $params = array())
    {
        parent::__construct(array_merge(array(
            'status' => 200,
            'headers' => array(),
            'body' => array(),
        ), $params));
    }

    protected function sendHeaders()
    {
        if (!$this->headersSent) {
            ob_start();

            // send http status
            if ($this->status !== 200) {
                http_response_code($this->status);
            }

            if (isset($this->headers['Transfer-Encoding']) && $this->headers['Transfer-Encoding'] === 'chunked') {
                header('Transfer-Encoding: chunked');
                ob_end_flush();
                flush();
                ob_start();
            }

            $setHeaders = false;

            // send headers
            foreach ($this->headers as $header => $value) {
                if (!in_array($header, array('Transfer-Encoding'))) {
                    header(sprintf('%s: %s', $header, '' . $value));
                    $setHeaders = true;
                }
            }

            if ($setHeaders) {
                ob_end_flush();
                flush();
                ob_start();
            }

            $this->headersSent = true;
        }
    }

    public function send($str = null)
    {
        $this->sendHeaders();

        if (!is_array($this->body)) {
            $this->body = array($this->body);
        }

        $this->body[] = $str;

        $chunk = implode('', $this->body);

        if (strlen($chunk)) {

            $this->body = array();

            echo dechex(strlen($chunk)) . "\r\n";
            echo $chunk;
            echo "\r\n";

            ob_end_flush();
            flush();
            ob_start();
        }

        return $this;
    }

    public function end()
    {
        $this->sendHeaders();

        // make sure the `body` property is an array
        if (!is_array($this->body)) {
            $this->body = array($this->body);
        }

        $isJsonType = isset($this->headers['Content-Type']) && in_array(
            $this->headers['Content-Type'],
            array('application/json', 'application/hal+json')
        );

        // render as JSON
        if ($isJsonType) {
            die(json_encode($this->body));
        }

        // anything else
        die(implode('', $this->body));
    }

    public static function createFromGlobals()
    {
        return new Response(array(
            'headers' => headers_list(),
        ));
    }
}
