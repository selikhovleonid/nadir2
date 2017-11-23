<?php

namespace nadir2\core;

/**
 * The class provides the processing of page headers.
 * @author Leonid Selikhov
 */
class Headers implements RunnableInterface
{
    /** @var self The singleton object of current class. */
    private static $instance = null;

    /** @var string[] The stack of the page headers. */
    protected $headerList = [];

    /** @var boolean The flag is equal true when the page headers was set. */
    protected $isRan = false;

    /**
     * @ignore.
     */
    private function __construct()
    {
        // Nothing here
    }

    /**
     * It returns the singleton instance of current class.
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
     * It returns the human readable explanation of HTTP status code.
     * @param integer $code The status code.
     * @return string The description.
     * @throws \nadir2\core\Exception It's throwen if unknown HTTP code was passed.
     */
    public static function getHTTPExplanationByCode(int $code): string
    {
        switch ((int) $code) {
            case 100:
                return 'Continue';
            case 101:
                return 'Switching Protocols';
            case 200:
                return 'OK';
            case 201:
                return 'Created';
            case 202:
                return 'Accepted';
            case 203:
                return 'Non-Authoritative Information';
            case 204:
                return 'No Content';
            case 205:
                return 'Reset Content';
            case 206:
                return 'Partial Content';
            case 300:
                return 'Multiple Choices';
            case 301:
                return 'Moved Permanently';
            case 302:
                return 'Moved Temporarily';
            case 303:
                return 'See Other';
            case 304:
                return 'Not Modified';
            case 305:
                return 'Use Proxy';
            case 400:
                return 'Bad Request';
            case 401:
                return 'Unauthorized';
            case 402:
                return 'Payment Required';
            case 403:
                return 'Forbidden';
            case 404:
                return 'Not Found';
            case 405:
                return 'Method Not Allowed';
            case 406:
                return 'Not Acceptable';
            case 407:
                return 'Proxy Authentication Required';
            case 408:
                return 'Request Time-out';
            case 409:
                return 'Conflict';
            case 410:
                return 'Gone';
            case 411:
                return 'Length Required';
            case 412:
                return 'Precondition Failed';
            case 413:
                return 'Request Entity Too Large';
            case 414:
                return 'Request-URI Too Large';
            case 415:
                return 'Unsupported Media Type';
            case 429:
                return 'Too Many Requests';
            case 451:
                return 'Unavailable For Legal Reasons';
            case 500:
                return 'Internal Server Error';
            case 501:
                return 'Not Implemented';
            case 502:
                return 'Bad Gateway';
            case 503:
                return 'Service Unavailable';
            case 504:
                return 'Gateway Time-out';
            case 505:
                return 'HTTP Version Not Supported';
            default:
                throw new Exception('Unknown HTTP code');
        }
    }

    /**
     * It adds the header to the stack.
     * @param string $header The page header.
     * @return self.
     * @throws \nadir2\core\Exception It's throwen if the passed header has already
     * been added earlier.
     */
    public function add(string $header): self
    {
        foreach ($this->getAll() as $tmp) {
            if ($tmp === $header) {
                throw new Exception("The '{$header}' header has already been added.");
            }
        }
        $this->headerList[] = $header;
        return self::$instance;
    }

    /**
     * It adds the header to the stack by HTTP code.
     * @param integer $code The code.
     * @return self.
     */
    public function addByHttpCode(int $code): self
    {
        $serverProtocol = filter_input(
            \INPUT_SERVER,
            'SERVER_PROTOCOL',
            \FILTER_SANITIZE_STRING
        );
        $protocol = !empty($serverProtocol) ? $serverProtocol : 'HTTP/1.1';
        $sHeader   = "{$protocol} {$code} ".self::getHTTPExplanationByCode($code);
        return $this->add($sHeader);
    }

    /**
     * The method returns the stack of page headers.
     * @return string[].
     */
    public function getAll(): array
    {
        return $this->headerList;
    }

    /**
     * The method returns true if the the page headers were set.
     * @return boolean.
     */
    public function isRan(): bool
    {
        return $this->isRan;
    }

    /**
     * The main execution method. It sets all added headers into the page.
     * @return void.
     */
    public function run(): void
    {
        $this->isRan = true;
        foreach ($this->getAll() as $header) {
            header($header);
        }
    }
}
