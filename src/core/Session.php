<?php

namespace nadir2\core;

/**
 * This is facade class for working with session.
 * @author Leonid Selikhov
 */
class Session implements ArrayCollectionInterface
{

    /**
     * @ignore.
     */
    public function __construct()
    {
        // Nothing here...
    }

    /**
     * It returns the current ident of session.
     * @return string The session id.
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * It checks if the session was started.
     * @return boolean
     */
    public function isStarted(): bool
    {
        return $this->getId() !== '';
    }

    /**
     * It sets the ident of current session.
     * @param string $sessionId
     * @return void
     */
    public function setId(string $sessionId): void
    {
        @session_id($sessionId);
    }

    /**
     * It returns the name of current session.
     * @return string
     */
    public function getName(): string
    {
        return session_name();
    }

    /**
     * The method sets the name of current session.
     * @param string $name By default it's PHPSESSID.
     * @return void
     * @throws Exception It's thrown if passed name consists digits only or is empty.
     */
    public function setName(string $name): void
    {
        if (!empty($name)) {
            if (!is_numeric($name)) {
                @session_name($name);
            } else {
                throw new Exception('The session name can\'t consist only of digits, '
                .'at least one letter must be presented.');
            }
        } else {
            throw new Exception('Empty session name value was passed.');
        }
    }

    /**
     * It inits the data of new session or continues the current session.
     * @param string $name The optional name of session, it has higher priority
     * than the $iSess parameter.
     * @param string $sessionId The optional session ident. It ignored if the $sSessName
     * parameter was passed.
     * @return string The id of current session.
     */
    public function start(?string $name = null, ?string $sessionId = null): string
    {
        if (!$this->isStarted()) {
            if (!is_null($name)) {
                $this->setName($name);
            }
            @session_start($sessionId);
        };
        return $this->getId();
    }

    /**
     * It commits the data of session and closes it.
     * @return void
     */
    public function commit(): void
    {
        if ($this->isStarted()) {
            session_commit();
        }
    }

    /**
     * It destroys the session data.
     * @return boolean|null The result of destruction.
     */
    public function destroy(): ?bool
    {
        if ($this->isStarted()) {
            @session_unset();
            return session_destroy();
        }
        return null;
    }

    /**
     * It complitly destroys session with cookie.
     * @return boolean|null The result.
     */
    public function destroyWithCookie(): ?bool
    {
        if ($this->isStarted()) {
            $this->destroy();
            return setcookie($this->getName(), '', time() - 1, '/');
        }
        return null;
    }

    /**
     * It adds the variable to the session.
     * @param string $key The name of variable.
     * @param mixed $value The value of it.
     * @return void.
     */
    public function add(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * It adds the array of variables (the key-value pairs) to the session.
     * @param array $pairs
     * @return void
     */
    public function addAll(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            $this->add($key, $value);
        }
    }

    /**
     * It returns TRUE if the variable with passed key contains into the session.
     * @param string $key
     * @return boolean
     */
    public function contains(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * It returns true if the session is empty.
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return empty($_SESSION);
    }

    /**
     * It returns the variable of session value by passed key.
     * @param string $key
     * @return mixed|null.
     */
    public function get(string $key)
    {
        return $this->contains($key) ? $_SESSION[$key] : null;
    }

    /**
     * It returns the list of session variables.
     * @return string[]
     */
    public function getKeys(): array
    {
        return array_keys($_SESSION);
    }

    /**
     * It returns all session variables as associative array.
     * @return mixed[]
     */
    public function getAll(): array
    {
        $res = [];
        foreach ($this->getKeys() as $key) {
            $res[$key] = $this->get($key);
        }
        return $res;
    }

    /**
     * It removes the variable of session by passed key.
     * @param string $key
     * @return mixed|null The removed variable value.
     */
    public function remove(string $key)
    {
        if ($this->contains($key)) {
            $res = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $res;
        } else {
            return null;
        }
    }

    /**
     * It clears the session by removing all stored variables.
     * @return mixed[] The array of removed vars.
     */
    public function removeAll(): array
    {
        $res = [];
        foreach ($this->getKeys() as $key) {
            $res[$key] = $this->remove($key);
        }
        return $res;
    }

    /**
     * It returns the count of variables contained into the session.
     * @return integer
     */
    public function size(): int
    {
        return count($this->getKeys());
    }
}
