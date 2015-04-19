<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require __DIR__ . '/../vendor/autoload.php';

try {
    (new Min\Http\Application())

        // middleware for parsing JSON payloads and form request data
        ->use('Min\Http\Middleware\BodyParser')

        // route: GET /
        ->get('/', function ($req, $res) {
            $res->body[] = '<h1>Hello, world!</h1>';

            $res->body[] = '<form action="'.$req->basePath.'/sign-in" method="POST">';
            $res->body[] = '<input name="btn-sign-in" type="submit" value="Sign in">';
            $res->body[] = '</form>';
        })

        // route: POST /sign-in
        ->post('/sign-in', function ($req, $res) {
            $res->headers['Content-Type'] = 'application/json';
            $res->body['message'] = 'Example of Min\Http\Middleware\BodyParser';
            $res->body['postData'] = $req->data;
        })

        ->process()
        ->send();
}

catch (Exception $err) {
    (new Min\Http\Response(array(
            'status' => 400,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => array(
                'message' => $err->getMessage(),
                'trace' => $err->getTrace(),
            ),
        )))
        ->send();
}
