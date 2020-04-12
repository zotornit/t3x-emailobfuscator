<?php


namespace EMAILOBFUSCATOR\Emailobfuscator\Service;


use EMAILOBFUSCATOR\Emailobfuscator\Utilities\ObfuscatorUtilities;
use TYPO3\CMS\Core\SingletonInterface;

class ObfuscationService implements SingletonInterface
{

    function obfuscateEmailLinks(string $content, $pattern = '/<a[^>]*?href=[\'"]mailto:[.\s\S]*?<\s*\/\s*a\s*>/i'): string
    {
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        foreach ($matches ?? [] as $match) {
            $replace[] = $match[0];
            $with[] = ObfuscatorUtilities::obfuscateToJavaScript($match[0]);
        }

        return str_replace($replace ?? [], $with ?? [], $content);
    }

    function obfuscatePlainEmails(string $content, $pattern = '/[a-zA-Z.0-9-+]+@[a-zA-Z.0-9-]+/i'): string
    {
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        foreach ($matches ?? [] as $match) {
            $replace[] = $match[0];
            $with[] = ObfuscatorUtilities::obfuscateToHTML($match[0]);
        }
        return str_replace($replace ?? [], $with ?? [], $content);
    }
}
