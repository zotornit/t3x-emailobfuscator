<?php

namespace EMAILOBFUSCATOR\Emailobfuscator\Tests\Unit;

use EMAILOBFUSCATOR\Emailobfuscator\EmailLink;
use EMAILOBFUSCATOR\Emailobfuscator\Obfuscator;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ObfuscatorTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase {

    protected $fixture;

    public function setUp() {
        $requestProphecy = $this->prophesize(TypoScriptFrontendController::class);
        $requestProphecy->config = [
            'config' => []
        ];
        $GLOBALS['TSFE'] = $requestProphecy;

        $linkToSet = '<a href="mailto:tp@tpronold.de">tp@tpronold.de</a>';
        $this->fixture = new Obfuscator(new EmailLink($linkToSet));
    }

    public function tearDown() {
        unset($this->fixture);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     *
     * @throws \InvalidArgumentException
     */
    public function setInvalidArgumentThrowsExceptionTest() {
        new Obfuscator("TEST");
    }

    /**
     * @test
     */
    public function cutToPiecesTest() {
        $string = 'mv0a43u5q0n8510n8501v501801841ß23840134oi1hf4o1u501284502180ß1';
        $result = Obfuscator::cutToPieces($string);

        foreach ($result as $value) {
            if (mb_strlen($value) >= 1 && mb_strlen($value) <= 4) {
                $this->assertTrue(TRUE);
            } else {
                $this->assertTrue(FALSE, $value);
            }
        }

    }

}
