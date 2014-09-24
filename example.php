<?php
require './vendor/autoload.php';
use Imagine\Gd\Imagine;
use chiisana\GaBan\GaBan;

$fileA = './asset/8a9942bf2d805572a7e5a71f47d7d287.png';
$fileB = './asset/798cf4d485a42bbf32a7f8c3f097d292.png';

//$fileA = './asset/949cab1b9fc916e56f8521a0ad5bb904.jpg';
//$fileB = './asset/cfd71a9e304f735569e91de3a777e6ae.jpg';

//$fileA = './asset/Original.jpg';
//$fileB = './asset/Resized.jpg';

$imagine = new Imagine();
$gaban   = new GaBan($imagine);

$gaban->setImageFromPath($fileA);
$signatureA   = $gaban->getSignature();
$fingerprintA = $gaban->getHash();

$gaban->setImageFromPath($fileB);
$signatureB   = $gaban->getSignature();
$fingerprintB = $gaban->getHash();

echo "Signature A: " . $signatureA . "\r\n";
echo "Signature B: " . $signatureB . "\r\n";


if ($signatureA === $signatureB) {
    echo "Same signature" . "\r\n";

    $difference = array();
    foreach($fingerprintA as $index => $print) {
        if ($fingerprintB[$index] !== $print) {
            $difference[] = $index;
        }
    }

    $similarity = 1.00 - (count($difference) / count($fingerprintA));
    echo "Similarity: " . $similarity . "\r\n";
}