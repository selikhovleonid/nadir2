<?php

namespace nadir2\core;

/**
 * The class provides the centralized access to the parameters of input request.
 * @author Leonid Selikhov
 */
class Request
{
    /** @var array It contains the raw request body. */
    private $rawBody = null;

    /**
     * The constructor inits the private properties of the object.
     * @return self.
     */
    public function __construct()
    {
        $this->rawBody = @file_get_contents('php://input');
    }

    /**
     * The method returns the server parameter value by the key. It's wrapper over
     * the filter_input() function.
     * @param string $name Name of a variable to get.
     * @param int $filter
     * @param mixed $options Associative array of options or bitwise disjunction
     * of flags. If filter accepts options, flags can be provided in "flags"
     * field of array.
     * @return mixed Value of the requested variable on success, false if the filter
     * fails, or null if the variable is not set.
     */
    public function getServerParam(
        string $name,
        int $filter = \FILTER_DEFAULT,
        $options = null
    ) {
        // Can be useful if FastCGI has strange side-effects with unexpected null
        // values when using INPUT_SERVER and INPUT_ENV with this function.
        //return isset($_SERVER[$name]) ? filter_var($_SERVER[$name], $filter, $options)
        //    : null;
        return filter_input(\INPUT_SERVER, $name, $filter, $options);
    }

    /**
     * The method returns the raw body of the request as string, which was gotten
     * from the input stream.
     * @return string|null.
     */
    public function getRawBody(): ?string
    {
        return $this->rawBody;
    }

    /**
     * The method returns the parameter value of request by the passed key. It's
     * wrapper over the filter_var() function.
     * @param string $name Name of a variable to get.
     * @param int $filter
     * @param mixed $options Associative array of options or bitwise disjunction
     * of flags. If filter accepts options, flags can be provided in "flags"
     * field of array.
     * @return mixed Value of the requested variable on success, false if the filter
     * fails, or null if the variable is not set.
     */
    public function getParam(
        string $name,
        int $filter = \FILTER_DEFAULT,
        $options = null
    ) {
        // Can be useful when INPUT_REQUEST is implemented for the filter_input()
        // function.
        //return filter_input(\INPUT_REQUEST, $name, $filter, $options);
        return isset($_REQUEST[$name]) ? filter_var($_REQUEST[$name], $filter, $options)
            : null;
    }

    /**
     * It returns the request HTTP-method.
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->getServerParam('REQUEST_METHOD', \FILTER_SANITIZE_STRING);
    }

    /**
     * It returns the array of request headers.
     * @return string[]
     */
    public function getAllHeaders()
    {
        return getallheaders();
    }

    /**
     * It returns the header by passed name. The search is case-insensitive.
     * @param string $name
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        $name = strtolower($name);
        foreach ($this->getAllHeaders() as $key => $value) {
            if (strtolower($key) === $name) {
                return $value;
            }
        }
        return null;
    }

    /**
     * It returns cookie value by name if it exists and matches predefined filter.
     * @param string $name Cookie name.
     * @return string|false|null
     */
    public function getCookie(string $name)
    {
        return filter_input(\INPUT_COOKIE, $name, \FILTER_SANITIZE_STRING);
    }

    /**
     * The method returns the associated array of cookies.
     * @return mixed[]|null
     */
    public function getAllCookies(): ?array
    {
        return filter_input_array(\INPUT_COOKIE, array_combine(
            array_keys($_COOKIE),
            array_fill(0, count($_COOKIE), \FILTER_SANITIZE_STRING)
        ));
    }

    /**
     * It returns the URL path of the request.
     * @return string|null
     */
    public function getUrlPath(): ?string
    {
        $uri = $this->getServerParam('REQUEST_URI', \FILTER_SANITIZE_URL);
        if (!is_null($uri)) {
            return parse_url($uri, \PHP_URL_PATH);
        }
        return null;
    }

    /**
     * It checks if the request is an ajax request.
     * @return boolean.
     */
    public function isAjax(): bool
    {
        $param = $this->getServerParam('HTTP_X_REQUESTED_WITH', \FILTER_SANITIZE_STRING);
        return !is_null($param) && strtolower($param) === 'xmlhttprequest';
    }
}
