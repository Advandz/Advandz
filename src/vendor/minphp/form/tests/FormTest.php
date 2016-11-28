<?php
namespace Minphp\Form\Tests;

use PHPUnit_Framework_TestCase;
use Minphp\Form\Form;

/**
 * @coversDefaultClass \Minphp\Form\Form
 */
class FormTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf('\Minphp\Form\Form', new Form());
    }
}
