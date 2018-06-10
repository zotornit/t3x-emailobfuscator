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

class Tx_EmailObfuscator_EmailPlain extends EmailPlain {

}

class EmailPlain extends EmailLink {
    protected $link = '';
    protected $email = '';

    protected $emailLinkPattern = '#([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6})#i';

    public function setLink($link) {
        if ($this->isValid($link)) {
            $this->link = $link;
            $this->parse();
        } else {
            throw new InvalidLinkException('The plain email provided is not valid. Given: ' . $link);
        }
    }

    public function __construct($link) {
        $this->setLink($link);
    }

//    protected function validate($link) {
//        if (preg_match($this->getEmailLinkPattern(), $link)) {
//            return TRUE;
//        }
//        return FALSE;
//    }

    protected function isValid($link) {
        if (preg_match($this->getEmailLinkPattern(), $link)) {
            return TRUE;
        }
        return FALSE;
    }

    protected function parse() {
        preg_match_all($this->getEmailLinkPattern(), $this->link, $matches);
        $this->email = trim($matches[1][0]);
    }

    public function getEmail() {
        return $this->email;
    }

    public function getEmailLinkPattern() {
        return $this->emailLinkPattern;
    }

}