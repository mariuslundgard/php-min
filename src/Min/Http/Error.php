<?php

namespace Min\Http;

use Exception;

/**
 * HTTP Error
 * Must be in the range 4**–5**
 */
class Error extends Exception
{
    function __construct($message, $code = 400)
    {
        parent::__construct($message, $code);
    }
}
