<?php

namespace Min\Http\Middleware;

use Min\AbstractLayer;
use Min\AbstractMessage;

class JsonpRenderer extends AbstractLayer
{
  public function process(AbstractMessage $req)
  {
    $res = $this->next->process($req);

    // check
    $isJsonType = isset($res->headers['Content-Type']) && in_array(
        $res->headers['Content-Type'],
        array('application/json', 'application/hal+json')
    );

    // rewrite headers and body
    if ($isJsonType && isset($req->query['callback'])) {
        $res->headers['Content-Type'] = 'application/javascript';
        $res->body = sprintf('%s(%s)', $req->query['callback'], json_encode($res->body));
    }

    return $res;
  }
}
