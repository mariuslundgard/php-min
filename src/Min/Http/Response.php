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
  public function __construct(array $params = array())
  {
    parent::__construct(array_merge(array(
      'status' => 200,
      'headers' => array(),
      'body' => array(),
    ), $params));
  }

  public function send()
  {
    // send http status
    if ($this->status !== 200) {
      http_response_code($this->status);
    }

    // send headers
    foreach ($this->headers as $header => $value) {
      header(sprintf('%s: %s', $header, '' . $value));
    }

    // make sure the `body` property is an array
    if (!is_array($this->body)) {
      $this->body = array($this->body);
    }

    // render as JSON
    if (isset($this->headers['Content-Type']) && $this->headers['Content-Type'] === 'application/json') {
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
