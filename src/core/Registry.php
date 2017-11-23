<?php

namespace nadir2\core;

/**
 * The singleton instance of current class is the Registry - the global storage
 * of custom variables, which lifetime is equal to the life cycle time of the
 * scrypt.
 * @author Leonid Selikhov
 */
class Registry
{
    /** @var self This is singleton object of current class. */
    private static $instance = null;

    /** @var mixed[] The user's variable storage. */
    protected $store = [];

    /**
     * It returns the singleton-instance of current class.
     * @return self.
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * It adds the user variable to the storage.
     * @param string $key The variable name.
     * @param mixed $value The variable value.
     * @return self.
     */
    public function set(string $key, $value): self
    {
        $this->store[$key] = $value;
        return self::$instance;
    }

    /**
     * It returns the variable value getted by the name from the storage.
     * @param string $key The variable name.
     * @return mixed|null.
     */
    public function get(string $key = '')
    {
        if (empty($key)) {
            return $this->store;
        } else {
            return $this->store[$key] ?? null;
        }
    }

    /**
     * It returns the whole registry store.
     * @return array
     */
    public function getAll(): array
    {
        return $this->get();
    }
}
