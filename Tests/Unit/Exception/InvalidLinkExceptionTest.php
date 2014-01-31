<?php

require_once(t3lib_extMgm::extPath('emailobfuscator') . 'Classes/Exception/InvalidLinkException.php');

class InvalidLinkExceptionTest extends Tx_Phpunit_TestCase {
    /**
     * @test
     *
     * @expectedException InvalidLinkException
     *
     * @throws InvalidLinkException
     */
    public function exceptionCanBeThrown() {
        throw new InvalidLinkException('some message', 12345);
    }
}