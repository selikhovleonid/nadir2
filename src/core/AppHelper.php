<?php

namespace nadir2\core;

/**
 * The application helper class, which provides config data loading and general
 * access to them. It's realized as Singleton.
 * @author Leonid Selikhov.
 */
class AppHelper extends AbstractAutoAccessors implements RunnableInterface
{
    /** @var string The path to the application root. */
    public $appRoot = null;

    /** @var string The path to the configuration file. */
    public $configFile = null;

    /** @var string The route configuration. */
    public $routeConfig = null;

    /** @var self The singleton-object of current class. */
    private static $instance = null;

    /** @var mixed[] The configuration data set. */
    private $configSet = [];

    /** @var string The site basic URL. */
    private $siteBaseUrl = '';

    /**
     * The closed class constructor. It determines  the site basic URL.
     * @return self
     */
    private function __construct()
    {
        $this->siteBaseUrl = self::getBaseUrl();
    }

    /**
     * It retrurns the current class instance.
     * @return self
     */
    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * This is mutator-method. It sets the path to the root of application.
     * @param string $appRoot The path to the application root.
     * @return self
     */
    public function setAppRoot(string $appRoot): self
    {
        $this->appRoot = $appRoot;
        return self::$instance;
    }

    /**
     * It sets the path to the main configuration of application file relative
     * to its root.
     * @param string $filePath
     * @return self
     */
    public function setConfigFile(string $filePath): self
    {
        $this->configFile = $filePath;
        return self::$instance;
    }

    /**
     * It loads main file of configuration and checks for validity.
     * @return void
     * @throws Exception It's throwen if some errors with the main configuration
     * file occurred.
     */
    public function run(): void
    {
        if (!$this->isAppRootSet()) {
            throw new Exception("The application root wasn't defined.");
        }
        if (!$this->isConfigFileSet()) {
            throw new Exception("The main config file wasn't defined.");
        }
        $configPath = $this->getAppRoot().$this->getConfigFile();
        if (!is_readable($configPath)) {
            throw new Exception("It's unable to load ".$configPath
                .'as main config file.');
        }
        $config = require_once $configPath;
        if (!is_array($config)) {
            throw new Exception('The main config must be an array.');
        }
        $this->configSet = $config;
    }

    /**
     * It returns the config value by passed name or all config set if it wasn't
     * specified.
     * @param string $sName The config name.
     * @return mixed|null
     */
    public function getConfig(string $sName = '')
    {
        if (empty($sName)) {
            return $this->configSet;
        } elseif (isset($this->configSet[$sName])) {
            return $this->configSet[$sName];
        } else {
            return null;
        }
    }

    /**
     * It determines the basic site URL.
     * @return string|null
     */
    private static function getBaseUrl(): ?string
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            $protocol = !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])
                == 'on' ? 'https' : 'http';
            return $protocol.'://'.$_SERVER['SERVER_NAME'];
        }
        return null;
    }

    /**
     * This is method-accessor to basic site URL.
     * @return string|null
     */
    public function getSiteBaseUrl(): ?string
    {
        return $this->siteBaseUrl;
    }

    /**
     * The method returns absolute or relative path (URL) to the component by
     * passed name. The absolute URL used to determs path to assets (media-data)
     * as usual.
     * @param string $name The component name.
     * @param boolean $asAbsolute The optional flag is equal true by default.
     * @return string|null
     */
    public function getComponentUrl(string $name, bool $asAbsolute = true): ?string
    {
        $rootMap = $this->getConfig('componentsRootMap');
        $siteUrl = $asAbsolute ? $this->siteBaseUrl : '';
        return isset($rootMap[$name]) ? $siteUrl.$rootMap[$name] : null;
    }

    /**
     * The method returns full path to the parent directory of component by its
     * name.
     * @param string $name The component name.
     * @return string|null
     */
    public function getComponentRoot(string $name): ?string
    {
        $rootMap = $this->getConfig('componentsRootMap');
        return isset($rootMap[$name]) ? $this->getAppRoot().$rootMap[$name] : null;
    }
}
