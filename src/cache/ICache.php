<?php
namespace cApiConnect\cache;

/**
 * Interface Cache
 *
 * @author thomas
 */
interface ICache
{
    public function getStack();

    public function read($key);

    public function write($key, $contents);
}