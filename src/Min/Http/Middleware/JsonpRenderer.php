<?php

namespace Min\Http\Middleware;

use Min\AbstractLayer;
use Min\AbstractMessage;

class JsonpRenderer extends AbstractLayer
{
  public function process(AbstractMessage $req)
  {
    $res = $this->next->process($req);

    if (isset($res->headers['Content-Type']) &&
        $res->headers['Content-Type'] === 'application/json' &&
        isset($req->query['callback'])
    ) {
        // rewrite headers and body
        $res->headers['Content-Type'] = 'application/javascript';
        $res->body = sprintf('%s(%s)', $req->query['callback'], json_encode($res->body));
    }

    return $res;
  }
}
