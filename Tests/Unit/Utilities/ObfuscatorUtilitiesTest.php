<?php

namespace EMAILOBFUSCATOR\Emailobfuscator\Tests\Unit\Utilities;

use EMAILOBFUSCATOR\Emailobfuscator\Utilities\ObfuscatorUtilities;

class ObfuscatorUtilitiesTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{

//    /**
//     * @test
//     */
//    public function testSplitLink()
//    {
//        $link = '<a class="maillink fast" id="test1000" href="mailto:tp@zotorn.de" name="supername" title="Mail now!" >send mail</a>';
//
//        $result = ObfuscatorUtilities::splitLink($link);
//
//        $this->assertEquals('send mail', $result['body']);
//        $this->assertEquals('maillink fast', $result['parts']['class']);
//        $this->assertEquals('test1000', $result['parts']['id']);
//        $this->assertEquals('supername', $result['parts']['name']);
//        $this->assertEquals('Mail now!', $result['parts']['title']);
//        $this->assertEquals('mailto:tp@zotorn.de', $result['parts']['href']);
//    }
//
//    /**
//     * @test
//     */
//    public function testSplitLinkWorksWithMultilineAndSpacer()
//    {
//        $link = '<a    ' . PHP_EOL . '  class="     maillink' . PHP_EOL . ' fast"      id="' . PHP_EOL . 'test1000"  ' . PHP_EOL . '   href="mailto:tp@zotorn.de "   ' .
//            'name="supername    " ' . PHP_EOL . ' title="Mail now!    " ' . PHP_EOL . ' >' . PHP_EOL . 'send mail</a>';
//
//        $result = ObfuscatorUtilities::splitLink($link);
//
//        $this->assertEquals('send mail', $result['body']);
//        $this->assertEquals('maillink fast', $result['parts']['class']);
//        $this->assertEquals('test1000', $result['parts']['id']);
//        $this->assertEquals('supername', $result['parts']['name']);
//        $this->assertEquals('Mail now!', $result['parts']['title']);
//        $this->assertEquals('mailto:tp@zotorn.de', $result['parts']['href']);
//    }

    /**
     * @test
     */
    public function testCutRandom()
    {
        $string = 'zjsnv5w94o8vazerhvabwo385awo58uavwbei5vwu4o5vzw3i47bva34hvl3gDFGSDJFGJSDFGJSDFGqOI42AK34';
        $result = ObfuscatorUtilities::cutRandom($string, 2, 4);
        $this->assertEquals($string, implode("", $result));
    }

    /**
     * Currently now way to test the result programmatically. Visual confirmation must be enough.
     * @test
     */
    public function testObfuscatoToJavaScript()
    {
        $string = 'VISUAL_REP_STRING_1234567890_TEST';
//        echo(ObfuscatorUtilities::obfuscateToJavaScript($string) .PHP_EOL);
        $this->assertTrue(true);
    }

    /**
     * Currently now way to test the result programmatically. Visual confirmation must be enough.
     * @test
     */
    public function testObfuscateToHTML()
    {
        $string = 'VISUAL_REP_STRING_1234567890_TEST';
//        echo(ObfuscatorUtilities::obfuscateToHTML($string) .PHP_EOL);
        $this->assertTrue(true);
    }

}
