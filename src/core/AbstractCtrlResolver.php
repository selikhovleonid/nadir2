<?php

namespace nadir2\core;

/**
 * This is the abstract class, which associates a controller with the request
 * parameters.
 * @author Leonid Selikhov
 */
abstract class AbstractCtrlResolver implements RunnableInterface
{
    /** @var array[] The route map. */
    protected $routeMap = [];

    /** @var string The controller name. */
    protected $ctrlName = '';

    /** @var string The action name. */
    protected $actionName = '';

    /** @var mixed[] These are additional parameters, which were passed to the action. */
    protected $actionArgs = [];

    /**
     * The constructor inits the properties of the route map object.
     * @return self.
     */
    public function __construct()
    {
        $this->routeMap = AppHelper::getInstance()->getConfig('routeMap');
    }

    /**
     * The method contains a controller object creating functionality.
     * @return \nadir2\core\AbstractCtrl
     */
    abstract protected function createCtrl(): AbstractCtrl;

    /**
     * The method tries to assign the request rote to the concrete controller
     * action according the regexp map.
     * @return void
     */
    abstract protected function tryAssignController(): void;

    /**
     * The method checks if the request route was assigned to the concrete
     * controller.
     * @return boolean
     */
    protected function isControllerAssigned(): bool
    {
        return !empty($this->ctrlName) && !empty($this->actionName);
    }

    /**
     * It starts the controller action on execution.
     */
    abstract public function run(): void;
}