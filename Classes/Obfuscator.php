<?php

namespace ZOTORN\EmailObfuscator;

mb_internal_encoding("UTF-8");

use ZOTORN\EmailObfuscator\Service\CSSService;

class Obfuscator
{

    private $emailLink;
    private $obfuscatedLink = '';

    private $emailPlainType = false;

    private static $allowedTrashcodeHTMLTags = array();
    private static $allowedTrashcodeHTMLTagsParsed = FALSE;

    private static $hiddenCSSHiddenSelectorsAdded = FALSE;

    private static $conf = array();
    private static $globalConf = array();
    private static $hiddenParams = array('style="display:none;"', 'style="display: none;"', 'style=\'display:none;\'', 'style=\'display: none;\'');

    private static $reservedJSWords = array('abstract', 'boolean', 'break', 'byte', 'case', 'catch', 'char', 'class', 'const',
        'continue', 'default', 'delete', 'do', 'double', 'else', 'export', 'extends', 'false', 'final', 'finally',
        'float', 'for', 'function', 'goto', 'if', 'implements', 'in', 'instanceof', 'int', 'long', 'native', 'new',
        'null', 'package', 'private', 'protected', 'public', 'return', 'short', 'static', 'super', 'switch',
        'synchronized', 'this', 'throw', 'throws', 'transient', 'true', 'try', 'typeof', 'undefined', 'var',
        'void', 'while', 'with',
    );

    public function __construct($emailLink) {

        self::$conf = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['emailobfuscator']);
        self::$globalConf = $GLOBALS['TSFE']->config['config'];

        if (!$emailLink instanceof EmailLink) {
            throw new InvalidArgumentException('Argument must be instance of EmailLink');
        }

        if ($emailLink instanceof EmailPlain) {
            $this->emailPlainType = true;
        }

        $this->emailLink = $emailLink;

        $this->parseAllowedTrashcodeHTMLTags();

        $this->addCSSHiddenSelectors();

    }

    private function addCSSHiddenSelectors() {
        if (!self::$hiddenCSSHiddenSelectorsAdded) {
            $cssParams = CSSService::getAllowedCssSelectors();

            if (count($cssParams) > 0) {
                foreach ($cssParams as $cssSelector) {
                    self::$hiddenParams[] = 'class="' . $cssSelector . '"';
                }
            }
            self::$hiddenCSSHiddenSelectorsAdded = TRUE;
        }
    }

    private function parseAllowedTrashcodeHTMLTags() {
        /**
         * check for valid userinput on self::$conf['allowedTrashcodeHTMLTags'])
         */
        if (!self::$allowedTrashcodeHTMLTagsParsed) {
            $userInputParts = explode(',', self::$conf['allowedTrashcodeHTMLTags']);

            if (is_array($userInputParts)) {
                foreach ($userInputParts as $input) {
                    if (preg_match('/^[a-z]{1,}$/i', $input)) {
                        self::$allowedTrashcodeHTMLTags[] = mb_strtolower($input);
                    }
                }
                self::$allowedTrashcodeHTMLTagsParsed = TRUE;
            }
        }
    }

    public function obfuscate() {
        // non javascriptStuff:
        $this->obfuscatedLink .= $this->obfuscateNonJavaScript();
        $this->obfuscatedLink .= $this->obfuscateJavascript();

        return $this->obfuscatedLink;
    }

    private function obfuscateJavascript() {
        if($this->emailPlainType) {
            $javascriptURLPart = self::convertToJSWriteDocument($this->emailLink->getEmail());
            return self::buildPlainEmailJavascript($javascriptURLPart);
        } else {
            $javascriptURLPart = self::convertToJSWriteDocument('mailto:' . $this->emailLink->getEmail());
            $javascriptLinkPart = self::convertToJSWriteDocument($this->emailLink->getLinkText());
            return self::buildJavascript($javascriptURLPart, $javascriptLinkPart, $this->emailLink->getPreHREF() . ' ' . $this->emailLink->getPostHREF());
        }
    }

    private static function buildPlainEmailJavascript($link) {
        self::isXHTMLEnabled();

        return '<script type=\'text/javascript\'>'
            . ((self::isXHTMLEnabled()) ? '/* <![CDATA[ */ ' : '')
//            . 'document.write(\'<a\' + \' href="\');'
//            . $url
//            . 'document.write(\'" ' . (($additionalATagParams != '') ? str_replace('\'', '\\\'', $additionalATagParams) : '') . '>\');'
            . $link
//            . 'document.write(endATag);'
            . ((self::isXHTMLEnabled()) ? '/* ]]> */' : '')
            . '</script>';
    }

    private static function buildJavascript($url, $link, $additionalATagParams = '') {
        self::isXHTMLEnabled();

        return '<script type=\'text/javascript\'>'
        . ((self::isXHTMLEnabled()) ? '/* <![CDATA[ */ ' : '')
        . 'document.write(\'<a\' + \' href="\');'
        . $url
        . 'document.write(\'" ' . (($additionalATagParams != '') ? str_replace('\'', '\\\'', $additionalATagParams) : '') . '>\');'
        . $link
        . 'document.write(endATag);'
        . ((self::isXHTMLEnabled()) ? '/* ]]> */' : '')
        . '</script>';
    }

    private static function isXHTMLEnabled() {
        if (preg_match('/^xhtml_[a-z0-9]{1,}$/i', self::$globalConf['doctype'])) {
            return TRUE;
        }

        return FALSE;
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
                $rLength = mt_rand(2, 4);
                $randomString = self::randomString($rLength);
                if (preg_match('/^[a-z]{' . $rLength . '}$/', $randomString)) {

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

        $piecesEmailLink = self::cutToPieces($this->emailLink->getEmail());
        $piecesLinkText = self::cutToPieces($this->emailLink->getLinkText());

        if (mb_strtolower($this->emailLink->getEmail()) != mb_strtolower($this->emailLink->getLinkText())) {
            if (is_array($piecesLinkText) && count($piecesLinkText) > 0) {
                foreach ($piecesLinkText as $linkTextPart) {
                    $noJavascriptPart .= $this->randomObfuscation($linkTextPart);
                }
            }
            if (!$this->emailPlainType) {
                $noJavascriptPart .= ' (';
            }
        }

        if (is_array($piecesEmailLink) && count($piecesEmailLink) > 0) {
            /*
             * @ and last . replace when spamProtectEmailAddresses_lastDotSubst and/or spamProtectEmailAddresses_atSubst is set with typoscript
            */
//            $lastDotSubst_done = FALSE;
//            for ($i = count($piecesEmailLink) - 1; $i >= 0; $i--) {
//
//                if (!$lastDotSubst_done && isset(self::$globalConf['spamProtectEmailAddresses_lastDotSubst'])
//                    && strlen(self::$globalConf['spamProtectEmailAddresses_lastDotSubst']) > 0 && preg_match('/\.{1}/', $piecesEmailLink[$i])
//                ) {
//                    $piecesEmailLink[$i] = str_replace('.', self::$globalConf['spamProtectEmailAddresses_lastDotSubst'], $piecesEmailLink[$i]);
//                    $lastDotSubst_done = TRUE;
//                }
//                if ($lastDotSubst_done && isset(self::$globalConf['spamProtectEmailAddresses_atSubst'])
//                    && strlen(self::$globalConf['spamProtectEmailAddresses_atSubst']) > 0 && preg_match('/@{1}/', $piecesEmailLink[$i])
//                ) {
//                    $piecesEmailLink[$i] = str_replace('@', self::$globalConf['spamProtectEmailAddresses_atSubst'], $piecesEmailLink[$i]);
//                    break;
//                }
//            }

            /*
             * generate output string using some random encryption and obfuscation
            */
            foreach ($piecesEmailLink as $linkTextPart) {
                $noJavascriptPart .= $this->randomObfuscation($linkTextPart);
            }
        }

        if (mb_strtolower($this->emailLink->getEmail()) != mb_strtolower($this->emailLink->getLinkText())) {
            if (!$this->emailPlainType) {
                $noJavascriptPart .= ')';
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

        /**
         * just unicode encryption, 50% of time
         */
        if ($mode <= 50) {
            return self::wrapWithSpan(self::encryptUnicode($string));
        }
        /**
         * just unicode encryption + additional invisible trashcode, 50% of time
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
        $result = '';
        $stringLen = mb_strlen($string);
        for ($i = 0; $i <= $stringLen - 1; $i++) {
            $result .= self::unicodeToHTML(mb_substr($string, $i, 1));
        }

        return $result;
    }

    /**
     * creates random invisible trashcode
     *
     * @return string
     */
    private static function createInvisibleTrashcode() {
        if (count(self::$allowedTrashcodeHTMLTags) > 0) {
            $usedTag = trim(self::$allowedTrashcodeHTMLTags[(mt_rand(0, count(self::$allowedTrashcodeHTMLTags) - 1))]);

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
        list(, $ord) = unpack('N', mb_convert_encoding($code, 'UCS-4BE', 'UTF-8'));

        return '&#' . ($ord) . ';';
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

//        var_dump($GLOBALS['TYPO3_LOADED_EXT']);

        do {
            $randomString = mb_substr(base64_encode(pack('H*', md5(microtime()))), 0, $length);
            $randomString = strtolower($randomString);

        } while (in_array($randomString, self::$reservedJSWords));

        return $randomString;
    }
}
