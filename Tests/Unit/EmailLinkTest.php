<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Thomas Pronold (someone@somewhere.tld)
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

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('emailobfuscator') . 'Classes/EmailLink.php');

class EmailLinkTest extends \PHPUnit_Framework_TestCase {

    protected $fixture;

    public function setUp() {
        $linkToSet = '<a href="mailto:someone@somewhere.tld">someone@somewhere.tld</a>';
        $this->fixture = new EmailLink($linkToSet);
    }

    public function tearDown() {
        unset($this->fixture);
    }

    /**
     * @test
     */
    public function setLinkTest() {
        $linkToSet = '<a href="mailto:someone@somewhere.tld">someone@somewhere.tld</a>';
        $this->fixture->setLink($linkToSet);
    }

    /**
     * @test
     */
    public function getPreHREFTest() {
        $this->assertEquals('', $this->fixture->getPreHREF());
    }

    /**
     * @test
     */
    public function getPostHREFTest() {
        $this->assertEquals('', $this->fixture->getPostHREF());
    }

    /**
     * @test
     */
    public function getEmailTest() {
        $this->assertEquals('someone@somewhere.tld', $this->fixture->getEmail());
    }

    /**
     * @test
     */
    public function getLinkTextTest() {
        $this->assertEquals('someone@somewhere.tld', $this->fixture->getLinkText());
    }

    /**
     * @test
     */
    public function getParsingOfComplicatedLinkTest() {
        $this->fixture->setLink('<a id="em1"   onclick="javascript:alert(\'ALERT\');"  href="mailto:someone@somewhere.tld"'
        . 'style="border:1px solid red;" class="email">Tom does some extension  </a>');

        $this->assertEquals('Tom does some extension', $this->fixture->getLinkText());
        $this->assertEquals('someone@somewhere.tld', $this->fixture->getEmail());
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
    public function setInvalidLinkThrowsExceptionTest() {
        $linkToSet = '';
        $this->fixture->setLink($linkToSet);
    }

    /**
     * @test
     */
    public function validLinkTest() {
        $linkToValidate = '<a href="mailto:someone@somewhere.tld">someone@somewhere.tld</a>';
        $this->assertEquals(1, preg_match($this->fixture->getEmailLinkPattern(), $linkToValidate));
    }

    /**
     * @test
     */
    public function invalidLinksTest() {
        $linksToValidate[] = ' <a href="mailto:someone@somewhere.tld">someone@somewhere.tld</a>';
        $linksToValidate[] = '<a href="mailto:someone@somewhere.tld">someone@somewhere.tld</a> ';
        $linksToValidate[] = 'a<a href="mailto:someone@somewhere.tld">someone@somewhere.tld</a>';
        $linksToValidate[] = '<a href="mailto:someone@somewhere.tld">someone@somewhere.tld</a>b';
        $linksToValidate[] = ' <a hrdef="mailto:someone@somewhere.tld">someone@somewhere.tld</a>';
        $linksToValidate[] = '<a href="mailtof:someone@somewhere.tld">someone@somewhere.tld</a>';
        $linksToValidate[] = '<a href="mailto:tp@tpronold">someone@somewhere.tld</a>';

        foreach ($linksToValidate as $link) {
            $this->assertEquals(0, preg_match($this->fixture->getEmailLinkPattern(), $link));
        }
    }

}