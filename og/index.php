<?php

define('WP_USE_THEMES', false); // We don't want to use themes.
require '../wp-load.php';
require './utils.inc.php';

$nocache = isset($_GET['nocache']);
$pid = intval($_GET['pid'] ?? 0);

if (!$pid) {
    exit;
}

$destination = realpath(__DIR__) . '/gen/' . $pid . '.jpg';

if (!$nocache && file_exists($destination)) {
    header('Content-Type: image/jpeg');
    readfile($destination);
}

$image = get_field('og_image', $pid);

if (!$image) {
    $image = wp_get_attachment_image_src(get_post_thumbnail_id($pid), 'large');
}

$url = $image['sizes']['large'] ?? $image[0] ?? false;
if (!$url) {
    exit;
}

$imagePath = urlToPathContent($url);

if (!file_exists($imagePath)) {
    exit;
}

$identite = get_field('site', 'option');
$watermarkPath = urlToPathContent($identite['filigrane']['url']);

if (!file_exists($watermarkPath)) {
    exit;
}

// Chargement des images
$sourceImage = imagecreatefromstring(file_get_contents($imagePath));
flou($sourceImage, 12);

$watermark = imagecreatefromstring(file_get_contents($watermarkPath));

// Dimensions du filigrane
$watermarkWidth = imagesx($watermark);
$watermarkHeight = imagesy($watermark);

// Création d'une nouvelle image avec les dimensions du filigrane
$newImage = imagecreatetruecolor($watermarkWidth, $watermarkHeight);

// Calcul des dimensions et position pour le centrage de l'image source
list($newWidth, $newHeight, $x, $y) = calculateImageSizeForCentering($sourceImage, $watermarkWidth, $watermarkHeight);

// Placement de l'image source au centre
imagecopyresampled($newImage, $sourceImage, $x, $y, 0, 0, $newWidth, $newHeight, imagesx($sourceImage), imagesy($sourceImage));

// Application du filigrane
imagecopy($newImage, $watermark, 0, 0, 0, 0, $watermarkWidth, $watermarkHeight);

// Envoi de l'image au navigateur
imagejpeg($newImage, $destination, 80);
header('Content-Type: image/jpeg');
imagejpeg($newImage, null, 80);

// Libération de la mémoire
imagedestroy($sourceImage);
imagedestroy($watermark);
imagedestroy($newImage);
