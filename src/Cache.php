<?php

namespace Alnv\CatalogManagerBundle;

class Cache
{

    protected static $objInstance;

    protected static $arrData = [];

    public static function has($strKey)
    {
        return isset(static::$arrData[$strKey]);
    }

    public static function get($strKey)
    {
        return static::$arrData[$strKey];
    }

    public static function set($strKey, $varValue)
    {
        static::$arrData[$strKey] = $varValue;
    }

    public static function remove($strKey)
    {
        unset(static::$arrData[$strKey]);
    }

    protected function __construct()
    {
    }

    final public function __clone()
    {
    }

    public function __isset($strKey)
    {
        return static::has($strKey);
    }

    public function __get($strKey)
    {
        if (static::has($strKey))
        {
            return static::get($strKey);
        }

        return null;
    }

    public function __set($strKey, $varValue)
    {
        static::set($strKey, $varValue);
    }

    public function __unset($strKey)
    {
        static::remove($strKey);
    }

    public static function getInstance()
    {
        if (static::$objInstance === null)
        {
            static::$objInstance = new static();
        }

        return static::$objInstance;
    }
}