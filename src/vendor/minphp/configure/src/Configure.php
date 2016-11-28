<?php
namespace Minphp\Configure;

use Minphp\Configure\Reader;
use ArrayIterator;
use UnexpectedValueException;

/**
 * Generic configuration library
 */
class Configure
{
    /**
     * @var \ArrayIterator
     */
    protected $data;

    /**
     * Initialize
     */
    public function __construct()
    {
        $this->data = new ArrayIterator();
    }

    /**
     * Loads a config file
     *
     * @param Reader\ReaderInterface $reader The reader to use
     * @throws ConfigureLoadException If the file is not valid
     * @throws \UnexpectedValueException If $reader failed to return the expected type
     */
    public function load(Reader\ReaderInterface $reader)
    {
        $data = $reader->getIterator();

        if (!($data instanceof ArrayIterator)) {
            throw new UnexpectedValueException(
                get_class($reader) . " failed to return an instance of \ArrayIterator."
            );
        }

        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Set a value in the config
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->data->offsetSet($key, $value);
    }

    /**
     * Retrieve a value in the config
     *
     * @param mixed $key
     * @return mixed
     */
    public function get($key)
    {
        if ($this->exists($key)) {
            return $this->data->offsetGet($key);
        }
        return null;
    }

    /**
     * Verify that a key exists
     *
     * @param mixed $key
     * @return boolean
     */
    public function exists($key)
    {
        return $this->data->offSetExists($key);
    }

    /**
     * Removes a value from the config
     *
     * @param mixed $key
     */
    public function remove($key)
    {
        if ($this->exists($key)) {
            $this->data->offsetUnset($key);
        }
    }
}
