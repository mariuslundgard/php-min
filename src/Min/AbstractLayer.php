<?php

namespace Min;

use LogicException;
use ReflectionClass;
use SplStack;

abstract class AbstractLayer
{
  protected $next;
  protected $config;
  protected $stack;
  protected $isResolving;

  public function __construct(AbstractLayer $next = null, array $config = array())
  {
    $this->next = $next;
    $this->config = $config;
    $this->stack = new SplStack();
    $this->isResolved = false;
  }

  // this class uses __call in order to use the protected word "use" as a method
  // so as to get closer to a syntax which is familiar to the Express-mindset.
  public function __call($method, array $args = array())
  {
    // push a layer to the stack
    if ($method === 'use') {
      $this->stack->push($args);

      return $this;
    }

    throw new LogicException(sprintf('Unknown method: AbstractLayer::%s', $method));
  }

  public function setNext(AbstractLayer $next)
  {
    $this->next = $next;

    return $this;
  }

  protected function resolve(AbstractMessage $message)
  {
    $this->stack->rewind();
    $next = $this;

    while ($this->stack->valid()) {
      $args = $this->stack->current();

      $layer = array_shift($args);

      if (is_a($layer, 'Min\AbstractLayer')) {
        $layer->setNext($next);
      } elseif (is_string($layer) && is_a($layer, 'Min\AbstractLayer', true)) {
        $reflClass = new ReflectionClass($layer);
        $layer = $reflClass->newInstanceArgs(array($this));
      } else {
        throw new LogicException('The provided layer is not an instance of Min\AbstractLayer');
      }

      $next = $layer;

      $this->stack->next();
    }

    return $next;
  }

  public function process(AbstractMessage $message)
  {
    if ($this->isResolving) {
      if ($this->next) {
        return $this->next->process($message);
      } else {
        return array();
      }
    }

    $this->isResolving = true;

    $res = $this->resolve($message)->process($message);

    $this->isResolving = false;

    return $res;
  }
}
