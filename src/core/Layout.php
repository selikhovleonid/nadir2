<?php

namespace nadir2\core;

/**
 * This is the class of layout.
 * @property mixed $name The variable for passing custom data from the controller
 * to the layout file.
 * @author Leonid Selikhov
 */
class Layout extends AbstractCompositeView
{
    /** @var \nadir2\core\View The view object. */
    public $view = null;

    /**
     * It assigns the oblect of current class with the file of Layout and indirectly
     * (through the View object) with the file of view markup.
     * @param string $layoutFilePath The path to the file with the layout markup.
     * @param \nadir2\core\View $view The object of view.
     */
    public function __construct(string $layoutFilePath, View $view)
    {
        parent::__construct($layoutFilePath);
        $this->view = $view;
    }

    /**
     * The method returns the view object binded with the layout.
     * @return \nadir2\core\View|null
     */
    public function getView(): ?View
    {
        return $this->view;
    }

    /**
     * {@inheritdoc}
     */
    public function render(): void
    {
        include $this->filePath;
    }
}
