<?php

namespace EMAILOBFUSCATOR\Emailobfuscator\Tests\Unit\Hook;

use EMAILOBFUSCATOR\Emailobfuscator\Hook\ObfuscationHook;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ObfuscationHookTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected $fixture = null;

    protected $mockConfigurationManager;

    function setUp()
    {
        $this->resetSingletonInstances = true;
        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class);
        $GLOBALS['TSFE']->config = ['config' => ['spamProtectEmailAddresses' => 0]];
        $GLOBALS['TSFE']->id = 123;

        $this->fixture = new ObfuscationHook();

        $objectManager = $this->prophesize(ObjectManager::class);
        GeneralUtility::setSingletonInstance(ObjectManager::class, $objectManager->reveal());

        $this->mockConfigurationManager = $this->prophesize(ConfigurationManagerInterface::class);

        $objectManager->get(ConfigurationManagerInterface::class)->shouldBeCalled()->willReturn($this->mockConfigurationManager);

    }

    /**
     * @test
     */
    public function testObfuscatePageContentDoesNotChangeContentWhenSpamProtectEmailAddressesEnabled()
    {

        $this->mockConfigurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, "emailobfuscator")
            ->shouldBeCalled()->willReturn([
                'enabled' => true
            ]);


        $GLOBALS['TSFE']->config = ['config' => ['spamProtectEmailAddresses' => 3]];
        $params['pObj'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();
        $params['pObj']->content = '<a href="mailto:tp@zotorn.de">Send mail</a>';

        $expected = $params['pObj']->content;
        $this->fixture->obfuscatePageContent($params);
        $this->assertEquals($expected, $params['pObj']->content);
    }


    /**
     * @test
     */
    public function testObfuscatePageContentDoesNotChangeContentWhenPluginIsDisabled()
    {
        $params['pObj'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();
        $params['pObj']->content = '<a href="mailto:tp@zotorn.de">Send mail</a>';

        $expected = $params['pObj']->content;
        $this->fixture->obfuscatePageContent($params);
        $this->assertEquals($expected, $params['pObj']->content);
    }



    /**
     * @test
     */
    public function testObfuscatePageContentWorks()
    {

        $this->mockConfigurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, "emailobfuscator")
            ->shouldBeCalled()->willReturn([
                'enabled' => true
            ]);


        $GLOBALS['TSFE']->config = ['config' => ['spamProtectEmailAddresses' => 0]];
        $params['pObj'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();
        $params['pObj']->content = '<a href="mailto:tp@zotorn.de">Send mail</a>';

        $expected = $params['pObj']->content;
        $this->fixture->obfuscatePageContent($params);
        $this->assertNotEquals($expected, $params['pObj']->content);
    }

}
