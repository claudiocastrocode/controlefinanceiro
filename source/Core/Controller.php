<?php

namespace Source\Core;

use CoffeeCode\Router\Router;

/**
 * Controller
 */
abstract class Controller
{
    /** @var Router */
    protected $router;
    
    /**
     * __construct
     *
     * @param  mixed $router
     * @return void
     */
    public function __construct($router)
    {
        $this->router = $router;
    }
}
