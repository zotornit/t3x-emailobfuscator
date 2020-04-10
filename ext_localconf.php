<?php

defined('TYPO3_MODE') || die();

call_user_func(function() {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['tx_emailobfuscator']
        = \EMAILOBFUSCATOR\Emailobfuscator\EmailObfuscator::class . '->init';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['tx_emailobfuscator']
        = \EMAILOBFUSCATOR\Emailobfuscator\Service\CSSService::class . '->addAllowedSelectorsToCSSDefaultStyle';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
        'page.includeJS.emailobfuscator = EXT:emailobfuscator/Resources/Public/JavaScript/emailobfuscator.js'
    );
});



