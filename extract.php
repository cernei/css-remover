<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "config.php";
include "lib2.php";
$i = 0;

function parseCovered($item): array
{
    $result = [];
    $log = '';

    $mediaList = Css::parseMedia($item['text']);
    foreach ($item['ranges'] as $i => $range) {
        $text = mb_substr($item['text'], $range['start'], $range['end'] - $range['start']);
        $mediaName = Css::findMediaByRange($mediaList, $range);
        if (!isset($result[$mediaName])) {
            $result[$mediaName] = [];
        }
        $result[$mediaName][] = $text;
    }
    file_put_contents('logs/coverage_parsed.txt', $log);

    return $result;
}
$coverageFile = file_get_contents('./63.4%.json');
$cssItem = Coverage::getCssItem($coverageFile);
$covered = parseCovered($cssItem);
$content = '@charset "UTF-8";' . "\n";
foreach ($covered as $mediaName => $styles) {
    if ($mediaName) {
        $content .= "\n" . '@media ' . $mediaName . ' {' . "\n";
    }
    foreach ($styles as $style) {
        $content .= "\n". $style . "\n";
    }
    if ($mediaName) {
        $content .= "\n}\n";
    }
}
file_put_contents('logs/coverage_parsed.css', $content);

echo $content;
//print_r($coverageSelectors);
