<?php

namespace nadir2\core;

/**
 * This is a view abstract class.
 * @author Leonid Selikhov
 */
abstract class AbstractView extends AbstractUserPropAccessor
{
    /** @var string The path to the file with view markup. */
    protected $filePath = '';

    /**
     * The constructor inits private object properties.
     * @param string $viewFilePath The path to the file with view markup.
     * @return self
     */
    public function __construct(string $viewFilePath)
    {
        $this->setFilePath($viewFilePath);
    }

    /**
     * This is method-accessor to the variable which contains the path to the file
     * with view markup.
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * It assosiates the object with view file.
     * @param string $viewFilePath The path to the file with view markup.
     * @return void
     * @throws \nadir2\core\Exception It throws if file isn't readable.
     */
    public function setFilePath(string $viewFilePath): void
    {
        if (!is_readable($viewFilePath)) {
            throw new Exception("The View file {$viewFilePath} isn't readable.");
        }
        $this->filePath = $viewFilePath;
    }

    /**
     * The method provides massive assignment user variables of the class.
     * @param array $data The users's variables of the class.
     * @return void
     */
    public function setVariables(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * It's an abstract method which renders the file of view.
     * @return void
     */
    abstract public function render(): void;
}
