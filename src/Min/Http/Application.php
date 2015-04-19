<?php

namespace Min\Http;

use Min\AbstractLayer;
use Min\AbstractMessage;
use Exception;

class Application extends AbstractLayer
{
	public function __construct(AbstractLayer $next = null)
	{
		parent::__construct($next);
	}

	public function map($method, $path, $callback)
	{
		$this->listeners[] = compact('method', 'path', 'callback');

		return $this;
	}

	public function process(AbstractMessage $req = null)
	{
		if ($req === null) {
			$req = Request::createFromGlobals();
		}

		if ($this->isResolving) {
			$res = $this->next ? $this->next->process($req) : Response::createFromGlobals();

			foreach ($this->listeners as $route) {
				$params = $req->match($route);

				if ($params !== null) {
					if (isset($params[0])) {
						$params = array();
					}

					$req->params = $params;

					if ($route['callback']) {
						call_user_func_array($route['callback'], array($req, $res));
					}

					return $res;
				}
			}

			throw new Exception('No matching route: ' . $req->method . ' ' . $req->path);
		}

		return parent::process($req);
	}
}
