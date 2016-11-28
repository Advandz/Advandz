<?php
namespace Minphp\Container;

use Pimple\Container as PimpleContainer;
use Minphp\Container\Exception\NotFoundException;

/**
 * A Container that extends Pimple using a standards compliant interface
 *
 */
class Container extends PimpleContainer implements ContainerInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!$this->offsetExists($id)) {
            throw new NotFoundException(sprintf('Identifier "%s" is not defined.', $id));
        }

        return $this->offsetGet($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * {@inheritdoc}
     */
    public function set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($id)
    {
        $this->offsetUnset($id);
    }
}
