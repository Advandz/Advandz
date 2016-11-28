<?php
namespace Minphp\Container;

/**
 * An interface allowing injection of a container
 *
 */
interface ContainerAwareInterface
{
    /**
     * Adds the container
     *
     * @param Minphp\Container\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null);
}
