<?php


namespace EMAILOBFUSCATOR\Emailobfuscator\Hook;


use EMAILOBFUSCATOR\Emailobfuscator\Service\ObfuscationService;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ObfuscationHook implements SingletonInterface
{

    public function obfuscatePageContent(&$parameters)
    {
        if (self::isSpamProtectEmailAddressesEnabled()) {
            // TODO When LTS8 support is dropped use new LTS 9+ method of getting the logger instance
            /** @var Logger $logger */
            $logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);
            $logger->warning(
                "emailobfuscator extension does not work when default config.spamProtectEmailAddresses is enabled. " .
                "Check your TypoScript config and remove/set 'config.spamProtectEmailAddresses = 0'. " .
                "Happened on page id #" . $GLOBALS['TSFE']->id, []
            );
            return;
        }

        /** @var ObfuscationService $service */
        $service = GeneralUtility::makeInstance(ObfuscationService::class);
        $parameters['pObj']->content = $service->obfuscateContent($parameters['pObj']->content);
    }

    private static function isSpamProtectEmailAddressesEnabled()
    {
        return isset($GLOBALS['TSFE']->config['config']['spamProtectEmailAddresses'])
            && is_numeric($GLOBALS['TSFE']->config['config']['spamProtectEmailAddresses'])
            && $GLOBALS['TSFE']->config['config']['spamProtectEmailAddresses'] != 0;
    }
}
