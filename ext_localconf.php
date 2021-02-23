<?php

defined('TYPO3_MODE') || die();

call_user_func(function() {
    /*
     * This will process only cached elements
     */
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['tx_emailobfuscator']
        = \EMAILOBFUSCATOR\Emailobfuscator\Hook\ObfuscationHook::class . '->obfuscatePageContent';

    /*
     * Since for some reason TYPO3 processes *_INT elements (non-cached) after `contentPostProc-all` hook.
     * We need to hook into `end of frontend` to process the non-cached elements.
     * Seems comment `Hook for post-processing of page content cached/non-cached` for 'contentPostProc-all' in
     * `TypoScriptFrontendController` is not very accurate.
     */
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_eofe']['tx_emailobfuscator']
        = \EMAILOBFUSCATOR\Emailobfuscator\Hook\ObfuscationHook::class . '->obfuscatePageContent';

    $GLOBALS['TYPO3_CONF_VARS']['LOG']['EMAILOBFUSCATOR']['Emailobfuscator']['Hook']['ObfuscationHook']['writerConfiguration'] = [
        \TYPO3\CMS\Core\Log\LogLevel::WARNING => [
            \EMAILOBFUSCATOR\Emailobfuscator\Log\Writer\SysLogDatabaseWriter::class => [],
        ],
    ];
});



