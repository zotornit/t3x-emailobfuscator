<?php


namespace EMAILOBFUSCATOR\Emailobfuscator\Service;


use EMAILOBFUSCATOR\Emailobfuscator\Utilities\ObfuscatorUtilities;
use TYPO3\CMS\Core\SingletonInterface;

class ObfuscationService implements SingletonInterface
{
    function obfuscateContent(string $content): string
    {
        $content = $this->obfuscateEmailLinks($content);
        $content = $this->obfuscatePlainEmails($content);
        return $content;
    }

    function obfuscateEmailLinks(string $content): string
    {
        preg_match_all('/<a[.\s\S]*?href=[\'"]mailto:[.\s\S]*?<\s*\/\s*a\s*>/i', $content, $matches);
        foreach ($matches[0] ?? [] as $maillink) {
            $replace[] = $maillink;
            $with[] = ObfuscatorUtilities::obfuscateToJavaScript($maillink);
        }

        return str_replace($replace ?? [], $with ?? [], $content);
    }

    function obfuscatePlainEmails(string $content): string
    {
        preg_match_all('/[a-zA-Z.0-9-+]+@[a-zA-Z.0-9-]+/i', $content, $matches, PREG_SET_ORDER);
        foreach ($matches ?? [] as $match) {
            $replace[] = $match[0];
            $with[] = ObfuscatorUtilities::obfuscateToHTML($match[0]);
        }
        return str_replace($replace ?? [], $with ?? [], $content);
    }
}
