<?php


namespace EMAILOBFUSCATOR\Emailobfuscator\Hook;


use EMAILOBFUSCATOR\Emailobfuscator\Service\ObfuscationService;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ObfuscationHook implements SingletonInterface
{

    public function obfuscatePageContent(&$parameters)
    {
        if (!($parameters['pObj'] instanceof TypoScriptFrontendController)) {
            return;
        }
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager */
        $configurationManager = $objectManager->get(ConfigurationManagerInterface::class);

        $settings = $configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'emailobfuscator' //extkey
        );

        // return when, disabled
        if (!isset($settings['enabled']) || !boolval($settings['enabled'])) {
            return;
        }

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
        if (!isset($settings['obfuscateEmailLinks']) || boolval($settings['obfuscateEmailLinks'])) {
            if (!isset($settings['patternEmailLinks']) || empty(trim($settings['patternEmailLinks']))) {
                $parameters['pObj']->content = $service->obfuscateEmailLinks($parameters['pObj']->content);
            } else {
                $parameters['pObj']->content = $service->obfuscateEmailLinks($parameters['pObj']->content, trim($settings['patternEmailLinks']));
            }
        }

        if (!isset($settings['obfuscatePlainEmails']) || boolval($settings['obfuscatePlainEmails'])) {
            if (!isset($settings['patternPlainEmails']) || empty(trim($settings['patternPlainEmails']))) {
                $parameters['pObj']->content = $service->obfuscatePlainEmails($parameters['pObj']->content);
            } else {
                $parameters['pObj']->content = $service->obfuscatePlainEmails($parameters['pObj']->content, trim($settings['patternPlainEmails']));
            }
        }
    }

    private static function isSpamProtectEmailAddressesEnabled()
    {
        return isset($GLOBALS['TSFE']->config['config']['spamProtectEmailAddresses'])
            && is_numeric($GLOBALS['TSFE']->config['config']['spamProtectEmailAddresses'])
            && intval($GLOBALS['TSFE']->config['config']['spamProtectEmailAddresses']) !== 0;
    }
}
