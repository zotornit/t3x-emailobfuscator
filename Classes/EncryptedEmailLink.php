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

class Tx_EmailObfuscator_EncryptedEmailLink extends EncryptedEmailLink {

}

class EncryptedEmailLink extends EmailLink {

    protected $encryptedEmailLinkPattern = '/^<a(.+?)href=[\'"]javascript:linkTo_UnCryptMailto\(\'(.{1,})\'\);[\'"](.*?)>(.*?)<\/a>$/i';

    public function setLink($link, $spamProtectEmailAddresses, $spamProtectEmailAddresses_atSubst = '(at)', $spamProtectEmailAddresses_lastDotSubst = '.') {
        if ($this->isEncryptionValid($link)) {

            preg_match_all($this->encryptedEmailLinkPattern, $link, $matches);

            $newLink = str_replace(
                array(
                    $matches[4][0],
                    'javascript:linkTo_UnCryptMailto(\'' . $matches[2][0] . '\');'
                ),
                array(
                    str_replace(array($spamProtectEmailAddresses_atSubst, $spamProtectEmailAddresses_lastDotSubst), array('@', '.'), $matches[4][0]),
                    self::decryptLink($matches[2][0], $spamProtectEmailAddresses),
                ),
                $link
            );
            parent::setLink($newLink);
        } else {
            throw new InvalidLinkException('The encrypted link provided is not valid. Given: ' . $link);
        }
    }

    public function __construct($link, $spamProtectEmailAddresses, $spamProtectEmailAddresses_atSubst = '(at)', $spamProtectEmailAddresses_lastDotSubst = '.') {
        $this->setLink($link, $spamProtectEmailAddresses, $spamProtectEmailAddresses_atSubst, $spamProtectEmailAddresses_lastDotSubst);
    }

    protected function isEncryptionValid($link) {
        if (preg_match($this->getEncryptedEmailLinkPattern(), $link)) {
            return TRUE;
        }
        return FALSE;
    }

    public function getEncryptedEmailLinkPattern() {
        return $this->encryptedEmailLinkPattern;
    }

    /**
     *
     * @param int $n char to decrypt
     * @param int $start
     * @param int $end
     * @param int $offset encryption offset, set by spamProtectEmailAddresses 10,-10
     * @return string
     */
    private static function decryptCharcode($n, $start, $end, $offset) {
        $n = $n + $offset;
        if ($offset > 0 && $n > $end) {
            $n = $start + ($n - $end - 1);
        } else if ($offset < 0 && $n < $start) {
            $n = $end - ($start - $n - 1);
        }

        return chr($n);
    }

    /**
     * decrypts an string to get original email link
     *
     * @param string $enc encrypted emailadress link
     * @param int $offset encryption offset, set by spamProtectEmailAddresses 10,-10
     * @return string
     */
    private static function decryptLink($enc, $offset) {
        $offset *= -1;
        $dec = '';
        $len = strlen($enc);
        for ($i = 0; $i < $len; $i++) {
            $n = ord(substr($enc, $i, 1));
            if ($n >= 0x2B && $n <= 0x3A) {
                $dec .= self::decryptCharcode($n, 0x2B, 0x3A, $offset); // 0-9 . , - + / :
            } else if ($n >= 0x40 && $n <= 0x5A) {
                $dec .= self::decryptCharcode($n, 0x40, 0x5A, $offset); // A-Z @
            } else if ($n >= 0x61 && $n <= 0x7A) {
                $dec .= self::decryptCharcode($n, 0x61, 0x7A, $offset); // a-z
            } else {
                $dec .= substr($enc, $i, 1);
            }
        }

        return $dec;
    }
}