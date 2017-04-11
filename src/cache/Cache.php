<?php

namespace cApiConnect\cache;

/**
 * Cache System
 * @author thomas
 */
Final class Cache
{
    /**
     * Time cache in local (second)
     * @var type
     */
    protected $time;

    /**
     * @var \cApiConnect\cache\ICache
     */
    protected $handler;

    /**
     * Constructor
     * @param \cApiConnect\cache\ICache $handler
     * @param Int $time
     */
    public function __construct($classFile = 'cacheFile', $time)
    {
        $this->initHandler($classFile);
        $this->timeLocal = $time;
    }

    /**
     * Return Handler Cache
     * @return \cApiConnect\cache\ICache
     */
    public function getHandler()
    {
        return $this->handler;
    }


    /**
     * Return Header Cache
     * @return Array
     */
    public function getHeaderCache()
    {
        return[
            'Cache-Control' => 'public, max-age='.$this->timeLocal,
            'Pragma' => 'cache',
        ];
    }

    /**
     * Init Class cache
     * @param Str $name Nom de la Classe
     */
    private function initHandler($name)
    {
        try {
            $class         = __NAMESPACE__.'\\'.ucfirst($name);
            $this->handler = new $class();
        } catch (Exception $ex) {
            echo ' The '.$name.' cache class was not found : ', $ex->getMessage();
        }
    }
}