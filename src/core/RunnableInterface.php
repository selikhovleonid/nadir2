<?php

namespace nadir2\core;

/**
 * This class describes the running process functionality.
 * @author Leonid Selikhov.
 */
interface RunnableInterface
{

    /**
     * It executes the process.
     * @return void
     */
    public function run(): void;
}