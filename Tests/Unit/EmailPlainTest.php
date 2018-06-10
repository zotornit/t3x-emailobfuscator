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

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('emailobfuscator') . 'Classes/EmailPlain.php');

class EmailPlainTest extends \PHPUnit_Framework_TestCase {

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
     * @expectedException InvalidLinkException
     *
     * @throws InvalidLinkException
     */
    public function setInvalidLinkThrowsExceptionTest() {
        $linkToSet = '';
        $this->fixture->setLink($linkToSet);
    }

}