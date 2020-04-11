<?php

namespace EMAILOBFUSCATOR\Emailobfuscator;

class EmailObfuscator
{

    private $content = '';

    private static $globalConf = array();
    private static $conf = array();

    const EMAILLINK_PATTERN = '#<a([^<>]+?)href=[\'"]mailto:([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6})[\'"](.*?)>(.*?)</a>#i';
    const EMAIL_PLAIN_PATTERN = '#([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6})#i';

    public function init(&$parameters)
    {
        self::$globalConf = $GLOBALS['TSFE']->config['config'];

        if (self::isSpamProtectEmailAddressesEnabled()) {

            throw new \TYPO3\CMS\Core\Exception("emailobfuscator extension does not work when TYPO3 default spamProtectEmailAddresses is enabled. Check your TypoScript config and set 'config.spamProtectEmailAddresses = 0'");
        }

        $this->content = $parameters['pObj']->content;


        self::$conf = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['emailobfuscator']);

        // find all mailto: matches
        preg_match_all(self::EMAILLINK_PATTERN, $this->content, $matches);

        foreach ($matches[0] as $link) {
            $obf = new Obfuscator(new EmailLink($link));
            $this->content = str_replace($link, $obf->obfuscate(), $this->content);
        }


        if (self::$conf['convertPlainEmailAddresses']) {

            // find all plain email matches
            preg_match_all(self::EMAIL_PLAIN_PATTERN, $this->content, $matchesPlain);
            foreach ($matchesPlain[0] as $emailPlain) {
                $obf = new Obfuscator(new EmailPlain($emailPlain));
                $this->content = str_replace($emailPlain, $obf->obfuscate(), $this->content);
            }
        }


        $parameters['pObj']->content = $this->content;
    }

    private function isSpamProtectEmailAddressesEnabled()
    {
        return isset(self::$globalConf['spamProtectEmailAddresses'])
            && is_numeric(self::$globalConf['spamProtectEmailAddresses'])
            && self::$globalConf['spamProtectEmailAddresses'] != 0;
    }

}
