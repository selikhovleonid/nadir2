<?php

namespace nadir2\core;

/**
 * This is the class of abstract web-controller. Despite the fact that no one method
 * declared as abstract, the 'abstract' modifier is set a specially to exclude the
 * possibility of creating an instance of the class.
 * @author Leonid Selikhov
 */
abstract class AbstractWebCtrl extends AbstractCtrl
{
    /** @var \nadir2\core\Request The request object. */
    protected $request = null;

    /** @var \nadir2\core\View The view object. */
    protected $view = null;

    /** @var \nadir2\core\Layout The layout object. */
    protected $layout = null;

    /**
     * The constructor assigns object with request object and possibly with
     * the view object (full or partial).
     * @param \nadir2\core\Request $request The request object.
     * @param \nadir2\core\AbstractView|null $view The view object.
     */
    public function __construct(Request $request, AbstractView $view = null)
    {
        $this->request = $request;
        if ($view instanceof View) {
            $this->view = $view;
        } elseif ($view instanceof Layout) {
            $this->layout = $view;
            $this->view   = $this->layout->view;
        }
    }

    /**
     * It returns the object of assigned request.
     * @return \nadir2\core\Request|null.
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * It returns the object of associated view.
     * @return \nadir2\core\View|null.
     */
    protected function getView(): ?View
    {
        return $this->view;
    }

    /**
     * It's used for binding the controller with a view (both with the default
     * and with corresponding another controller).
     * @param string $ctrlName The controller name.
     * @param string $actionName The action name (without prefix action).
     * @return void.
     */
    protected function setView(string $ctrlName, string $actionName): void
    {
        $this->view = ViewFactory::createView($actionName, $ctrlName);
        if (!is_null($this->layout)) {
            $this->layout->view = $this->view;
        }
    }

    /**
     * It returns the object of associated layout.
     * @return \nadir2\core\Layout|null.
     */
    protected function getLayout(): ?Layout
    {
        return $this->layout;
    }

    /**
     * It assosiates the controller with the layout.
     * @param string $layoutName The layout name.
     * @return void
     * @throws \nadir2\core\Exception It's thrown if the layout doesn't contain
     * a view.
     */
    protected function setLayout(string $layoutName): void
    {
        if (is_null($this->view)) {
            throw new Exception("It's unable to set Layout without View.");
        }
        $this->layout = ViewFactory::createLayout($layoutName, $this->view);
    }

    /**
     * It renders the page both full (layout with view) and partial (view only).
     * @return void
     * @throws \nadir2\core\Exception It's thrown if the view is empty.
     */
    protected function render(): void
    {
        if (!is_null($this->layout)) {
            $this->layout->render();
        } elseif (!is_null($this->view)) {
            $this->partialRender();
        } else {
            throw new Exception("It's unable to render with empty View.");
        }
    }

    /**
     * The method provides partial rendering (view without layout).
     * @return void
     */
    protected function partialRender(): void
    {
        $this->view->render();
    }

    /**
     * It renders the page with JSON-formatted data.
     * @param mixed $data The input data.
     * @return void
     */
    protected function renderJson($data): void
    {
        echo json_encode($data, \JSON_UNESCAPED_UNICODE);
    }

    /**
     * The method redirects to the URL, which passed as param. The HTTP-code is
     * 302 as default. The method unconditional completes the script execution,
     * the code after it will not be executed.
     * @param string $uri
     * @param bool $isPermanent The flag of permanent redirect.
     * @return void
     */
    protected function redirect(string $uri, bool $isPermanent = false): void
    {
        $nCode = $isPermanent ? 301 : 302;
        Headers::getInstance()
            ->addByHttpCode($nCode)
            ->add('Location: '.$uri)
            ->run();
        exit;
    }
}
