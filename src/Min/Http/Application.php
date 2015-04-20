<?php

namespace Min\Http;

use Min\AbstractLayer;
use Min\AbstractMessage;

class Application extends AbstractLayer
{
  public function __construct(AbstractLayer $next = null, array $config = array())
  {
    parent::__construct($next, $config);
  }

  public function map($method, $path, $callback)
  {
    $this->listeners[] = compact('method', 'path', 'callback');

    return $this;
  }

  public function get($path, $callback)
  {
    return $this->map('GET', $path, $callback);
  }

  public function post($path, $callback)
  {
    return $this->map('POST', $path, $callback);
  }

  public function put($path, $callback)
  {
    return $this->map('PUT', $path, $callback);
  }

  public function patch($path, $callback)
  {
    return $this->map('PATCH', $path, $callback);
  }

  public function delete($path, $callback)
  {
    return $this->map('DELETE', $path, $callback);
  }

  public function copy($path, $callback)
  {
    return $this->map('COPY', $path, $callback);
  }

  // not supported by nginx:
  public function head($path, $callback)
  {
    return $this->map('HEAD', $path, $callback);
  }

  public function options($path, $callback)
  {
    return $this->map('OPTIONS', $path, $callback);
  }

  public function link($path, $callback)
  {
    return $this->map('LINK', $path, $callback);
  }

  public function unlink($path, $callback)
  {
    return $this->map('UNLINK', $path, $callback);
  }

  public function purge($path, $callback)
  {
    return $this->map('PURGE', $path, $callback);
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

      throw new Error('Not found: ' . $req->method . ' ' . $req->path, 404);
    }

    return parent::process($req);
  }
}
