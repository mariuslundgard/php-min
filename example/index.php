<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require __DIR__ . '/../vendor/autoload.php';

use Min\Http;

$config = array(
    'title' => 'Example App',
);

try {
    (new Http\Application(null, $config))

        // adds JSONP-support
        ->use('Min\Http\Middleware\JsonpRenderer')

        // middleware for parsing Accept-headers
        ->use('Min\Http\Middleware\AcceptParser')

        // middleware for parsing JSON payloads and form request data
        ->use('Min\Http\Middleware\BodyParser')

        // OPTIONS /
        ->options('/', function ($req, $res, $app) {
            $res->headers['Content-Type'] = 'application/json';
            $res->body['GET'] = array(
                'description' => 'View the dashboard',
            );
        })

        // GET /
        ->get('/', function ($req, $res) {
            $res->headers['Content-Type'] = 'application/json';
            $res->body['_links'] = array(
                'self' => array(
                    'href' => $req->basePath . '/',
                    'title' => 'Dashboard',
                ),
                'sign-in' => array(
                    'href' => $req->basePath . '/sign-in',
                    'title' => 'Sign In',
                ),
            );
        })

        ->get('/sign-in', function ($req, $res) {
            $res->headers['Content-Type'] = 'application/json';
            $res->body[] = '<form></form>';
        })

        // route: POST /sign-in
        ->post('/sign-in', function ($req, $res) {
            $res->headers['Content-Type'] = 'application/json';
            $res->body['message'] = 'Example of Min\Http\Middleware\BodyParser';
            $res->body['postData'] = $req->data;
        })

        ->get('/stream', function ($req, $res) {
            $res->headers['Content-Encoding'] = 'chunked';
            $res->headers['Transfer-Encoding'] = 'chunked';
            $res->headers['Content-Type'] = 'text/html';
            $res->headers['Vary'] = 'Accept-Encoding';
            // $res->headers['Connection'] = 'keep-alive';
            $res->send();

            $res->body[] = '<p>Hello</p>';
            $res->send();

            usleep(1000 * 1000); // wait for 1000 ms

            $res->body[] = '<p>World! time=' . float_microtime() . '</p>';
            $res->send();
        })

        ->process()
        ->end();
}

catch (Http\Error $err) {
    (new Http\Response(array(
            'status' => $err->getCode(),
            'headers' => array('Content-Type' => 'application/json'),
            'body' => array(
                'message' => $err->getMessage(),
            ),
        )))
        ->end();
}

catch (Exception $err) {
    (new Min\Http\Response(array(
            'status' => $err->getCode(),
            'headers' => array('Content-Type' => 'application/json'),
            'body' => array(
                'message' => $err->getMessage(),
                'trace' => $err->getTrace(),
            ),
        )))
        ->end();
}
