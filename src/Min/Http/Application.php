<?php

namespace Min\Http;

use Min\AbstractLayer;
use Min\AbstractMessage;
use LogicException;
use ReflectionClass;

class Application extends AbstractLayer
{
    protected $routes;

    public function __construct(AbstractLayer $next = null, array $config = array())
    {
        parent::__construct($next, $config);
    }

    public function map($method, $path, $callback)
    {
        $this->routes[] = compact('method', 'path', 'callback');

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

    // not supported by nginx
    // (and probably shouldn't be used anyway)
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

            foreach ($this->routes as $route) {
                $params = $req->match($route);

                if ($params !== null) {
                    if (isset($params[0])) {
                        $params = array();
                    }

                    $req->params = $params;

                    if (is_string($route['callback'])) {
                        @list($controllerClass, $action) = explode('::', $route['callback']);

                        // make sure the controller class exists
                        if (!class_exists($controllerClass)) {
                            throw new LogicException('The class does not exist: ' . $controllerClass);
                        }

                        // create controller instance
                        $controllerRefl = new ReflectionClass($controllerClass);
                        $controllerInstance = $controllerRefl->newInstanceArgs(array($this));

                        // make sure the controller action exists
                        if (!$controllerRefl->hasMethod($action)) {
                            throw new LogicException('The controller does not have the method: ' . $action);
                        }

                        // call the controller action
                        call_user_func_array(array($controllerInstance, $action), array($req, $res));

                    } elseif (is_callable($route['callback'])) {

                        // call the callback action
                        call_user_func_array($route['callback'], array($req, $res));
                    } else {
                        throw new LogicException('The callback is not valid');
                    }

                    return $res;
                }
            }

            throw new Error('Not found: ' . $req->method . ' ' . $req->path, 404);
        }

        return parent::process($req);
    }
}
