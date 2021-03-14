<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "config.php";
include "lib2.php";
$i = 0;

$coverageFile = file_get_contents(Config::COVERAGE_FILENAME);
$cssItem = Coverage::getCssItem($coverageFile);
$coverageSelectors = Coverage::parse($cssItem);

$deltaSum = 0;
foreach (Config::CSS_FILENAME_LIST as $fileName) {
    $source = file_get_contents($fileName);
    $sourceLength = mb_strlen($source);
    $source = Helpers::removePossibleCurlyBracesInComments($source);
    $newSource = Css::parseAndReplace($source, $coverageSelectors);
   // file_put_contents('./output/'. basename($fileName), $newSource);
    file_put_contents($fileName, $newSource);
    $delta =  $sourceLength - mb_strlen($newSource);
    $deltaSum += $delta;
    echo $fileName .' '. round((mb_strlen($newSource)/$sourceLength)*100, 2) . "%\n";
}

echo 'Total '. round(($deltaSum/mb_strlen($cssItem['text']))*100, 2) . "%\n";



