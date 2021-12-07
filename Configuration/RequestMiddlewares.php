<?php

/**
 *  * keep in mind:
 * TYPO3 default Middleware is kinda weired in the order how they get exectuted. since the internal
 * Middlewares do their stuff sometimes AFTER alle the other Middlewares did.
 * f.e. `AdminPanelRenderer` does ->handle() FIRST and then its own stuff
 * that makes the placement of our Middleware tricky
 *
 */
return [
    'frontend' => [
        'zotornit/emailobfuscator/obfuscation-middleware' => [
            'target' => \EMAILOBFUSCATOR\Emailobfuscator\Middleware\ObfuscationMiddleware::class,
            'after' => [
                'typo3/cms-adminpanel/renderer',
            ],
            'before' => [
                'typo3/cms-adminpanel/data-persister',
            ],
        ],
    ],
];
