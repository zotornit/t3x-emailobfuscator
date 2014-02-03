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

    const EMAILLINK_PATTERN = '#<a(.+?)href=[\'"]mailto:([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6})[\'"](.*?)>(.*?)</a>#i';

    /**
     * Initiate the EmailObfuscator.
     */
    public function init(&$parameters, &$pObj) {

        $this->content = $parameters['pObj']->content;

        // find alle mailto: matches
        preg_match_all(self::EMAILLINK_PATTERN, $this->content, $matches);

        foreach ($matches[0] as $link) {
//            $emailLink = new EmailLink($link);
            $obfuscator = new Obfuscator(new EmailLink($link));
            $this->content = str_replace($link, $obfuscator->obfuscate(), $this->content);
        }

        $parameters['pObj']->content = $this->content;

    }

}

class EmailObfuscatorAlt {

    private $conf;
    private $globalConf;


    private $linkText; // Link Text between <a href..> and </a>
    private $linkURL; // URL in href=""
    private $additionalATagParams;

    private $obfuscation = '';

    private $hiddenParams = array('style="display:none;"', 'style="display: none;"', 'style=\'display:none;\'', 'style=\'display: none;\'');

    private static $reveredJSWords = array('abstract', 'boolean', 'break', 'byte', 'case', 'catch', 'char', 'class', 'const',
        'continue', 'default', 'delete', 'do', 'double', 'else', 'export', 'extends', 'false', 'final', 'finally',
        'float', 'for', 'function', 'goto', 'if', 'implements', 'in', 'instanceof', 'int', 'long', 'native', 'new',
        'null', 'package', 'private', 'protected', 'public', 'return', 'short', 'static', 'super', 'switch',
        'synchronized', 'this', 'throw', 'throws', 'transient', 'true', 'try', 'typeof', 'undefined', 'var',
        'void', 'while', 'with');

    const TYPO3_JS_ENCRYPTION_PATTERN = 'javascript:linkTo_UnCryptMailto\(\'(.*)\'\);'; // Pattern to find encrypted email
    const TYPO3_DEFAULT_ENCRYPTION_PATTERN = 'mailto:([\w\.@]*)'; // Pattern to find encrypted email

    const FINAL_TAG_CLOSER = '<a href="" style="display:none;">'; // necessary cause TYPO3 adds a closing </a> tag to the modified link at the end to prevent HTML open/closing tag erros.

    public function  __construct() {
        /*
         * Reads values from ext_conf_template.txt and saves them to $this->conf.
        */
        $this->conf = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['emailobfuscator']);
        $this->globalConf = $GLOBALS['TSFE']->config['config'];
    }


    private static function wrapArrayItems($before, $after, $arr) {
        $newarr = array();
        if (is_array($arr)) {
            foreach ($arr as $value) {
                $newarr[] = $before . $value . $after;
            }
        } else {
            return $arr;
        }

        return $newarr;
    }

    /**
     * Undoes already parsed typolink for TYPE 'mailto' to get the default value for link and linktext.
     */
    private function undoDefaultSpamProtection() {
        if ($this->isSpamProtectEmailAddressesEnabled()) {

            $result = $this->matchFinalTag();
            /*
             * make sure preg_match returns usefull result.
            */
            if (is_array($result) && isset($result[0]) && isset($result[1])) {
                $tmpLinkURL = self::decryptLinkURL($result[1], $this->globalConf['spamProtectEmailAddresses'] * -1);
                $this->setLinkURL($tmpLinkURL);
                $this->remove_lastDotSubst();
                $this->remove_atSubst();
                $this->setParameter_finalTagParts_url($tmpLinkURL);

            }
        }

    }

    private function isSpamProtectEmailAddressesEnabled() {
        if (isset($this->globalConf['spamProtectEmailAddresses']) && is_numeric($this->globalConf['spamProtectEmailAddresses'])
            && $this->globalConf['spamProtectEmailAddresses'] != 0
        ) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * simple preg_match for $this->parameters['finalTag']
     *
     * @return Array $result
     */
    private function matchFinalTag() {
        if (isset($this->globalConf['spamProtectEmailAddresses']) && $this->globalConf['spamProtectEmailAddresses'] != 0) {
            preg_match('/' . self::TYPO3_JS_ENCRYPTION_PATTERN . '/', $this->parameters['finalTag'], $result);
        } else {
            preg_match('/' . self::TYPO3_DEFAULT_ENCRYPTION_PATTERN . '/', $this->parameters['finalTag'], $result);
        }

        //var_dump($result);
        return $result;
    }

    /**
     * sets $this->parameters['finalTag']     *
     *
     */
    private function setParameter_finalTag($value = '') {
        if ($value == '') {
            $this->parameters['finalTag'] = '<a href="' . $this->parameters['finalTagParts']['url'] . '" ' . $this->getAdditionalATagParams() . ' >';
        } else {
            $this->parameters['finalTag'] = $value;
        }
    }

    /**
     * sets $this->parameters['finalTagParts']['url'] and $this->pObj->lastTypoLinkUrl
     *
     * @param String $value
     */
    private function setParameter_finalTagParts_url($value) {
        $this->parameters['finalTagParts']['url'] = trim($value);
        $this->pObj->lastTypoLinkUrl = trim($value);
        $this->setParameter_finalTag();
    }

    /**
     * sets $this->parameters['linktxt']
     *
     * @param String $value
     */
    private function setParameter_linktxt($value) {
        $this->parameters['linktxt'] = $value;
    }

    /*
     * replaces spamProtectEmailAddresses_lastDotSubst with .
    */
    private function remove_lastDotSubst() {
        $this->setLinkText(str_replace($this->globalConf['spamProtectEmailAddresses_lastDotSubst'], '.', $this->getLinkText()));
    }

    /*
     * replaces spamProtectEmailAddresses_atSubst with @
    */
    private function remove_atSubst() {
        $this->setLinkText(str_replace($this->globalConf['spamProtectEmailAddresses_atSubst'], '@', $this->getLinkText()));
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
    private static function decryptLinkURL($enc, $offset) {
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

    /*
     * Setters & Getters
    */

    private function getConfVar($variable) {
        if (isset($this->conf[$variable])) {
            return $this->conf[$variable];
        } else {
            return FALSE;
        }
    }

    private function setLinkText($string) {
        $this->linkText = $string;
        $this->setParameter_linktxt($string);
    }

    private function getLinkText() {
        return $this->linkText;
    }

    private function setLinkURL($string) {
        $this->linkURL = trim($string);
        $this->setParameter_finalTagParts_url($string);
    }

    private function getAdditionalATagParams() {
        return $this->additionalATagParams;
    }

    private function setAdditionalATagParams() {
        $result = $this->matchFinalTag();
        if (is_array($result) && isset($result[0]) && isset($result[1])) {
            $tmp = str_replace($result[0], '', $this->parameters['finalTag']);
            $tmp = str_replace(array('<a', 'href=""', '>'), '', $tmp);
            $this->additionalATagParams = trim($tmp);
        }
    }

    private function setObfuscation($string) {
        $this->obfuscation = $string;
    }

    private function appendToObfuscation($string) {

        $this->setObfuscation($this->getObfuscation() . $string);
    }

    private function getObfuscation() {
        return $this->obfuscation;
    }
}