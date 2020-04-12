<?php

namespace EMAILOBFUSCATOR\Emailobfuscator\Tests\Service;


use EMAILOBFUSCATOR\Emailobfuscator\Service\ObfuscationService;

class ObfuscationServiceTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{

    protected $fixture = null;

    protected function setUp()
    {
        $this->fixture = new ObfuscationService();
    }


    /**
     * @test
     */
    public function testObfuscateEmailLinks()
    {
        $content = 'DONTCHANGE4 <a class="maillink fast" id="test1000" href="mailto:tp@zotorn1.de" name="supername" title="Mail now!1" >send mail</a> DONTCHANGE1 ' .
            '<a class="maillink fast" id="test1000" href="mailto:tp@zotorn2.de" name="supername" title="Mail now!2" >send mail</a> DONTCHANGE2' .
            '<a class="maillink fast" id="test1000" href="mailto:tp@zotorn3.de" name="supername" title="Mail now!3" >send mail</a> DONTCHANGE3';

        $result = $this->fixture->obfuscateEmailLinks($content);

        $this->assertFalse(mb_strpos($result, 'mailto:tp@zotorn1.de'));
        $this->assertFalse(mb_strpos($result, 'mailto:tp@zotorn2.de'));
        $this->assertFalse(mb_strpos($result, 'mailto:tp@zotorn3.de'));
        $this->assertFalse(mb_strpos($result, 'Mail now!1'));
        $this->assertFalse(mb_strpos($result, 'Mail now!2'));
        $this->assertFalse(mb_strpos($result, 'Mail now!3'));


        $this->assertTrue(mb_strpos($result, 'DONTCHANGE1') >= 0);
        $this->assertTrue(mb_strpos($result, 'DONTCHANGE2') >= 0);
        $this->assertTrue(mb_strpos($result, 'DONTCHANGE3') >= 0);
        $this->assertTrue(mb_strpos($result, 'DONTCHANGE4') >= 0);
    }

    /**
     * @test
     */
    public function testObfuscatePlainEmails()
    {
        $content = ' 
                mail@tomgrill.de 
                sdfg
                sdg
                 >ma42.34il@tomg33rill.de< dfgs
                gsdfgsdfgsdf
                
                ="mail@tom22grill.de" 
                
                
                =\'mail@tom22grill.de\'
                
                
                mail@tomgrill.de
                ';

        $result = $this->fixture->obfuscatePlainEmails($content);

        $this->assertFalse(mb_strpos($result, 'mail@tomgrill.de'));
        $this->assertFalse(mb_strpos($result, 'ma42.34il@tomg33rill.de'));
        $this->assertFalse(mb_strpos($result, 'mail@tom22grill.de'));
        $this->assertFalse(mb_strpos($result, 'mail@tomgrill.de'));


        $this->assertTrue(mb_strpos($result, 'gsdfgsdfgsdf') >= 0);
        $this->assertTrue(mb_strpos($result, 'sdfg') >= 0);
        $this->assertTrue(mb_strpos($result, 'sdg') >= 0);
    }
}
