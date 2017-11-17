<?php

namespace nadir2\core;

/**
 * It's the web-application class. It specifies an abstract application.
 * @author Leonid Selikhov
 */
class WebApp extends AbstractApp
{
    /** @var self The singleton object of the current class. */
    protected static $instance = null;

    /**
     * @ignore.
     */
    protected function __construct()
    {
        // nothing here...
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(): void
    {
        (new WebCtrlResolver(new Request()))->run();
    }
}