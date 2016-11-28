<?php
namespace Minphp\Pagination\Tests;

use Minphp\Pagination\Pagination;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass \Minphp\Pagination\Pagination
 */
class PaginationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::mergeArrays
     * @uses \Minphp\Pagination\Pagination::setGet
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('\Minphp\Pagination\Pagination', new Pagination());
    }
}
