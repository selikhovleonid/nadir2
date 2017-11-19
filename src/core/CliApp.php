<?php

namespace nadir2\core;

/**
 * This is a class of cli-application (command line interface application).
 * @author Leonid Selikhov
 */
class CliApp extends AbstractApp
{
    /** @var self The singleton object of current class. */
    protected static $instance = null;

    /**
     * @ignore.
     */
    protected function __construct()
    {
        // Nothing here...
    }

    /**
     * It processes the call parameters of cli-script and passed them to the
     * CliCtrlResolver object.
     * @global string[] $argv The array of passed to cli-scrypt args.
     * @throws \core\Exception It's throwen if it was attempting to call cli-scprit
     * out the command line interface.
     */
    public function handleRequest(): void
    {
        global $argv;
        if (!is_array($argv) || empty($argv)) {
            throw new Exception('Invalid value of the cli args array was given.');
        }
        (new CliCtrlResolver($argv))->run();
    }
}
