<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require __DIR__ . '/../vendor/autoload.php';

$app = new Min\Http\Application();

// parse JSON and form data request payloads
$app->use('Min\Http\Middleware\BodyParser');

// route: GET /
$app->map('GET', '/', function ($req, $res) {
	$res->body['message'] = 'Hello, world!';
});

// route: POST /
$app->map('POST', '/test', function ($req, $res) {
	$res->body['message'] = 'Hello, world!';
	$res->body['receivedData'] = $req->data;
});

try {
	$res = $app->process();
	$res->headers['Content-Type'] = 'application/json';
	$res->send();
}

catch (Exception $err) {
	$res = new Min\Http\Response(array(
		'status' => 400,
		'headers' => array('Content-Type' => 'application/json'),
		'body' => array(
			'message' => $err->getMessage(),
			'trace' => $err->getTrace(),
		),
	));

	$res->send();
}
