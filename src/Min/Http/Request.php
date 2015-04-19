<?php

namespace Min\Http;

use Min\AbstractMessage;

// for nginx
if (!function_exists('getallheaders')) {
	function getallheaders() {
		$headers = '';

		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}

		return $headers;
	}
}

class Request extends AbstractMessage
{
	public function __construct(array $params = array())
	{
		parent::__construct(array_merge(array(
			'headers' => array(),
			'method' => 'GET',
			'path' => '/',
		), $params));
	}

	public function match(array $params)
	{
		$params = array_merge(array(
			'method' => 'GET',
			'path' => '/',
		), $params);

		if ($params['method'] !== $this->method) {
			return null;
		}

		return static::matchPathPattern($params['path'], $this->path);
	}

	protected static function compilePathPattern($pattern)
	{
		// convert all characters to safe characters
		$pattern = preg_quote($pattern, '~');

		// -> /name/:key<regex>
		$pattern = preg_replace_callback(
			'/\\\:([A-Za-z0-9\_]+)\\\<([^\/]+)\>/',
			function ($match) {
				return '(?P<' . $match[1] . '>' . stripslashes($match[2]) . ')';
			},
			$pattern
		);

		// -> /name/:key
		$pattern = preg_replace_callback(
			'/\\\:([A-Za-z0-9\_\.\-]+)/',
			function ($match) {
				return '(?P<' . $match[1] . '>[^/]+)';
			},
			$pattern
		);

		// -> /name/*key
		$pattern = preg_replace_callback(
			'/\\\\\*([^\/]+)/',
			function ($match) {
				return '(?P<' . $match[1] . '>.*)';
			},
			$pattern
		);

		// -> /name/*
		$pointer = 0;
		$pattern = preg_replace_callback(
			'/\\\\\*/',
			function ($match) use ($pointer) {
				return '(?P<__wildcard_' . $pointer . '>.*)';
			},
			$pattern
		);

		// add ~ delimeters
		return '~^' . $pattern . '$~';
	}

	protected static function matchPathPattern($pattern, $uri)
	{
		$pattern = static::compilePathPattern($pattern);

		// just to avoid PHP CS errors
		$matches = array();

		if (preg_match_all($pattern, $uri, $matches)) {
			$ret = array();

			// build named parameters
			foreach ($matches as $key => $val) {
				if (! is_numeric($key)) {
					if (substr($key, 0, 11) == '__wildcard_') {
						$ret[] = $val[0];
					} else {
						$ret[$key] = $val[0];
					}
				}
			}

			return $ret;
		}

		return null;
	}

	public static function createFromGlobals()
	{
		$uriInfo = parse_url($_SERVER['REQUEST_URI']);
		$documentRoot = $_SERVER['DOCUMENT_ROOT'];
		$scriptDirname = dirname($_SERVER['SCRIPT_FILENAME']);
		$basePath = substr($scriptDirname, strlen($documentRoot));

		$params = array(
			'headers' => getallheaders(),
			'method' => $_SERVER['REQUEST_METHOD'],
			'basePath' => $basePath,
			'path' => substr($uriInfo['path'], strlen($basePath)),
			'body' => file_get_contents('php://input'),
			'query' => $_GET,
			'time' => $_SERVER['REQUEST_TIME'],
			'timeFloat' => $_SERVER['REQUEST_TIME_FLOAT'],
			'remote' => array(
				'address' => $_SERVER['REMOTE_ADDR'],
				'port' => $_SERVER['REMOTE_PORT'],
			),
			'host' => array(
				'name' => $_SERVER['SERVER_NAME'],
				'address' => $_SERVER['SERVER_ADDR'],
				'port' => $_SERVER['SERVER_PORT'],
			),
		);

		return new Request($params);
	}
}
