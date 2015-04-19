<?php

namespace Min;

use LogicException;

abstract class AbstractMessage
{
  public function __construct(array $props = array())
  {
    $this->setProperties($props);
  }

  public function setProperties(array $props)
  {
    foreach ($props as $property => $value) {
      $this->__set($property, $value);
    }
  }

  public function toArray()
  {
    return get_object_vars($this);
  }

  public function __get($property)
  {
    throw new LogicException(sprintf(
      'Call to undefined message property %s',
      $property
    ));
  }

  public function __set($property, $value)
  {
    if (stripos($property, '_') === 0 && isset($this->$property)) {
      throw new LogicException(sprintf(
        '%s is a read-only message property',
        $property
      ));
    }

    $this->$property = $value;

    return true;
  }
}
