<?php
namespace cApiConnect\cache;

use Kevinrob\GuzzleCache\CacheMiddleware;
use League\Flysystem\Adapter\Local;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Kevinrob\GuzzleCache\Storage\FlysystemStorage;

/**
 * Managing Cache File with League\Flysystem
 * @author thomas
 */
class CacheFile implements ICache
{
    /**
     * Folder Save Cache
     * @var String
     */
    protected $folderSave;

    /**
     * Permission folder
     * @var String
     */
    protected $chmod;

    /**
     * Set folder cache
     * @param type $folderCache
     * @param type $chmod
     */
    public function setFolder($folderCache, $chmod = 0777)
    {
        $this->folderSave = $folderCache;
        $this->chmod      = $chmod;
    }


    /**
     * Read file in cache
     * @param str $key  Name file
     * @return boolean
     */
    public function read($key)
    {
        $file    = 'local://'.$key;
        $manager = $this->getManagerFileSystem();

        if ($manager->has($file) == true) {
            return $manager->read($file);
        }
        return false;
    }

    /**
     * Write file in cache
     * @param str $key Name file
     * @param str $contents
     * @return type
     */
    public function write($key, $contents)
    {
        $file    = 'local://'.$key;
        $manager = $this->getManagerFileSystem();

        if ($manager->has($file) == true) {
            return $manager->update($file, $contents);
        } else {
            return $manager->write($file, $contents);
        }
    }

    /**
     * Get Handler Stack
     * @return GuzzleHttp\HandlerStack
     */
    public function getStack()
    {
        $localStorage = $this->getStorage();

        $systemStorage = new FlysystemStorage($localStorage);
        $cacheStrategy = new PrivateCacheStrategy($systemStorage);
        $cache         = new CacheMiddleware($cacheStrategy);
        $cache->setHttpMethods(['GET' => true, 'POST' => false, 'DELETE' => false, 'PUT' => false]);

        $stack = \GuzzleHttp\HandlerStack::create();
        $stack->push($cache);
        return $stack;
    }

    /**
     * Return instance file System
     * @return FlysystemStorage
     */
    protected function getStorage()
    {
        if ($this->folderSave == null) {
            trigger_error("The folder path is not defined", E_USER_ERROR);
        }

        //Create folder save
        $this->createFolderSave();

        return new Local($this->folderSave);
    }


    /**
     * Get Manager
     */
    protected function getManagerFileSystem()
    {
        $storage = $this->getStorage();
        return new \League\Flysystem\MountManager([
            'local' => new \League\Flysystem\Filesystem($storage, ['disable_asserts' => true])
        ]);
    }

    /**
     * Create folder Save
     */
    protected function createFolderSave()
    {
        if (is_dir($this->folderSave) == false) {
            if (!mkdir($concurrentDirectory = $this->folderSave, $this->chmod, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }else{
                chmod($this->folderSave, $this->chmod);
            }
        }
    }
}
