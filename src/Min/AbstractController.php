<?php

namespace Min;

abstract class AbstractController
{
    protected $app;

    public function __construct(AbstractLayer $app)
    {
        $this->app = $app;
    }
}
