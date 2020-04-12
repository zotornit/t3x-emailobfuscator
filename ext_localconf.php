<?php

defined('TYPO3_MODE') || die();

call_user_func(function() {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['tx_emailobfuscator']
        = \EMAILOBFUSCATOR\Emailobfuscator\Hook\ObfuscationHook::class . '->obfuscatePageContent';

    $GLOBALS['TYPO3_CONF_VARS']['LOG']['EMAILOBFUSCATOR']['Emailobfuscator']['Hook']['ObfuscationHook']['writerConfiguration'] = [
        \TYPO3\CMS\Core\Log\LogLevel::WARNING => [
            \EMAILOBFUSCATOR\Emailobfuscator\Log\Writer\SysLogDatabaseWriter::class => [],
        ],
    ];
});



