<?php
namespace Minphp\Language\Tests;

use PHPUnit_Framework_TestCase;
use Minphp\Language\Language;

/**
 * @coversDefaultClass \Minphp\Language\Language
 */
class LanguageTest extends PHPUnit_Framework_TestCase
{
    protected $lang_path;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->lang_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . "Fixtures" . DIRECTORY_SEPARATOR;
    }

    /**
     * @covers ::_
     * @covers ::getText
     * @covers ::loadLang
     */
    public function testShortGetText()
    {
        Language::setLang("en_us");
        Language::loadLang("language_test", "en_us", $this->lang_path);

        $this->assertEquals("The blue car is fast.", Language::_("LanguageTest.b", true, "blue", "fast", "car"));
    }

    /**
     * @covers ::getText
     * @covers ::loadLang
     * @covers ::allowPassthrough
     */
    public function testGetText()
    {
        Language::setLang("en_us");
        Language::loadLang("language_test", "en_uk", $this->lang_path);

        $this->assertEquals(
            "The blue car is fast.",
            Language::getText("LanguageTest.b", true, "blue", "fast", "car")
        );
        $this->assertEquals(
            "The blue car is fast.",
            Language::getText("LanguageTest.b", true, array("blue", "fast", "car"))
        );

        $this->assertEquals("I like the color green.", Language::getText("LanguageTest.a", true));

        Language::setLang("en_uk");
        $this->assertEquals("The blue car is fast.", Language::getText("LanguageTest.b", true, "blue", "fast", "car"));

        Language::allowPassthrough(true);
        $this->assertEquals("Non-existent", Language::getText("Non-existent", true));

        $this->expectOutputString("I like the colour green.");
        Language::getText("LanguageTest.a");
    }

    /**
     * @covers ::loadLang
     * @covers ::setDefaultLanguage
     */
    public function testLoadLang()
    {
        Language::setDefaultLanguage("en_uk");
        Language::loadLang("language_test", null, $this->lang_path);
        Language::loadLang(array("language_test", "language_not_exists"), null, $this->lang_path);

        Language::setLang("en_us");
        $this->assertEquals("I like the color green.", Language::getText("LanguageTest.a", true));

        Language::setLang("en_uk");
        $this->assertEquals("I like the colour green.", Language::getText("LanguageTest.a", true));
    }

    /**
     * @covers ::setLang
     */
    public function testSetLang()
    {
        Language::setLang(null);
        $this->assertNull(Language::setLang("en_uk"));
        $this->assertEquals("en_uk", Language::setLang("en_us"));

    }
}
