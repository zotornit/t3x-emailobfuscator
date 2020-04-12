<?php

namespace EMAILOBFUSCATOR\Emailobfuscator\Tests\Hook;

use EMAILOBFUSCATOR\Emailobfuscator\Hook\ObfuscationHook;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ObfuscationHookTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected $fixture = null;

    function setUp()
    {
        $this->resetSingletonInstances = true;
        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class);
        $GLOBALS['TSFE']->config = ['config' => ['spamProtectEmailAddresses' => 0]];
        $GLOBALS['TSFE']->id = 123;

        $this->fixture = new ObfuscationHook();
    }

    /**
     * @test
     */
    public function testObfuscateDoesNotChangeContentWhenSpamProtectEmailAddressesEnabled()
    {
        $GLOBALS['TSFE']->config = ['config' => ['spamProtectEmailAddresses' => 3]];
        $params['pObj'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();
        $params['pObj']->content = '<a href="mailto:tp@zotorn.de">Send mail</a>';

        $expected = $params['pObj']->content;
        $this->fixture->obfuscatePageContent($params);
        $this->assertEquals($expected, $params['pObj']->content);
    }
}
