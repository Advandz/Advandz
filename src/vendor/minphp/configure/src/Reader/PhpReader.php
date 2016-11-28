<?php
namespace Minphp\Configure\Reader;

use Minphp\Configure\Reader\Exception\ReaderParseException;
use ArrayIterator;
use SplFileObject;

/**
 * PHP Reader
 *
 * Reads PHP config files. Expects files to return an object or array of
 * key/value pairs.
 */
class PhpReader implements ReaderInterface
{
    /**
     * @var \SplFileObject The file to load
     */
    protected $file;

    /**
     * Prepare the config reader
     *
     * @param \SplFileObject $file
     */
    public function __construct(SplFileObject $file)
    {
        $this->file = $file;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        if (!$this->file->isFile()) {
            throw new ReaderParseException("Invalid file.");
        }
        return new ArrayIterator(include_once $this->file->getPathname());
    }
}
