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

class tx_emailobfuscator
{

    private $conf; // Extension conf
    private $globalConf; // $GLOBALS['TSFE']->config['config']

    private $parameters; // Method parameter
    private $pObj; // Method parameter

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


    public function  __construct()
    {
        /*
         * Reads values from ext_conf_template.txt and saves them to $this->conf.
        */
        $this->conf = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['emailobfuscator']);
        $this->globalConf = $GLOBALS['TSFE']->config['config'];
    }


    /**
     * Initiate the obfuscator.
     *
     * @param Array $parameters
     * @param tslib_cObj $pObj
     */
    public function initEmailObfuscator(Array &$parameters, tslib_cObj &$pObj)
    {
        $this->parameters = $parameters;
        $this->pObj = $pObj;

        if ($this->isMailtoTypolink()) {

            $this->setLinkText($this->parameters['linktxt']);
            $this->setupHiddenParams();
            $this->setAdditionalATagParams();
            $this->undoDefaultSpamProtection();
            $this->execObfuscation();

            $parameters = $this->parameters;
            $pObj = $this->pObj;
        }
        unset($this->parameters);
        unset($this->pObj);
    }


    /*
     * returns TRUE if the given typolink is of TYPE mailto.
     */
    private function isMailtoTypolink()
    {
        if (isset($this->parameters['finalTagParts']['TYPE']) && $this->parameters['finalTagParts']['TYPE'] == 'mailto') {
            return TRUE;
        }
        return FALSE;
    }


    private function execNonJSObfuscation()
    {
        $noJavascriptPart = '<span class="tx-emailobfuscator-noscript">';

        $pieces = self::cutToPieces(self::removeMailto($this->parameters['finalTagParts']['url']));

        if (is_array($pieces)) {
            /*
             * @ and last . replace when spamProtectEmailAddresses_lastDotSubst and/or spamProtectEmailAddresses_atSubst is set with typoscript
            */
            $lastDotSubst_done = FALSE;
            for ($i = count($pieces) - 1; $i >= 0; $i--) {

                if (!$lastDotSubst_done && isset($this->globalConf['spamProtectEmailAddresses_lastDotSubst'])
                    && strlen($this->globalConf['spamProtectEmailAddresses_lastDotSubst']) > 0 && preg_match('/\.{1}/', $pieces[$i])
                ) {
                    $pieces[$i] = str_replace('.', $this->globalConf['spamProtectEmailAddresses_lastDotSubst'], $pieces[$i]);
                    $lastDotSubst_done = TRUE;
                }
                if ($lastDotSubst_done && isset($this->globalConf['spamProtectEmailAddresses_atSubst'])
                    && strlen($this->globalConf['spamProtectEmailAddresses_atSubst']) > 0 && preg_match('/@{1}/', $pieces[$i])
                ) {
                    $pieces[$i] = str_replace('@', $this->globalConf['spamProtectEmailAddresses_atSubst'], $pieces[$i]);
                    break;
                }
            }

            /*
             * generate output string using some random encryption and obfuscation
            */
            foreach ($pieces as $key => $value) {
                $noJavascriptPart .= $this->randomObfuscation($value);

            }
        }
        $noJavascriptPart .= '</span>';

        $this->appendToObfuscation($noJavascriptPart);
    }


    private function execJSObfuscation()
    {
        $javascriptURLPart = self::convertToJSWriteDocument($this->parameters['finalTagParts']['url']);
        $javascriptLinkPart = self::convertToJSWriteDocument($this->parameters['linktxt']);
        $this->appendToObfuscation(self::buildJavascript($javascriptURLPart, $javascriptLinkPart, $this->getAdditionalATagParams()));
    }

    /*
     * executes the obfusctaion for given typolink
    */
    private function execObfuscation()
    {
        $this->execNonJSObfuscation();
        $this->execJSObfuscation();
        $this->setParameter_finalTag($this->getObfuscation() . self::FINAL_TAG_CLOSER);
        $this->setObfuscation('');
        $this->setParameter_linktxt('');
    }

    private static function buildJavascript($url, $link, $additionalATagParams = '')
    {
        //var_dump($additionalATagParams );

        return '<script language=\'JavaScript\' type=\'text/javascript\'>'
        . 'var el = document.getElementsByClassName(\'tx-emailobfuscator-noscript\');'
        . 'for(var i = 0; i != el.length; i++) { el[i].style.display = \'none\';}'
        . 'document.write(\'<a\' + \' href="\');'
        . $url
        . 'document.write(\'" ' . (($additionalATagParams != '') ? $additionalATagParams : '') . '>\');'
        . $link
        . 'document.write(ecf+cef+cfe);</script>';
    }

    /**
     * converts a string to an javascript write document output
     * @param unknown_type $string
     * @return string
     */
    private static function convertToJSWriteDocument($string)
    {
        $javaVarArray = array();
        $javascriptDocumentWrite = 'document.write(';
        $javascriptVarDeclaration = '';
        $pieces = self::cutToPieces($string);
        $piecesCnt = count($pieces);
        for ($i = 0; $i <= $piecesCnt - 1; $i++) {
            $foundValidString = FALSE;
            while (!$foundValidString) {
                $rLength = mt_rand(2, 6);
                if (preg_match('/[a-zA-Z]{' . $rLength . '}/', $rstring = self::randomString($rLength))) {

                    $rstring = strtolower($rstring);
                    if (!in_array($rstring, $javaVarArray)) {
                        $javaVarArray[$i] = $rstring;
                        $foundValidString = TRUE;
                        $javascriptVarDeclaration .= 'var ' . $rstring . '=\'' . $pieces[$i] . '\';';
                        $javascriptDocumentWrite .= $rstring . '+';
                    }
                }
            }
        }

        $javascriptDocumentWrite .= '\'\');';
        return $javascriptVarDeclaration . $javascriptDocumentWrite;
    }


    /**
     * obfuscates an email address with some random methods
     * I am not very happy with this code. Change it later. NYI
     */
    public function randomObfuscation($string)
    {
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
            return self::wrapWithSpan(self::encryptUnicode($string)) . $this->createInvisibleTrashcode();
        }
    }

    /**
     * wraps a string with span tag
     * @param string $string
     * @return String
     */
    public static function wrapWithSpan($string)
    {
        return '<span>' . $string . '</span>';
    }


    /**
     * setup all availible hiddenParams and sets the CSS
     */
    private function setupHiddenParams()
    {

        $this->addHiddenParams(self::wrapArrayItems('class="', '"', $this->getAllowedSelectors()));
        $this->addAllowedSelectorsToCSSDefaultStyle();
    }


    /**
     * adds all allowed CSS selectors to the _CSS_DEFAULT_STYLE
     */
    private function addAllowedSelectorsToCSSDefaultStyle()
    {
        if (!isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE'])
            || trim($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE']) == ''
        ) {
            $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE'] = '';
            foreach ($this->getAllowedSelectors() as $k => $value) {
                $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE'] .= '.' . $value . '{display: none;}' . '\n';
            }
        }
    }


    private function addHiddenParams($params)
    {
        if (is_array($params)) {
            foreach ($params as $value) {
                $this->hiddenParams[] = $value;
            }
        }
    }


    private function getAllowedSelectors()
    {
        $allowedSelectors = array();
        if ($this->getConfVar('allowedCSSSelectors')) {
            $allowedSelectorsTemp = explode(",", $this->getConfVar('allowedCSSSelectors'));

            foreach ($allowedSelectorsTemp as $value) {
                $value = trim($value);
                if ($value != '') {
                    $allowedSelectors[] = $value;
                }
            }
        }
        return $allowedSelectors;
    }

    private static function wrapArrayItems($before, $after, $arr)
    {
        $newarr = array();
        if (is_array($arr)) {
            foreach ($arr as $k => $value) {
                $newarr[] = $before . $value . $after;
            }
        } else {
            return $arr;
        }
        return $newarr;
    }


    /**
     * creates random invisible trashcode
     *
     * @return string
     */
    private function createInvisibleTrashcode()
    {
        $trashTags = explode(',', trim($this->conf['allowedTrashcodeHTMLTags']));
        if (is_array($trashTags)) {
            $usedTag = trim($trashTags[(mt_rand(0, count($trashTags) - 1))]);
        } else {
            $usedTag = 'span';
        }
        return '<' . $usedTag . ' ' . $this->getHiddenParam() . ' >' . self::randomString(mt_rand(2, 5)) . '</' . $usedTag . '>';
    }


    /**
     * @return random hiddenParams
     */
    private function getHiddenParam()
    {
        return $this->hiddenParams[(mt_rand(0, count($this->hiddenParams) - 1))];
    }

    /**
     * generates a random string
     *
     * @param string $length
     * @return string
     */
    public static function randomString($length)
    {
        if (!($length < 22 && $length > 0)) {
            $length = 22;
        }

        do {
            $randomString = substr(base64_encode(pack('H*', md5(microtime()))), 0, $length);
        } while (in_array($randomString, self::$reveredJSWords));
        return $randomString;
    }


    /**
     * removes the 'mailto:' part in a string
     * @param String $string
     * @return String
     */
    public static function removeMailto($string)
    {
        return str_replace('mailto:', '', $string);
    }

    /**
     * Cuts a String into random pieces between 2 and 4 chars length
     *
     * @param String $string
     * @return Array
     */
    public static function cutToPieces($string)
    {
//        $nmbPieces = floor(strlen($string) / 3);
        $start = 0;
        do {
            $pieceLength = mt_rand(2, 4);
            $piece = substr($string, $start, $pieceLength);
            $start += $pieceLength;
            if ($piece != '') {
                $result[] = $piece;
            }
        } while ($piece != '');
        return $result;
    }


    /**
     * encrypts a string to unicode HTML chars
     *
     * @param String $string
     * @return String $result
     */
    private static function encryptUnicode($string)
    {
        $string = trim($string);
        $result = '';
        $stringLen = strlen($string);
        for ($i = 0; $i <= $stringLen - 1; $i++) {
            $result .= self::unicodeToHTML(substr($string, $i, 1));
        }
        return $result;
    }


    private static function unicodeToHTML($code)
    {
        return '&#' . ord($code) . ';';
    }

    /**
     * Undoes already parsed typolink for TYPE 'mailto' to get the default value for link and linktext.
     */
    private function undoDefaultSpamProtection()
    {
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

    private function isSpamProtectEmailAddressesEnabled()
    {
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
    private function matchFinalTag()
    {
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
    private function setParameter_finalTag($value = '')
    {
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
    private function setParameter_finalTagParts_url($value)
    {
        $this->parameters['finalTagParts']['url'] = trim($value);
        $this->pObj->lastTypoLinkUrl = trim($value);
        $this->setParameter_finalTag();
    }

    /**
     * sets $this->parameters['linktxt']
     *
     * @param String $value
     */
    private function setParameter_linktxt($value)
    {
        $this->parameters['linktxt'] = $value;
    }

    /*
     * replaces spamProtectEmailAddresses_lastDotSubst with .
    */
    private function remove_lastDotSubst()
    {
        $this->setLinkText(str_replace($this->globalConf['spamProtectEmailAddresses_lastDotSubst'], '.', $this->getLinkText()));
    }

    /*
     * replaces spamProtectEmailAddresses_atSubst with @
    */
    private function remove_atSubst()
    {
        $this->setLinkText(str_replace($this->globalConf['spamProtectEmailAddresses_atSubst'], '@', $this->getLinkText()));
    }

    /**
     *
     * @param char $n char to decrypt
     * @param int $start
     * @param int $end
     * @param int $offset encryption offset, set by spamProtectEmailAddresses 10,-10
     * @return string
     */
    private static function decryptCharcode($n, $start, $end, $offset)
    {
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
    private static function decryptLinkURL($enc, $offset)
    {
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

    private function getConfVar($variable)
    {
        if (isset($this->conf[$variable])) {
            return $this->conf[$variable];
        } else {
            return FALSE;
        }
    }


    private function setLinkText($string)
    {
        $this->linkText = $string;
        $this->setParameter_linktxt($string);
    }

    private function getLinkText()
    {
        return $this->linkText;
    }


    private function setLinkURL($string)
    {
        $this->linkURL = trim($string);
        $this->setParameter_finalTagParts_url($string);
    }

    private function getLinkURL()
    {
        return $this->linkURL;
    }


    private function getAdditionalATagParams()
    {
        return $this->additionalATagParams;
    }

    private function setAdditionalATagParams()
    {
        $result = $this->matchFinalTag();
        if (is_array($result) && isset($result[0]) && isset($result[1])) {
            $tmp = str_replace($result[0], '', $this->parameters['finalTag']);
            $tmp = str_replace(array('<a', 'href=""', '>'), '', $tmp);
            $this->additionalATagParams = trim($tmp);
        }
    }


    private function setObfuscation($string)
    {
        $this->obfuscation = $string;
    }

    private function appendToObfuscation($string)
    {

        $this->setObfuscation($this->getObfuscation() . $string);
    }

    private function prependToObfuscation($string)
    {
        $this->setObfuscation($string . $this->getObfuscation());
    }

    private function getObfuscation()
    {
        return $this->obfuscation;
    }
}

?>