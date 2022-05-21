<?php

namespace Source\Core;

use CoffeeCode\Router\Router;

abstract class Controller
{
    /** @var Router */
    protected $router;

    public function __construct($router)
    {
        $this->router = $router;
    }
}
