<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Thomas Pronold (tp@tpronold.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(t3lib_extMgm::extPath('emailobfuscator') . 'Classes/EncryptedEmailLink.php');

class EncryptedEmailLinkTest extends Tx_Phpunit_TestCase {

    protected $fixture;

    private $linkToTest = '<a href="javascript:linkTo_UnCryptMailto(\'thpsav1awGawyvuvsk5kl\');" class="mail" >tp(at)tpronold.de</a>';

    public function setUp() {
        $this->fixture = new EncryptedEmailLink($this->linkToTest, 7);
    }

    public function tearDown() {
        unset($this->fixture);
    }

    /**
     * @test
     */
    public function validEncryptedLinkTest() {
        $this->assertEquals(1, preg_match($this->fixture->getEncryptedEmailLinkPattern(), $this->linkToTest));
    }
}