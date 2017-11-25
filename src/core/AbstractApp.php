<?php

namespace nadir2\core;

/**
 * This is an abstract application class. It determines the central entry point for
 * the all requests, it creates the configured application. AbstractApp implements
 * Front Controller pattern, it's Singleton-instance.
 * @author Leonid Selikhov
 */
abstract class AbstractApp implements FrontControllerInterface, RunnableInterface
{
    use PropertyMethodsTrait;

    /** @var string This is path to the config file root. */
    public $configFile = '';

    /** @var string The path to the root of application. */
    public $appRoot = '';

    /** @var \nadir2\core\ProcessInterface The user defined Process object. */
    public $customProcess = null;

    /** @var self This is singleton object of the current class. */
    protected static $instance = null;

    /**
     * @ignore.
     */
    protected function __construct()
    {
        // Nothing here...
    }

    /**
     * It returns the context called singleton-instance. It implements late static
     * binding.
     * @return self
     */
    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * It sets the path to the main application config file referenced to its root.
     * @param string $filePath
     * @return self
     */
    public function setConfigFile(string $filePath): self
    {
        $this->configFile = $filePath;
        return static::$instance;
    }

    /**
     * It sets the root of application.
     * @param string $appRoot The path to the application root.
     * @return self
     */
    public function setAppRoot(string $appRoot): self
    {
        $this->appRoot = $appRoot;
        return static::$instance;
    }

    /**
     * It sets the custom Process.
     * @param \nadir2\core\ProcessInterface $process The user defined Process object.
     * @return self
     */
    public function setCustomProcess(ProcessInterface $process): self
    {
        $this->customProcess = $process;
        return static::$instance;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->init();
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->initHelper();
        $this->runCustomProcess();
        $this->handleRequest();
        $this->stopCustomProcess();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function handleRequest(): void;

    /**
     * It inits the application helper.
     * @return void
     */
    private function initHelper(): void
    {
        if (!$this->isConfigFileSet()) {
            throw new Exception("The main config file wasn't defined.");
        }
        if (!$this->isAppRootSet()) {
            throw new Exception("The application root wasn't defined.");
        }
        AppHelper::getInstance()
            ->setAppRoot($this->getAppRoot())
            ->setConfigFile($this->getConfigFile())
            ->run();
    }

    /**
     * This method runs custom process. The highest priority has a Process that
     * has been set by the setCustomProcess() method. If it wasn't set, it inits
     * the default Process from the project skeleton extension.
     * @return void
     */
    private function runCustomProcess(): void
    {
        if ($this->isCustomProcessSet()) {
            $this->getCustomProcess()->run();
        } elseif (class_exists('\extensions\core\Process')) {
            \extensions\core\Process::getInstance()->run();
        }
    }

    /**
     * The method kills custom process.
     * @return void
     */
    private function stopCustomProcess(): void
    {
        if ($this->isCustomProcessSet()) {
            $this->getCustomProcess()->stop();
        } elseif (class_exists('\extensions\core\Process')) {
            \extensions\core\Process::getInstance()->stop();
        }
    }
}
