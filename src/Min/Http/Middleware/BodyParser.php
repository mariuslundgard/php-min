<?php

namespace Min\Http\Middleware;

use Min\AbstractLayer;
use Min\AbstractMessage;
use Exception;

class BodyParser extends AbstractLayer
{
	public function process(AbstractMessage $req)
	{
		$req->data = $_POST;

		$isJsonRequest = isset($req->headers['Content-Type']) && $req->headers['Content-Type'] === 'application/json';

		$data = json_decode($req->body, true);

		if ($isJsonRequest && !is_array($data) && in_array($req->method, array('POST', 'UPDATE'))) {
			throw new Exception('The provided payload is not valid JSON');
		} else {
			if ($isJsonRequest || is_array($data) && count($req->data) === 0) {
				$req->data = $data;
			}
		}

		return $this->next->process($req);
	}
}
