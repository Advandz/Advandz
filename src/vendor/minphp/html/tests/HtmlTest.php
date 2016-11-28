<?php
namespace Minphp\Html\Tests;

use PHPUnit_Framework_TestCase;
use Minphp\Html\Html;

/**
 * @coversDefaultClass \Minphp\Html\Html
 */
class HtmlTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf('\Minphp\Html\Html', new Html());
    }
}
