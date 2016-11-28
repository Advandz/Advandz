<?php
namespace Minphp\Cache;

/**
 * Handles writing to and from the cache
 */
class Cache
{
    /**
     * @var string The path to the cache directory
     */
    protected $cache_dir;
    /**
     * @var int Cache directory permissions
     */
    protected $cache_dir_perms;
    /**
     * @var string The file extension for cache files
     */
    protected $cache_ext;

    /**
     * Initialize the cache environment
     *
     * @param string $cache_dir The default cache directory
     * @param int $cache_dir_perms The permissions for the cache directory (if it must be created)
     * @param string $cache_ext The file extension to use for files in this cache
     */
    public function __construct($cache_dir, $cache_dir_perms = 0755, $cache_ext = null)
    {
        $this->cache_dir = $cache_dir;
        $this->cache_dir_perms = $cache_dir_perms;
        $this->cache_ext = $cache_ext;
    }

    /**
     * Empties the entire cache of all files (directories excluded, not recursive)
     *
     * @param string $path The path within cache directory to empty
     */
    public function clear($path = null)
    {
        if (!($dir = @opendir($this->cache_dir . $path))) {
            return;
        }

        while ($item = @readdir($dir)) {
            if (is_file($this->cache_dir . $path . $item)) {
                @unlink($this->cache_dir . $path . $item);
            }
        }
    }

    /**
     * Removes the given cache file from the cache
     *
     * @param string $name The name of the cache file to remove
     *  (note: the original file name, not the cached name of the file)
     * @param string $path The path within cache directory to clear a given file from
     * @return boolean True if removed, false if no such file exists
     * @see Cache::cacheName
     */
    public function remove($name, $path = null)
    {
        $cache = $this->cacheName($name, $path);
        if (is_file($cache)) {
            @unlink($cache);
            return true;
        }
        return false;
    }

    /**
     * Writes the given data to the cache using the name given
     *
     * @param string $name The name of the cache file to create
     *  (note: the original file name, not the cached name of the file)
     * @param string $output The data to write to the cache
     * @param int $ttl The cache file's time to live
     * @param string $path The path within cache directory to write the file to
     * @see Cache::cacheName
     */
    public function write($name, $output, $ttl, $path = null)
    {
        $cache = $this->cacheName($name, $path);

        $cache_dir = dirname($cache);
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir, $this->cache_dir_perms, true);
        }

        // Save output to cache file
        file_put_contents($cache, $output);
        // Set the cache expiration date/time
        touch($cache, time()+$ttl);
    }

    /**
     * Fetches the contents of a cache, if it exists and is valid
     *
     * @param string $name The name of the cache file to fetch
     *  (note: not the actual name of the file on the file system)
     * @param string $path The path within cache directory to read the file from
     * @return string A string containing the file contents if the cache file
     *  exists and has not yet expired, false otherwise.
     * @see Cache::cacheName
     */
    public function fetch($name, $path = null)
    {
        $cache = $this->cacheName($name, $path);
        if (file_exists($cache) && filemtime($cache) > time()) {
            return file_get_contents($cache);
        } else {
            return false;
        }
    }

    /**
     * Builds the file name of the cache file based on the name given
     *
     * @param string $name The name to use when creating the cache file name
     * @param string $path The path within cache directory to construct the cache file path in
     * @return string A fully qualified cache file name
     */
    protected function cacheName($name, $path = null)
    {
        return $this->cache_dir . $path . md5(strtolower($name)) . $this->cache_ext;
    }
}
