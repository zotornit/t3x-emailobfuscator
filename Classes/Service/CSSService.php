<?php

namespace EMAILOBFUSCATOR\Emailobfuscator\Service;

class CSSService
{

    private static $allowedCssSelectors = array();
    private static $parseAllowedCSSSelectorsParsed = FALSE;

    private static $allowedCssSelectorsAdded = FALSE;

    private static $conf = array();

    public function __construct() {

        self::$conf = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['emailobfuscator']);

        $this->parseAllowedCSSSelectors();

    }

    /**
     * adds all allowed CSS selectors to the _CSS_DEFAULT_STYLE
     */
    public function addAllowedSelectorsToCSSDefaultStyle() {
        if (!self::$allowedCssSelectorsAdded) {
            if (!isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE'])
                || trim($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE']) == ''
            ) {
                $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE'] = PHP_EOL;

                foreach (self::$allowedCssSelectors as $cssSelector) {

                    $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE'] .=
                        '.' . $cssSelector . ' {display: none;}' . PHP_EOL;
                }
            }
            self::$allowedCssSelectorsAdded = TRUE;
        }
    }

    private function parseAllowedCSSSelectors() {
        /**
         * check for valid userinput on self::$conf['allowedCSSSelectors'])
         */
        if (!self::$parseAllowedCSSSelectorsParsed) {
            $userInputParts = explode(',', self::$conf['allowedCSSSelectors']);
            $CSSSelectorPrefix = trim(self::$conf['CSSSelectorPrefix']);
            if (is_array($userInputParts)) {
                foreach ($userInputParts as $input) {
                    if ($CSSSelectorPrefix == '') {
                        $tempSelector = $input;
                    } else {
                        $tempSelector = $CSSSelectorPrefix . '-' . $input;
                    }

                    if (preg_match('/^-?[_a-zA-Z]+[_a-zA-Z0-9-]*$/i', $tempSelector)) {
                        self::$allowedCssSelectors[] = mb_strtolower($tempSelector);
                    }
                }
                self::$parseAllowedCSSSelectorsParsed = TRUE;
            }
        }
    }

    public static function getAllowedCssSelectors() {
        return self::$allowedCssSelectors;
    }
}
