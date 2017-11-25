<?php

namespace nadir2\core;

/**
 * This trait provides auto generation of accessors and mutators methods (get-,
 * set- and isSet-) to the public properties of the extended classes.
 * @author Leonid Selikhov
 */
trait PropertyMethodsTrait
{

    /**
     * It's a reflection method, which checks a availability and accessibility
     * of the properties of the child-class.
     * @param string $propName The property name.
     * @return boolean
     */
    private function isPropAccessible(string $propName): bool
    {
        $res        = false;
        $reflection = new \ReflectionClass(get_class($this));
        if ($reflection->hasProperty($propName)
            && $reflection->getProperty($propName)->isPublic()) {
            $res = true;
        }
        unset($reflection);
        return $res;
    }

    /**
     * This is interceptor method, which catches the calls of undeclared methods of
     * the class. If the name of the invoked method matches the setProperty, getProperty
     * or isPropertySet pattern and the target class has corresponding public
     * property, then it calls needed accessor as if it was declared directly in
     * the child-class. In another case it throws exception.
     * @param string $methodName The name of the method.
     * @param mixed[] $args The array of passed args.
     * @return mixed|boolean The result is mixed for the getters and setters, is
     * boolean for isSets.
     * @throws \nadir2\core\Exception
     */
    public function __call(string $methodName, array $args)
    {
        // Lambda-function
        $throwException = function (string $className, string $propName): void {
            throw new Exception('Undefined or non public property '
            ."{$className}::\${$propName} was called.");
        };

        $matches = [];
        if (preg_match('#^get(\w+)$#', $methodName, $matches)) {
            $propName = lcfirst($matches[1]);
            if ($this->isPropAccessible($propName)) {
                return $this->$propName;
            } else {
                $throwException(get_class($this), $propName);
            }
        } elseif (preg_match('#^set(\w+)$#', $methodName, $matches)) {
            $propName = lcfirst($matches[1]);
            if ($this->isPropAccessible($propName)) {
                $this->$propName = $args[0];
                return $args[0];
            } else {
                $throwException(get_class($this), $propName);
            }
        } elseif (preg_match('#^is(\w+)Set$#', $methodName, $matches)) {
            $propName = lcfirst($matches[1]);
            if ($this->isPropAccessible($propName)) {
                return !is_null($this->$propName);
            } else {
                $throwException(get_class($this), $propName);
            }
        } else {
            $className = get_class($this);
            throw new Exception("Call the undefined method {$className}::{$methodName}");
        }
    }
}
