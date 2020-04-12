<?php


namespace EMAILOBFUSCATOR\Emailobfuscator\Utilities;

/**
 * This class does all the heavy lifting with regexp
 *
 * Class ObfuscatorUtilities
 * @package EMAILOBFUSCATOR\Emailobfuscator\Utilities
 */
class ObfuscatorUtilities
{

//    /**
//     * Splits an email link into pieces:
//     *      <a href="mailto:tp@zotorn.de" class="maillink fast" title="Mail now!">send mail</a>
//     * becomes:
//     *      [
//     *          'body' = "send mail",
//     *          'parts' = [
//     *              'class' = 'maillink fast',
//     *              'title' = 'Mail now!',
//     *              'href' = "mailto:tp@zotorn.de"
//     *          ]
//     *      ]
//     *
//     * @param string $link
//     * @return array
//     */
//    public static function splitLink(string $link): array
//    {
//        $splitPattern = '/<\s*a([\s\S]*?)>([\s\S]*?)<\s*\/\s*a\s*>/i'; // everything in <a> tag and <a> tag's body
//        preg_match_all($splitPattern, $link, $matches);
//        preg_match_all('/([\s\S]+?)="([\s\S]+?)"/', $matches[1][0], $m); // <a> tag parts splitter
//        $mcount = count($m[0]);
//        for ($i = 0; $i < $mcount; $i++) {
//            $parts[trim($m[1][$i])] = str_replace(PHP_EOL, "", trim($m[2][$i]));
//        }
//        return [
//            'body' => trim($matches[2][0]),
//            'parts' => $parts
//        ];
//    }

    /**
     * Cuts a string into pieces between $min and $max (random) chars length
     *
     * @param String $string
     * @param int $min
     * @param int $max
     * @return array
     */
    public static function cutRandom(string $string, int $min = 2, int $max = 4): array
    {
        $result = [];
        $totalLength = mb_strlen($string);
        $index = 0;
        while ($index < $totalLength) {
            $splitIndexes[] = $index;
            $index += mt_rand($min, $max);
        }

        $siCount = count($splitIndexes);
        for ($i = 0; $i < $siCount; $i++) {
            if ($i + 1 < $siCount) { // not last piece
                $result[] = mb_substr($string, $splitIndexes[$i], $splitIndexes[$i + 1] - $splitIndexes[$i]);
            } else { // last piece, special case
                $result[] = mb_substr($string, $splitIndexes[$i], $totalLength - $splitIndexes[$i]);
            }
        }
        return $result;
    }

    /**
     * converts a string to an obfuscated javascript document.write();
     * @param String $string
     * @param float $weight amount% of trash code in result 0-1
     * @return string
     */
    public static function obfuscateToJavaScript(string $string, float $weight = 0.7): string
    {
        $output[] = '<script type="text/javascript">(function(p,u){for(i of u){document.write(p[i]);}})(';
        $pieces = self::cutRandom($string);
        $goodIndexIterator = 0;
        $goodIndexes = [];
        $walkIterator = 0;
        while ($walkIterator < count($pieces)) {
            if ((float)mt_rand() / (float)mt_getrandmax() < $weight) { // do fake
                $piecesToAdd[] = '\'' . self::randChars(3) . '\',';
                $goodIndexIterator++;
            } else { // do real;
                $piecesToAdd[] = '\'' . $pieces[$walkIterator++] . '\',';
                $goodIndexes[] = $goodIndexIterator++;
            }
        }
        $output[] = '[';
        $output[] = rtrim(implode("", $piecesToAdd), ',');
        $output[] = '],[';
        $output[] = rtrim(implode(",", $goodIndexes), ',');
        $output[] = ']';
        $output[] = ');';
        $output[] = '</script>';
        return implode("", $output);
    }

    private static function randChars(int $length)
    {
        if ($length < 1) return '';
        if ($length > 4) $length = 4; // safety check
        do {
            $randomString = strtolower(mb_substr(base64_encode(pack('H*', md5(microtime()))), 0, $length));
        } while (!preg_match('/^[a-z]{' . $length . '}$/', $randomString));
        return $randomString;
    }

    /**
     * converts a string to obfuscated html
     * @param String $input
     * @param float $weight probability a character gets replaces 0-1
     * @return string
     */
    public static function obfuscateToHTML(string $input, float $weight = 0.8): string
    {
        $output = [];
        foreach (self::cutRandom($input) as $piece) {
            if ((float)mt_rand() / (float)mt_getrandmax() < $weight) { // do encode
                $output[] = self::encryptUnicode($piece);
            } else {
                $output[] = $piece; // keep untouched
            }
        }

        return implode("", $output);
    }

    /**
     * encrypts a string to unicode HTML chars
     *
     * @param String $string
     * @return String $result
     */
    private static function encryptUnicode($string)
    {
        $result = [];
        for ($i = 0; $i <= mb_strlen($string) - 1; $i++) {
            $result[] = self::unicodeToHTML(mb_substr($string, $i, 1));
        }
        return implode("", $result);
    }

    private static function unicodeToHTML($code)
    {
        list(, $ord) = unpack('N', mb_convert_encoding($code, 'UCS-4BE', 'UTF-8'));
        return '&#' . ($ord) . ';';
    }

}
