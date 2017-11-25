<?php

namespace nadir2\core;

/**
 * This class provides the choosing of controller, passing the request parameters
 * in it and binding with corresponding layout and view.
 * @author Leonid Selikhov
 */
class WebCtrlResolver extends AbstractCtrlResolver
{
    /** @var \nadir2\core\Request Объект запроса. */
    protected $request = null;

    /**
     * It inits the request property.
     * @param \nadir2\core\Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
    }

    /**
     * It creates the controller object, assignes it with default view and layout
     * objects.
     * @return \nadir2\core\AWebController
     * @throws \nadir2\core\Exception It's thrown if the 'componentsRootMap.controllers'
     * field not presented in the main config.
     */
    protected function createCtrl(): AbstractCtrl
    {
        $view              = ViewFactory::createView(
            str_replace('action', '', $this->actionName),
            $this->ctrlName
        );
        $componentsRootMap = AppHelper::getInstance()->getConfig('componentsRootMap');
        if (!isset($componentsRootMap['controllers'])) {
            throw new Exception("The field 'componentsRootMap.controllers' must be "
                .'presented in the main configuration file.');
        }
        $ctrlNamespace = str_replace(
            \DIRECTORY_SEPARATOR,
            '\\',
            $componentsRootMap['controllers']
        );
        $ctrlFullName  = $ctrlNamespace.'\\'.$this->ctrlName;
        if (!is_null($view)) {
            $layoutName = AppHelper::getInstance()->getConfig('defaultLayout');
            if (!is_null($layoutName)) {
                $layout = ViewFactory::createLayout($layoutName, $view);
                $ctrl   = new $ctrlFullName($this->request, $layout);
            } else {
                $ctrl = new $ctrlFullName($this->request, $view);
            }
        } else {
            $ctrl = new $ctrlFullName($this->request);
        }
        return $ctrl;
    }

    /**
     *  {@inheritdoc}
     */
    protected function tryAssignController(): void
    {
        $method = strtolower($this->request->getMethod());
        if (isset($this->routeMap[$method])) {
            foreach ($this->routeMap[$method] as $route => $config) {
                $actionArgs = [];
                if (preg_match(
                    '#^'.$route.'/?$#u',
                    urldecode($this->request->getUrlPath()),
                    $actionArgs
                )) {
                    AppHelper::getInstance()->setRouteConfig($config);
                    $this->ctrlName   = $config['ctrl'][0];
                    $this->actionName = $config['ctrl'][1];
                    unset($actionArgs[0]);
                    $this->actionArgs = array_values($actionArgs);
                    break;
                }
            }
        }
    }

    /**
     * It runs the controller action on execution.
     * @throws \nadir2\core\Exception It's thrown if the attempt to assign controller
     * with current route path was failed.
     */
    public function run(): void
    {
        $this->tryAssignController();
        if (!$this->isControllerAssigned()) {
            throw new Exception("It's unable to assign controller to this route path.");
        }
        (new CtrlWrapper($this->createCtrl()))
            ->{$this->actionName}($this->actionArgs);
    }
}
