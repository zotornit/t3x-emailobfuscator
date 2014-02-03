<?php

class Tx_Emailobfuscator_Service_AddCSS extends AddCSS {
}

class AddCSS {

    private static $allowedCSSSelectors = array();
    private static $parseAllowedCSSSelectorsParsed = false;

    private static $conf = array();

    public function __construct() {

        self::$conf = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['emailobfuscator']);
        $this->parseAllowedCSSSelectors();
        $this->addAllowedSelectorsToCSSDefaultStyle();
    }

    public function init() {

    }

    /**
     * adds all allowed CSS selectors to the _CSS_DEFAULT_STYLE
     */
    private function addAllowedSelectorsToCSSDefaultStyle() {
        if (!isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE'])
            || trim($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE']) == ''
        ) {
            $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE'] = '';

            foreach (self::$allowedCSSSelectors as $cssSelector) {

                $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_emailobfuscator.']['_CSS_DEFAULT_STYLE'] .= '.' . $cssSelector . '{display: none;}' . PHP_EOL;
            }
        }

    }

    private function parseAllowedCSSSelectors() {
        /**
         * check for valid userinput on self::$conf['allowedCSSSelectors'])
         */
        if (!self::$parseAllowedCSSSelectorsParsed) {
            $userInputParts = explode(',', self::$conf['allowedCSSSelectors']);

            if (is_array($userInputParts)) {
                foreach ($userInputParts as $input) {
                    if (preg_match('/^-?[_a-zA-Z]+[_a-zA-Z0-9-]*$/i', $input)) {
                        self::$allowedCSSSelectors[] = mb_strtolower($input);
                    }
                }
                self::$parseAllowedCSSSelectorsParsed = true;
            }
        }
    }

    public static function getallowedCSSSelectors() {
        return self::$allowedCSSSelectors;
    }
}