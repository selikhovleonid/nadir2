<?php

namespace nadir2\core;

/**
 * This is a class of controller wrapper, an instance of which perfoms delegated
 * function - it calls target controller after the succesful auth or calls onFail
 * auth class functionality in other case.
 * @author Leonid Selikhov
 */
class CtrlWrapper
{
    /** @var \nadir2\core\AbstractWebCtrl The target controller object. */
    protected $ctrl = null;

    /**
     * The constructor assigns the object-wrapper with controller object.
     * @param \nadir2\core\AbstractWebCtrl $ctrl The controller object.
     */
    public function __construct(AbstractWebCtrl $ctrl)
    {
        $this->ctrl = $ctrl;
    }

    /**
     * The method calls user auth checking, on successful complition of which
     * it invokes the target controller and the onFail method of Auth class in
     * other case.
     * @param string $actionName The action name of target controller.
     * @param mixed[] $actionArgs The action parameters.
     */
    protected function processAuth(string $actionName, array $actionArgs): void
    {
        // Lambda
        $callAction = function (string $actionName, array $actionArgs) {
            if (empty($actionArgs)) {
                $this->ctrl->{$actionName}();
            } else {
                (new \ReflectionMethod($this->ctrl, $actionName))
                    ->invokeArgs($this->ctrl, $actionArgs);
            }
        };

        if (class_exists('\extensions\core\Auth')) {
            $auth = new \extensions\core\Auth($this->ctrl->getRequest());
            $auth->run();
            if ($auth->isValid()) {
                $callAction($actionName, $actionArgs);
            } else {
                $auth->onFail();
            }
        } else {
            $callAction($actionName, $actionArgs);
        }
    }

    /**
     * This is the method-interseptor of method calling of the target controller.
     * @param string $actionName The action name of target controller.
     * @param mixed[] $actionArgs The action parameters.
     */
    public function __call(string $actionName, array $actionArgs): void
    {
        $this->processAuth($actionName, $actionArgs);
    }
}
