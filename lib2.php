<?php
class Helpers
{
    public static function presentInCoverage($style, $coverageSelectors): bool
    {
        foreach ($coverageSelectors as $coverageSelector) {

            if ($coverageSelector === $style) {
                return true;
            }
        }
        return false;
    }
    public static function removePossibleCurlyBracesInComments($file) {
        preg_match_all('/\/\*.*?\*\//', $file, $matches);
        if ($matches[0]) {
            foreach ($matches[0] as $match) {
                $sanitized = str_replace(['{', '}'], '', $match);
                $file = str_replace($match, $sanitized, $file);
            }
        }

        return $file;
    }
}
class Css {
    public static function findInMediaAndReplace($input, $mediaName, $coverageSelectors): string
    {
        $log = '';
        $replaceFn = function ($matches) use ($mediaName, $coverageSelectors, &$log) {
            $item = [
                'media' => $mediaName,
                'selector' => trim(str_replace(",\n", ', ', $matches[1])),
            ];

            $log .= $item['media'] . '=>'. $item['selector']. "\n";

            if (Helpers::presentInCoverage($item, $coverageSelectors)) {

                return $matches[0];
            } else {
                return '';
            }
        };
        $replaced =  preg_replace_callback("/([^{}\/]+)\s+{[^{}]+}/", $replaceFn, $input);
        file_put_contents('./output/'. substr(md5($mediaName), 0, 5).'.txt', $log);
        return $replaced;
    }

    public static function parseAndReplace($input, $coverageSelectors): string
    {
        $resulting = [];
        preg_match_all("/@media([^{]+){([\s\S]+?})\s*}/", $input, $matches);
        foreach ($matches[0] as $key => $match) {
            $mediaName = trim($matches[1][$key]);
            $mediaBlock = $matches[2][$key];
            $mediaBlockProcessed = self::findInMediaAndReplace($mediaBlock, $mediaName, $coverageSelectors);
            echo mb_strlen($mediaBlock). ' '. mb_strlen($mediaBlockProcessed). "\n";
            $resulting[$mediaName] = $mediaBlockProcessed;
            $input = str_replace($match, '', $input);
        }
    //    print_r($resulting);
        $result = '';
        foreach ($resulting as $mediaName => $mediaBlock) {
            $result .= '@media '.$mediaName . " {\n". $mediaBlock . "\n}\n";
        }

        $result .= self::findInMediaAndReplace($input, '', $coverageSelectors);

        return $result;
    }

    public static function parseMedia($input): array
    {
        preg_match_all("/@media([^{]+)({)[\s\S]+?}\s*(})/ui", $input, $matches, PREG_OFFSET_CAPTURE);
        $result = [];

        if ($matches[1]) {
            foreach ($matches[1] as $index => $match) {
                $name = trim($match[0]);
                if (!isset($result[$name])) {
                    $result[$name] = [];
                }
                $result[$name][] = [$matches[2][$index][1], $matches[3][$index][1]];
            }
        }

        return $result;
    }

    public static function findMediaByRange($mediaList, $range): ?string
    {
        if ($mediaList) {
            foreach ($mediaList as $mediaName => $media) {
                foreach ($media as $mediaOccurrence) {
                    if ($range['start'] > $mediaOccurrence[0] && $range['start'] < $mediaOccurrence[1]) {
                        return $mediaName;
                    }
                }
            }
        }
        return '';
    }
}
class Coverage
{
    public static function parse($item): array
    {
        $result = [];
        $log = '';

        $mediaList = Css::parseMedia($item['text']);
        foreach ($item['ranges'] as $i => $range) {
            $text = mb_substr($item['text'], $range['start'], $range['end'] - $range['start']);
            $selector = trim(substr($text, 0, mb_strpos($text, '{')));
            $style = [
                'media' => Css::findMediaByRange($mediaList, $range),
                'selector' => str_replace(",\n", ', ', $selector),
            ];
            $result[] = $style;
            $log .= $style['media'] . '=>' . $style['selector'] . "\n";
        }
        file_put_contents('logs/coverage_parsed.txt', $log);

        return $result;
    }

    public static function getCssItem($reportFileContent): ?array
    {
        $json = json_decode($reportFileContent, true);
        foreach ($json as $item) {
            if (str_contains($item['url'], '.css') && str_contains($item['url'], Config::CSS_FILENAME_PART_IN_COVERAGE)) {
                file_put_contents('logs/index.css', $item['text']);
                return $item;
            }
        }
        return null;
    }
}
