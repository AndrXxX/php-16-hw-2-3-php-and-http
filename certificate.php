<?php
session_start();
header('Content-Type: image/png');
$image = imagecreatetruecolor(500, 350) or die('Невозможно инициализировать GD поток');;
$imageBack = imagecreatefrompng('./certificate.png');
imagecopy($image, $imageBack, 0, 0, 0, 0, 500, 350);

$textColor = imagecolorallocate($image, 0, 0, 0);
$fontFile = './font.ttf';
if (!file_exists($fontFile)) {
    echo 'Файл шрифта не найден!';
    exit;
}
$finalMark = round(5 * $_SESSION['userScore'] / $_SESSION['maxScore']);
$textTestName = $_SESSION['testName'];
$textMark = $_SESSION['userName'] . ', Ваша оценка: ' . ($finalMark < 2 ? 2 : $finalMark) . ' (набрано ' .
    $_SESSION['userScore'] . ' баллов из ' . $_SESSION['maxScore'] . ').';
$textErrors = 'Допущено ошибок: ' . $_SESSION['errorCounts'] . '.';
$textDate = date('H:i   d.m.y');

imagettftext($image, 18, 0, 60, 140, $textColor, $fontFile, $textTestName);
imagettftext($image, 14, 0, 60, 170, $textColor, $fontFile, $textMark);
imagettftext($image, 14, 0, 60, 200, $textColor, $fontFile, $textErrors);
imagettftext($image, 12, 0, 340, 280, $textColor, $fontFile, $textDate);


imagepng($image);
imagedestroy($image);
imagedestroy($imageBack);
