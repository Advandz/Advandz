<?php
namespace Minphp\Container;

use Interop\Container\ContainerInterface as InteropContainerInterface;

/**
 * Container interface that expands on the Interop\Container\ContainerInterface
 *
 */
interface ContainerInterface extends InteropContainerInterface
{
    /**
     * Adds an entry to the container
     *
     * @param string $id Identifier of the entry to add
     * @param mixed $value The entry to add to the container
     */
    public function set($id, $value);

    /**
     * Removes an entry from the container
     *
     * @param string $id Identifier of the entry to remove
     */
    public function remove($id);
}
