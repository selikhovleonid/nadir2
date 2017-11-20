<?php

namespace nadir2\core;

/**
 * The class provides the binding of call parameters of cli-script  with determinated
 * controller and action in it.
 * @author Leonid Selikhov
 */
class CliCtrlResolver extends AbstractCtrlResolver
{
    /** @var string The route of cli-script calling (the first passed param). */
    protected $requestRoute = null;

    /**
     * The object properties initialization.
     * @param string[] $args The array of passed to the script arguments.
     * @throws \nadir2\core\Exception It's throwen if it wasn't passed the route
     * as the first call param.
     */
    public function __construct(array $args)
    {
        parent::__construct();
        if (!isset($args[1])) {
            throw new Exception('Undefined route for the cli request. '
            ."The route wasn't passed as first param when the cli script was called.");
        }
        $this->requestRoute = (string) $args[1];
        unset($args[0], $args[1]);
        $this->actionArgs   = array_values($args);
    }

    /**
     *  {@inheritdoc}
     */
    protected function createCtrl(): AbstractCtrl
    {
        $componentsRootMap = AppHelper::getInstance()
            ->getConfig('componentsRootMap');
        if (!isset($componentsRootMap['controllers'])) {
            throw new Exception("The field 'componentsRootMap.controllers' must be "
            ."presented in the main configuration file.");
        }
        $ctrlNamespace = str_replace(
            \DIRECTORY_SEPARATOR,
            '\\',
            $componentsRootMap['controllers']
        );
        $ctrlFullName  = $ctrlNamespace.'\\'.$this->ctrlName;
        return new $ctrlFullName();
    }

    /**
     *  {@inheritdoc}
     */
    protected function tryAssignController(): void
    {
        if (isset($this->routeMap['cli'])) {
            foreach ($this->routeMap['cli'] as $route => $config) {
                if ($route === $this->requestRoute) {
                    AppHelper::getInstance()->setRouteConfig($config);
                    $this->ctrlName   = $config['ctrl'][0];
                    $this->actionName = $config['ctrl'][1];
                    break;
                }
            }
        }
    }

    /**
     * It executes the action of controller.
     * @throws \nadir2\core\Exception It's throwen if it was unable to assign
     * controller to the route path.
     */
    public function run(): void
    {
        $this->tryAssignController();
        if (!$this->isControllerAssigned()) {
            throw new Exception("It's unable to assign controller to this route path.");
        }
        $this->createCtrl()->{$this->actionName}($this->actionArgs);
    }
}
