<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2018 BuzzingPixel, LLC
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace buzzingpixel\stockpile\services;

/**
 * Class Storage
 */
class Storage
{
    /** @var Storage $instance */
    private static $instance = null;

    /** @var array $storage */
    private $storage = array();

    /**
     * Thou shalt not construct
     */
    private function __construct()
    {
    }

    /**
     * Thou shalt not clone
     */
    private function __clone()
    {
    }

    /**
     * Get instance
     */
    public static function getInstance()
    {
        // Check if we need to create an instance
        if (static::$instance === null) {
            static::$instance = new static;
        }

        // Return the instance
        return static::$instance;
    }

    /**
     * Set storage value
     * @param mixed $value
     * @param string $key
     * @param string $namespace
     * @return self
     */
    public function set($value, $key, $namespace = 'storage')
    {
        // Make sure the namespace is set on the array
        if (! isset($this->storage[$namespace])) {
            $this->storage[$namespace] = array();
        }

        // Set the value
        $this->storage[$namespace][$key] = $value;

        // Return instance
        return self::getInstance();
    }

    /**
     * Get storage value
     * @param string $key
     * @param string $namespace
     * @return mixed
     */
    public function get($key, $namespace = 'storage')
    {
        // If this item is not set, return null
        if (! isset($this->storage[$namespace][$key])) {
            return null;
        }

        // Return the value
        return $this->storage[$namespace][$key];
    }
}
