<?php

/**
 * The InfusionSoft SDK
 */
require_once plugin_dir_path(__FILE__) . '/vendors/infusion/isdk.php';

class InfusionProxy
{

    /**
     * $app iSDK instance
     */
    private $app;

    /**
     * Infusion Application Name
     * @var string
     */
    private $applicationName = 'un207';
    //private $applicationName = 'hc212';

    /**
     * Infusion Aplication API KEY
     * @var string
     */
    private $applicationKey = 'f10ab9a2b2c628b153ad760eed24ce09';
    //private $applicationKey = '0d996783a2c143e779608b494a5dfaa1bbd0849fdc48e21fd9f8c98c65d7adf3';

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @static var Singleton $instance The *Singleton* instances of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
        try {
            $this->app = new iSDK();
            $this->app->cfgCon($this->applicationName, $this->applicationKey);
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }

    }

    public function getApp()
    {
        return $this->app;
    }

    /**
     * Helper to log the SDK errors.
     * @param  string $error Error to log
     */
    private function log($error)
    {
        error_log('InfusionProxy Error ' . $error);
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}
