<?php

namespace nadir2\core;

/**
 * This is a factory class for generating of View-objects (View, Layout).
 * @author Leonid Selikhov
 */
class ViewFactory
{

    /**
     * @ignore.
     */
    private function __construct()
    {
        // Nothing here ...
    }

    /**
     * The method creates a view object assosiated with the specific controller and
     * action in it. If controller name is empty it means a markup file determined
     * only with action name. It doesn't physically consist into the direcory named
     * as controller, it's in the root of the view directory.
     * @param string $actionName The action name.
     * @param string $ctrlName|null The controller name (as optional)
     * @return \nadir2\core\View|null It returns null if view file isn't readable.
     */
    public static function createView(
        string $actionName,
        ?string $ctrlName = null
    ): ?View {
        $viewsRoot = AppHelper::getInstance()->getComponentRoot('views');
        $addPath   = '';
        if (!empty($ctrlName)) {
            $addPath .= \DIRECTORY_SEPARATOR.strtolower($ctrlName);
        }
        $viewFile = $viewsRoot.$addPath.\DIRECTORY_SEPARATOR
            .strtolower($actionName).'.php';
        if (is_readable($viewFile)) {
            return new View($viewFile);
        }
        return null;
    }

    /**
     * It creates a layout object.
     * @param string $layoutName The layout name.
     * @param \nadir2\core\View $view The object of view.
     * @return \nadir2\core\Layout|null It returns null if layout file isn't
     * readable.
     */
    public static function createLayout(string $layoutName, View $view): ?Layout
    {
        $layoutsRoot = AppHelper::getInstance()->getComponentRoot('layouts');
        $layoutFile  = $layoutsRoot.\DIRECTORY_SEPARATOR
            .strtolower($layoutName).'.php';
        if (is_readable($layoutFile)) {
            return new Layout($layoutFile, $view);
        }
        return null;
    }

    /**
     * The method creates a snippet-object.
     * @param type $snptName The snippet name.
     * @return \nadir2\core\Snippet|null. It returns null if snippet file isn't
     * readable.
     */
    public static function createSnippet(string $snptName): ?Snippet
    {
        $snptRoot = AppHelper::getInstance()->getComponentRoot('snippets');
        $snptFile  = $snptRoot.\DIRECTORY_SEPARATOR
            .strtolower($snptName).'.php';
        if (is_readable($snptFile)) {
            return new Snippet($snptFile);
        }
        return null;
    }
}
