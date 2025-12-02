<?php

class App
{
    private Router $router;
    private array $config;

    public function __construct(array $config)
    {
        $this->config  = $config;
        $this->router  = new Router();
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run()
    {
        $this->router->dispatch();
    }
}
