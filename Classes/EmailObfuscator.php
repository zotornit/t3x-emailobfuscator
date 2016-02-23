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
require_once(t3lib_extMgm::extPath('emailobfuscator') . 'Classes/EncryptedEmailLink.php');

class Tx_Emailobfuscator extends EmailObfuscator {
}

class EmailObfuscator {

    private $content = '';

    private static $globalConf = array();
    private static $conf = array();

    const EMAILLINK_PATTERN = '#<a(.+?)href=[\'"]mailto:([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6})[\'"](.*?)>(.*?)</a>#i';
    const DEFAULT_TYPO3_ENCRYPT_PATTERN = '#<a(.+?)href=[\'"]javascript:linkTo_UnCryptMailto\(\'(.{1,})\'\);[\'"](.*?)>(.*?)</a>#i';

    public function init(&$parameters) {

        $this->content = $parameters['pObj']->content;

        self::$globalConf = $GLOBALS['TSFE']->config['config'];
        self::$conf = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['emailobfuscator']);

        // find all mailto: matches
        preg_match_all(self::EMAILLINK_PATTERN, $this->content, $matches);

        foreach ($matches[0] as $link) {
            $obf = new Obfuscator(new EmailLink($link));
            $this->content = str_replace($link, $obf->obfuscate(), $this->content);
        }

        // find all default spam protected links
        if (self::isSpamProtectEmailAddressesEnabled() && self::isOverwriteSpamProtectEmailAddressesEnabled()) {

            preg_match_all(self::DEFAULT_TYPO3_ENCRYPT_PATTERN, $this->content, $matches);

            foreach ($matches[0] as $link) {
                $obf = new Obfuscator(
                    new EncryptedEmailLink(
                        $link,
                        $this->getspamProtectEmailAddresses(),
                        $this->getSpamProtectEmailAddresses_atSubst(),
                        $this->getSpamProtectEmailAddresses_lastDotSubst()
                    )
                );
                $this->content = str_replace($link, $obf->obfuscate(), $this->content);
            }
        }

        $parameters['pObj']->content = $this->content;
    }

    private function getSpamProtectEmailAddresses_atSubst() {
        if (!isset(self::$globalConf['spamProtectEmailAddresses_atSubst']) || self::$globalConf['spamProtectEmailAddresses_atSubst'] == '') {
            return '(at)';
        }
        return self::$globalConf['spamProtectEmailAddresses_atSubst'];
    }

    private function getSpamProtectEmailAddresses_lastDotSubst() {
        if (!isset(self::$globalConf['spamProtectEmailAddresses_lastDotSubst']) || self::$globalConf['spamProtectEmailAddresses_lastDotSubst'] == '') {
            return '.';
        }
        return self::$globalConf['spamProtectEmailAddresses_lastDotSubst'];
    }

    private function getspamProtectEmailAddresses() {
        return self::$globalConf['spamProtectEmailAddresses'];
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

}