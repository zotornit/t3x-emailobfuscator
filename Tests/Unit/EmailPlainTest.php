<?php
namespace EMAILOBFUSCATOR\Emailobfuscator\Tests\Unit;

use EMAILOBFUSCATOR\Emailobfuscator\EmailPlain;

class EmailPlainTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase {

    protected $fixture;

    public function setUp() {
        $linkToSet = 'someone@somewhere.tld';
        $this->fixture = new EmailPlain($linkToSet);
    }

    public function tearDown() {
        unset($this->fixture);
    }

    /**
     * @test
     */
    public function setLinkTest() {
        $linkToSet = 'someone@somewhere.tld';
        $this->fixture->setLink($linkToSet);
    }
    /**
     * @test
     */
    public function getEmailTest() {
        $this->assertEquals('someone@somewhere.tld', $this->fixture->getEmail());
    }

    /**
     * @test
     *
     * @expectedException \EMAILOBFUSCATOR\Emailobfuscator\Exception\InvalidLinkException
     *
     * @throws \EMAILOBFUSCATOR\Emailobfuscator\Exception\InvalidLinkException
     */
    public function setInvalidLinkThrowsExceptionTest() {
        $linkToSet = '';
        $this->fixture->setLink($linkToSet);
    }

}
