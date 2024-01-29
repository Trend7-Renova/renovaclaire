<?php
/**
 * Calcule les dimensions et la position pour ajuster et centrer l'image tout en préservant son rapport d'aspect.
 * Assure que les dimensions de l'image sont au moins égales à celles de la nouvelle image.
 *
 * @param resource $image Ressource de l'image
 * @param int $maxWidth Largeur minimale
 * @param int $maxHeight Hauteur minimale
 * @return array Dimensions et position (largeur, hauteur, x, y)
 */
function calculateImageSizeForCentering($image, $maxWidth, $maxHeight) {
    $srcWidth = imagesx($image);
    $srcHeight = imagesy($image);

    // Calcul du rapport d'aspect pour une mise à l'échelle où les deux dimensions sont au moins égales aux dimensions maximales
    $scaleWidth = $maxWidth / $srcWidth;
    $scaleHeight = $maxHeight / $srcHeight;
    $scale = max($scaleWidth, $scaleHeight);

    // Nouvelles dimensions
    $newWidth = $srcWidth * $scale;
    $newHeight = $srcHeight * $scale;

    // Calcul de la position pour centrer l'image
    $x = ($maxWidth - $newWidth) / 2;
    $y = ($maxHeight - $newHeight) / 2;

    return array($newWidth, $newHeight, $x, $y);
}

function flou($sourceImage, $intesite=5) {
    for ($i = 0; $i < $intesite; $i++) {
        imagefilter($sourceImage, IMG_FILTER_GAUSSIAN_BLUR);
    }
    
}



/**
 * Vérifie si un fichier est plus récent qu'une date donnée.
 *
 * @param string $filePath Chemin du fichier.
 * @param string $date Date donnée dans un format inconnu.
 * @return bool 
 */
function isFileMoreRecentThan($filePath, $date) {
    if(!file_exists($filePath)) return;
    $fileTime = filemtime($filePath);
    $time = strtotime($date);
    return $fileTime > $time;
}
