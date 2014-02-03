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

mb_internal_encoding("UTF-8");

class Tx_Obfuscator_EmailLink extends Obfuscator {

}

class Obfuscator {

//    public function obfuscate() {
//        // non javascriptStuff:
//        $this->generateNonJSObfuscation();
//
//        return $this->obfuscatedLink;
//    }

    private $emailLink;
    private $obfuscatedLink = '';

    private static $conf = array();
    private static $globalConf = array();
    private static $hiddenParams = array('style="display:none;"', 'style="display: none;"', 'style=\'display:none;\'', 'style=\'display: none;\'');

    private static $reservedJSWords = array('abstract', 'boolean', 'break', 'byte', 'case', 'catch', 'char', 'class', 'const',
        'continue', 'default', 'delete', 'do', 'double', 'else', 'export', 'extends', 'false', 'final', 'finally',
        'float', 'for', 'function', 'goto', 'if', 'implements', 'in', 'instanceof', 'int', 'long', 'native', 'new',
        'null', 'package', 'private', 'protected', 'public', 'return', 'short', 'static', 'super', 'switch',
        'synchronized', 'this', 'throw', 'throws', 'transient', 'true', 'try', 'typeof', 'undefined', 'var',
        'void', 'while', 'with');

    public function __construct($emailLink) {

        self::$conf = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['emailobfuscator']);
        self::$globalConf = $GLOBALS['TSFE']->config['config'];

        if (!$emailLink instanceof EmailLink) {
            throw new InvalidArgumentException('Argument must be instance of EmailLink');
        }

        $this->emailLink = $emailLink;
    }

    public function obfuscate() {
        // non javascriptStuff:
//        $this->obfuscatedLink .= $this->obfuscateNonJavaScript();
        $this->obfuscatedLink .= $this->obfuscateJavascript();
        return $this->obfuscatedLink;
    }

    private function obfuscateJavascript() {
        $javascriptURLPart = self::convertToJSWriteDocument('mailto:' . $this->emailLink->getEmail());
        $javascriptLinkPart = self::convertToJSWriteDocument($this->emailLink->getLinkText());
        return self::buildJavascript($javascriptURLPart, $javascriptLinkPart, $this->emailLink->getPreHREF() . ' ' . $this->emailLink->getPostHREF());
    }

    private static function buildJavascript($url, $link, $additionalATagParams = '') {
        return '<script type=\'text/javascript\'>'
        . 'var el = document.getElementsByClassName(\'tx-emailobfuscator-noscript\');'
        . 'for(var i = 0; i != el.length; i++) { el[i].style.display = \'none\';};'
        . 'document.write(\'<a\' + \' href="\');'
        . $url
        . 'document.write(\'" ' . (($additionalATagParams != '') ? str_replace('\'', '\\\'', $additionalATagParams) : '') . '>\');'
        . $link
        . 'document.write(ecf+cef+cfe);'
        . '</script>';
    }

    /**
     * converts a string to an javascript write document output
     * @param String $string
     * @return string
     */
    private static function convertToJSWriteDocument($string) {
        $usedRandomStrings = array();
        $javascriptDocumentWrite = 'document.write(';
        $javascriptVarDeclaration = '';
        $pieces = self::cutToPieces($string);
        $piecesCnt = count($pieces);

        for ($i = 0; $i < $piecesCnt; $i++) {
            $foundValidString = FALSE;
            while (!$foundValidString) {
                $rLength = mt_rand(2, 6);
                $randomString = self::randomString($rLength);
                if (preg_match('/[a-zA-Z]{' . $rLength . '}/', $randomString)) {

                    $randomString = strtolower($randomString);
                    if (!in_array($randomString, $usedRandomStrings)) {
                        $usedRandomStrings[] = $randomString;
                        $foundValidString = TRUE;

                        $javascriptVarDeclaration .= 'var ' . $randomString . '=\'' . $pieces[$i] . '\';';
                        $javascriptDocumentWrite .= $randomString . '+';
                    }
                }
            }
        }

        $javascriptDocumentWrite .= '\'\');';
        return $javascriptVarDeclaration . $javascriptDocumentWrite;
    }

    public function obfuscateNonJavaScript() {
        $noJavascriptPart = '<span class="tx-emailobfuscator-noscript">';

        $pieces = self::cutToPieces($this->emailLink->getEmail());

        if (is_array($pieces) && count($pieces) > 0) {
            /*
             * @ and last . replace when spamProtectEmailAddresses_lastDotSubst and/or spamProtectEmailAddresses_atSubst is set with typoscript
            */
            $lastDotSubst_done = FALSE;
            for ($i = count($pieces) - 1; $i >= 0; $i--) {

                if (!$lastDotSubst_done && isset(self::$globalConf['spamProtectEmailAddresses_lastDotSubst'])
                    && strlen(self::$globalConf['spamProtectEmailAddresses_lastDotSubst']) > 0 && preg_match('/\.{1}/', $pieces[$i])
                ) {
                    $pieces[$i] = str_replace('.', self::$globalConf['spamProtectEmailAddresses_lastDotSubst'], $pieces[$i]);
                    $lastDotSubst_done = TRUE;
                }
                if ($lastDotSubst_done && isset(self::$globalConf['spamProtectEmailAddresses_atSubst'])
                    && strlen(self::$globalConf['spamProtectEmailAddresses_atSubst']) > 0 && preg_match('/@{1}/', $pieces[$i])
                ) {
                    $pieces[$i] = str_replace('@', self::$globalConf['spamProtectEmailAddresses_atSubst'], $pieces[$i]);
                    break;
                }
            }

            /*
             * generate output string using some random encryption and obfuscation
            */
            foreach ($pieces as $value) {
                $noJavascriptPart .= $this->randomObfuscation($value);

            }
        }
        $noJavascriptPart .= '</span>';

        return $noJavascriptPart;
    }

    /**
     * Cuts a String into random pieces between 2 and 4 chars length
     *
     * @param String $string
     * @return Array
     */
    public static function cutToPieces($string) {
        $result = array();

        while (mb_strlen($string) >= 2) {
            $pieceLength = mt_rand(2, (mb_strlen($string) > 4) ? 4 : mb_strlen($string));
            $result[] = mb_substr($string, 0, $pieceLength);
            $string = mb_substr($string, $pieceLength);

        }

        if (mb_strlen($string) > 0) {
            $result[] = $string;
        }

        return $result;
    }

    /**
     * obfuscates an email address with some random methods
     * I am not very happy with this code. Change it later. NYI
     */
    private static function randomObfuscation($string) {
        $mode = mt_rand(1, 100);

        /*
         * no encryption, leave it blank 15% of time
        */
        if ($mode <= 15) {
            return self::wrapWithSpan($string);
        } /*
		 * just unicode encryption, 25% of time
		*/
        elseif ($mode > 15 && $mode <= 40) {
            return self::wrapWithSpan(self::encryptUnicode($string));
        } /*
		 * just unicode encryption + additional inivisible trashcode, 45% of time
		*/
        else {
            return self::wrapWithSpan(self::encryptUnicode($string)) . self::createInvisibleTrashcode();
        }
    }

    /**
     * wraps a string with span tag
     * @param string $string
     * @return String
     */
    public static function wrapWithSpan($string) {
        return '<span>' . $string . '</span>';
    }

    /**
     * encrypts a string to unicode HTML chars
     *
     * @param String $string
     * @return String $result
     */
    private static function encryptUnicode($string) {
        $string = trim($string);
        $result = '';
        $stringLen = strlen($string);
        for ($i = 0; $i <= $stringLen - 1; $i++) {
            $result .= self::unicodeToHTML(substr($string, $i, 1));
        }
        return $result;
    }

    /**
     * creates random invisible trashcode
     *
     * @return string
     */
    private static function createInvisibleTrashcode() {
        $trashTags = explode(',', trim(self::$conf['allowedTrashcodeHTMLTags']));
        if (is_array($trashTags)) {
            $usedTag = trim($trashTags[(mt_rand(0, count($trashTags) - 1))]);
        } else {
            $usedTag = 'span';
        }
        return '<' . $usedTag . ' ' . self::getHiddenParam() . ' >' . self::randomString(mt_rand(2, 5)) . '</' . $usedTag . '>';
    }

    /**
     * @return String hiddenParams
     */
    private static function getHiddenParam() {
        return self::$hiddenParams[(mt_rand(0, count(self::$hiddenParams) - 1))];
    }

    private static function unicodeToHTML($code) {
        return '&#' . ord($code) . ';';
    }

    /**
     * generates a random string
     *
     * @param int $length
     * @return String
     */
    public static function randomString($length = 22) {
        if (!($length < 22 && $length > 0)) {
            $length = 22;
        }

        do {
            $randomString = mb_substr(base64_encode(pack('H*', md5(microtime()))), 0, $length);
            $randomString = strtolower($randomString);

        } while (in_array($randomString, self::$reservedJSWords));
        return $randomString;
    }
}



