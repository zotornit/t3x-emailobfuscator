<?php

$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output']['tx_emailobfuscator']
    = \ZOTORN\EmailObfuscator\EmailObfuscator::class . '->init';

$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['tx_emailobfuscator']
    = \ZOTORN\EmailObfuscator\Service\CSSService::class . '->addAllowedSelectorsToCSSDefaultStyle';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
    'page.includeJS.emailobfuscator = EXT:emailobfuscator/Resources/Public/JavaScript/emailobfuscator.js'
);
