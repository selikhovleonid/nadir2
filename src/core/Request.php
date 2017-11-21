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
        $this->rawBody    = @file_get_contents('php://input');
    }

    /**
     * The method returns the server parameter value by the key. It's wrapper over
     * the filter_var() function.
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
        // May be useful if FastCGI has strange side-effects with unexpected null
        // values when using INPUT_SERVER and INPUT_ENV with this function.
        //return isset($_SERVER[$name]) ? \filter_var($_SERVER[$name], $filter, $options)
        //    : null;
        return filter_input(\INPUT_SERVER, $name, $filter, $options);
    }

    /**
     * The method returns the raw body of the request as string, which was gotten
     * from the input stream.
     * @return string.
     */
    public function getRawBody(): string
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
        return isset($_REQUEST[$name]) ? \filter_var($_REQUEST[$name], $filter, $options)
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
    public function getHeaderByName(string $name): ?string
    {
        foreach ($this->getAllHeaders() as $name => $value) {
            if (strtolower($name) === strtolower($name)) {
                return $value;
            }
        }
        return null;
    }

    /**
     * The method returns the associated array of cookies.
     * @return array.
     */
    public function getCookies()
    {
        $mRes       = null;
        $mRawCookie = $this->getHeaderByName('Cookie');
        if (!is_null($mRawCookie)) {
            $aCookies = explode(';', $mRawCookie);
            foreach ($aCookies as $sCookie) {
                $aParts = explode('=', $sCookie);
                if (count($aParts) > 1) {
                    $mRes[trim($aParts[0])] = trim($aParts[1]);
                }
            }
        }
        return $mRes;
    }

    /**
     * It returns trhe URL path of the request.
     * @return string|null
     */
    public function getUrlPath(): ?string
    {
        $uri = $this->getServerParam('REQUEST_URI', \FILTER_SANITIZE_URL);
        if (!is_null($uri)) {
            return \explode('?', $uri)[0];
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
