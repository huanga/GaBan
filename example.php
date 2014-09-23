<?php
require './vendor/autoload.php';
use Imagine\Gd\Imagine;
use chiisana\GaBan\GaBan;

//$fileA = './asset/8a9942bf2d805572a7e5a71f47d7d287.png';
//$fileB = './asset/798cf4d485a42bbf32a7f8c3f097d292.png';

//$fileA = './asset/949cab1b9fc916e56f8521a0ad5bb904.jpg';
//$fileB = './asset/cfd71a9e304f735569e91de3a777e6ae.jpg';

$fileA = './asset/Original.jpg';
$fileB = './asset/Resized.jpg';

$imagine       = new Imagine();
$fingerprinter = new GaBan($imagine);

$fingerprintA   = $fingerprinter->getHashes($fileA);
$fingerprintB   = $fingerprinter->getHashes($fileB);

echo $fingerprintA['signature']['findimagedupes.pl'] . "\r\n";
echo $fingerprintB['signature']['findimagedupes.pl'] . "\r\n";

if ($fingerprintA['signature'] === $fingerprintB['signature']) {
    echo "Same signature" . "\r\n";

    $difference = array();
    foreach($fingerprintA['fingerprint'] as $index => $print) {
        if ($fingerprintB['fingerprint'][$index] !== $print) {
            $difference[] = $index;
        }
    }

    $similarity = 1.00 - (count($difference) / count($fingerprintA['fingerprint']));
    echo $similarity . "\r\n";
}