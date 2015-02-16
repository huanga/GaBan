<?php
require './vendor/autoload.php';
use Imagine\Gd\Imagine;
use chiisana\GaBan\GaBan;

$fileA = './asset/8a9942bf2d805572a7e5a71f47d7d287.png';
$fileB = './asset/798cf4d485a42bbf32a7f8c3f097d292.png';

//$fileA = './asset/949cab1b9fc916e56f8521a0ad5bb904.jpg';
//$fileB = './asset/cfd71a9e304f735569e91de3a777e6ae.jpg';

//$fileA = './asset/example1.png';
//$fileB = './asset/example2.png';

//$fileA = './asset/Original.jpg';
//$fileB = './asset/NegativeExposure.jpg';


function levenshtein_distance($text1, $text2) {
    $len1 = strlen($text1);
    $len2 = strlen($text2);
    for($i = 0; $i <= $len1; $i++)
        $distance[$i][0] = $i;
    for($j = 0; $j <= $len2; $j++)
        $distance[0][$j] = $j;
    for($i = 1; $i <= $len1; $i++)
        for($j = 1; $j <= $len2; $j++)
            $distance[$i][$j] = min($distance[$i - 1][$j] + 1, $distance[$i][$j - 1] + 1, $distance[$i - 1][$j - 1] + ($text1[$i - 1] != $text2[$j - 1]));
    return $distance[$len1][$len2];
}

$imagine = new Imagine();
$gaban   = new GaBan($imagine, ['sectorsWide' => 6, 'sectorsHigh' => 10]);

$gaban->setImageFromPath($fileA);
$signatureA   = $gaban->getSignature();
$fingerprintA = $gaban->getHash();
$edgeDetectA  = $gaban->getEdgeDetection();

$gaban->setImageFromPath($fileB);
$signatureB   = $gaban->getSignature();
$fingerprintB = $gaban->getHash();
$edgeDetectB  = $gaban->getEdgeDetection();

echo "Signature A: " . $signatureA . "\r\n";
echo "Signature B: " . $signatureB . "\r\n";

//echo "Fingerprint A: " . json_encode($fingerprintA) . "\r\n";
//echo "Fingerprint B: " . json_encode($fingerprintB) . "\r\n";

if ($signatureA === $signatureB) {
    echo "Same signature" . "\r\n";
} else {
    var_dump(levenshtein($signatureA, $signatureB));
}


$difference = array();
foreach($fingerprintA as $index => $print) {
    if ($fingerprintB[$index] !== $print) {
        $difference[] = $index;
    }
}

//var_dump(serialize($fingerprintA));

$similarity = 1.00 - (count($difference) / count($fingerprintA));
echo "Similarity: " . $similarity . "\r\n";

echo "Edge detection A: " . $edgeDetectA . "\r\n";
echo "Edge detection B: " . $edgeDetectB . "\r\n";

if ($edgeDetectA === $edgeDetectB) {
    echo "Same edges" . "\r\n";
} else {
    var_dump(levenshtein_distance($edgeDetectA, $edgeDetectB));
}
