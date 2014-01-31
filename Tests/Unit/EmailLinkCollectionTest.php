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

require_once(t3lib_extMgm::extPath('emailobfuscator') . 'Classes/EmailLinkCollection.php');

class EmailLinkCollectionTest extends Tx_Phpunit_TestCase {

    protected $fixture;

    public function setUp() {
        $this->fixture = new EmailLinkCollection();
    }

    public function tearDown() {
        unset($this->fixture);
    }

//    /**
//     * @test
//     */
//    public function addEmailLink() {
//        $linkToAdd = '<a href="mailto:tp@tpronold.de">tp@tpronold.de</a>';
//        $this->fixture->addEmailLink();
//    }

}

?>