<?php

namespace nadir2\core;

/**
 * The interface describes the Front Controller pattern functionality.
 * @author Leonid Selikhov
 */
interface FrontControllerInterface
{

    /**
     * It's the main executable method. It runs the application.
     * @return void
     */
    public function run(): void;

    /**
     * The method inits settings at initial appliction startup.
     * @return void.
     */
    public function init(): void;

    /**
     * It handles the Request object passing it to the ControllerResolver object.
     * @return void.
     */
    public function handleRequest(): void;
}
