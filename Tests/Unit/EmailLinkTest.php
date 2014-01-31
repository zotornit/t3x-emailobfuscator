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

/**
 * Test case.
 *
 * @package TPRONOLD
 * @subpackage tx_emailobfuscator
 *
 * @author Thomas Pronold <tp@tpronold.de>
 */

require_once(t3lib_extMgm::extPath('emailobfuscator') . 'Classes/EmailLink.php');

class EmailLinkTest extends Tx_Phpunit_TestCase {

    protected $fixture;

    public function setUp() {
        $linkToSet = '<a href="mailto:tp@tpronold.de">tp@tpronold.de</a>';
        $this->fixture = new EmailLink($linkToSet);
    }

    public function tearDown() {
        unset($this->fixture);
    }

    /**
     * @test
     */
    public function setLink() {
        $linkToSet = '<a href="mailto:tp@tpronold.de">tp@tpronold.de</a>';
        $this->fixture->setLink($linkToSet);
    }

    /**
     * @test
     */
    public function getPreHREF() {
        $this->assertEquals('', $this->fixture->getPreHREF());
    }

    /**
     * @test
     */
    public function getPostHREF() {
        $this->assertEquals('', $this->fixture->getPostHREF());
    }

    /**
     * @test
     */
    public function getEmail() {
        $this->assertEquals('tp@tpronold.de', $this->fixture->getEmail());
    }

    /**
     * @test
     */
    public function getLinkText() {
        $this->assertEquals('tp@tpronold.de', $this->fixture->getLinkText());
    }

    /**
     * @test
     */
    public function getParsingOfComplicatedLink() {
        $this->fixture->setLink('<a id="em1"   onclick="javascript:alert(\'ALERT\');"  href="mailto:tp@tpronold.de"'
        . 'style="border:1px solid red;" class="email">Tom does some extension  </a>');

        $this->assertEquals('Tom does some extension', $this->fixture->getLinkText());
        $this->assertEquals('tp@tpronold.de', $this->fixture->getEmail());
        $this->assertEquals('style="border:1px solid red;" class="email"', $this->fixture->getPostHREF());
        $this->assertEquals('id="em1"   onclick="javascript:alert(\'ALERT\');"', $this->fixture->getPreHREF());
    }

    /**
     * @test
     *
     * @expectedException InvalidLinkException
     *
     * @throws InvalidLinkException
     */
    public function setInvalidLinkThrowsException() {
        $linkToSet = '';
        $this->fixture->setLink($linkToSet);
    }

    /**
     * @test
     */
    public function validateValidLink() {
        $linkToValidate = '<a href="mailto:tp@tpronold.de">tp@tpronold.de</a>';
        $this->assertTrue(EmailLink::validate($linkToValidate));
    }

    /**
     * @test
     */
    public function validateInvalidLinks() {
        $linksToValidate[] = ' <a href="mailto:tp@tpronold.de">tp@tpronold.de</a>';
        $linksToValidate[] = '<a href="mailto:tp@tpronold.de">tp@tpronold.de</a> ';
        $linksToValidate[] = 'a<a href="mailto:tp@tpronold.de">tp@tpronold.de</a>';
        $linksToValidate[] = '<a href="mailto:tp@tpronold.de">tp@tpronold.de</a>b';
        $linksToValidate[] = ' <a hrdef="mailto:tp@tpronold.de">tp@tpronold.de</a>';
        $linksToValidate[] = '<a href="mailtof:tp@tpronold.de">tp@tpronold.de</a>';
        $linksToValidate[] = '<a href="mailto:tp@tpronold">tp@tpronold.de</a>';

        foreach ($linksToValidate as $link) {
            $this->assertFalse(EmailLink::validate($link));
        }
    }

}

?>