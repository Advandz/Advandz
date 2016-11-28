<?php
namespace Minphp\Configure\Reader;

use Minphp\Configure\Reader\Exception\ReaderParseException;
use SplFileObject;
use ArrayIterator;

/**
 * JSON Reader
 *
 * Reads JSON config files.
 */
class JsonReader implements ReaderInterface
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
        $data = null;
        while (!$this->file->eof()) {
            $data .= $this->file->fgets();
        }

        $data = json_decode($data);

        if (!is_object($data)) {
            throw new ReaderParseException("Unable to parse JSON file.");
        }

        return new ArrayIterator($data);
    }
}
