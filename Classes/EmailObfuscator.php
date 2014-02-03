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

require_once(t3lib_extMgm::extPath('emailobfuscator') . 'Classes/Obfuscator.php');
require_once(t3lib_extMgm::extPath('emailobfuscator') . 'Classes/EmailLink.php');

class Tx_Emailobfuscator extends EmailObfuscator {
}

class EmailObfuscator {

    private $content = '';

    private static $globalConf = array();
    private static $conf = array();

    const EMAILLINK_PATTERN = '#<a(.+?)href=[\'"]mailto:([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6})[\'"](.*?)>(.*?)</a>#i';

    const DEFAULT_TYPO3_ENCRYPT_PATTERN = '#<a(.+?)href=[\'"]javascript:linkTo_UnCryptMailto\(\'(.{1,})\'\);[\'"](.*?)>(.*?)</a>#i';

    /**
     * Initiate the EmailObfuscator.
     */
    public function init(&$parameters) {

        $this->content = $parameters['pObj']->content;

        self::$globalConf = $GLOBALS['TSFE']->config['config'];
        self::$conf = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['emailobfuscator']);

        // find alle mailto: matches
        preg_match_all(self::EMAILLINK_PATTERN, $this->content, $matches);

        foreach ($matches[0] as $link) {
            $obfuscator = new Obfuscator(new EmailLink($link));
            $this->content = str_replace($link, $obfuscator->obfuscate(), $this->content);
        }

        // find all default spam protected links
        if (self::isSpamProtectEmailAddressesEnabled() && self::isOverwriteSpamProtectEmailAddressesEnabled()) {
            if (!isset(self::$globalConf['spamProtectEmailAddresses_atSubst']) || self::$globalConf['spamProtectEmailAddresses_atSubst'] == '') {
                self::$globalConf['spamProtectEmailAddresses_atSubst'] = '(at)';
            }

            if (!isset(self::$globalConf['spamProtectEmailAddresses_lastDotSubst']) || self::$globalConf['spamProtectEmailAddresses_lastDotSubst'] == '') {
                self::$globalConf['spamProtectEmailAddresses_lastDotSubst'] = '.';
            }

            preg_match_all(self::DEFAULT_TYPO3_ENCRYPT_PATTERN, $this->content, $matches);

            $count = 0;
            foreach ($matches[0] as $link) {

                $newLink = str_replace(
                    array(
                        'javascript:linkTo_UnCryptMailto(\'' . $matches[2][$count] . '\');',
                        self::$globalConf['spamProtectEmailAddresses_atSubst'],
                        self::$globalConf['spamProtectEmailAddresses_lastDotSubst']
                    ),
                    array(
                        self::decryptLink($matches[2][$count], self::$globalConf['spamProtectEmailAddresses']),
                        '@',
                        '.'
                    ),
                    $link
                );
//                var_dump($link);
                $obfuscator = new Obfuscator(new EmailLink($newLink));
                $this->content = str_replace($link, $obfuscator->obfuscate(), $this->content);
                $count++;
            }

        }

        $parameters['pObj']->content = $this->content;
    }

    private static function isOverwriteSpamProtectEmailAddressesEnabled() {
        if (self::$conf['overwriteSpamProtectEmailAddresses'] == 1) {
            return TRUE;
        }

        return FALSE;
    }

    private function isSpamProtectEmailAddressesEnabled() {
        if (isset(self::$globalConf['spamProtectEmailAddresses'])
            && is_numeric(self::$globalConf['spamProtectEmailAddresses'])
            && self::$globalConf['spamProtectEmailAddresses'] != 0
        ) {
            return TRUE;
        }

        return FALSE;
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