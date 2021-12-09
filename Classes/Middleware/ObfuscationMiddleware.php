<?php

namespace EMAILOBFUSCATOR\Emailobfuscator\Middleware;


use EMAILOBFUSCATOR\Emailobfuscator\Service\ObfuscationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class ObfuscationMiddleware
 * @package EMAILOBFUSCATOR\Emailobfuscator\Middleware
 *
 *
 * keep in mind:
 * TYPO3 default Middleware is kinda weired in the order how they get exectuted. since the internal
 * Middlewares do their stuff sometimes AFTER alle the other Middlewares did.
 * f.e. `AdminPanelRenderer` does ->handle() FIRST and then its own stuff
 * that makes the placement of our Middleware tricky
 *
 */
class ObfuscationMiddleware implements MiddlewareInterface
{

    /** @var ConfigurationManagerInterface  */
    private $configurationManager;
    /** @var ObfuscationService  */
    private $obfuscationService;
    /**
     * @var TimeTracker
     */
    protected $timeTracker;

    public function __construct()
    {
        $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $this->obfuscationService = GeneralUtility::makeInstance(ObfuscationService::class);
        $this->timeTracker = GeneralUtility::makeInstance(TimeTracker::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {



        $settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'emailobfuscator' //extkey
        );

        // return when, disabled
        if (!isset($settings['enabled']) || !boolval($settings['enabled'])) {
            return $handler->handle($request);
        }

        $response = $handler->handle($request);
        if (
            $GLOBALS['TSFE'] instanceof TypoScriptFrontendController
        ) {
            $body = $response->getBody();
            $body->rewind();
            $contents = $response->getBody()->getContents();

            if (!isset($settings['obfuscateEmailLinks']) || boolval($settings['obfuscateEmailLinks'])) {
                if (!isset($settings['patternEmailLinks']) || empty(trim($settings['patternEmailLinks']))) {
                    $contents = $this->obfuscationService->obfuscateEmailLinks($contents);
                } else {
                    $contents = $this->obfuscationService->obfuscateEmailLinks($contents, trim($settings['patternEmailLinks']));
                }
            }

            if (!isset($settings['obfuscatePlainEmails']) || boolval($settings['obfuscatePlainEmails'])) {
                if (!isset($settings['patternPlainEmails']) || empty(trim($settings['patternPlainEmails']))) {
                    $contents = $this->obfuscationService->obfuscatePlainEmails($contents);
                } else {
                    $contents = $this->obfuscationService->obfuscatePlainEmails($contents, trim($settings['patternPlainEmails']));
                }
            }



            $body = new Stream('php://temp', 'rw');
            $body->write($contents);
            $response = $response->withBody($body);
        }
        return $response;
    }
}
