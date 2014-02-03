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

require_once(t3lib_extMgm::extPath('emailobfuscator') . 'Classes/Obfuscator.php');

class ObfuscatorTest extends Tx_Phpunit_TestCase {

    protected $fixture;

    public function setUp() {
        $linkToSet = '<a href="mailto:tp@tpronold.de">tp@tpronold.de</a>';
        $this->fixture = new Obfuscator(new EmailLink($linkToSet));
    }

    public function tearDown() {
        unset($this->fixture);
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     *
     * @throws InvalidArgumentException
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

?>