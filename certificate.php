<?php
session_start();
header('Content-Type: image/png');
$image = imagecreatetruecolor(500, 350) or die('Невозможно инициализировать GD поток');;
$imageBack = imagecreatefrompng('./resources/certificate.png');
imagecopy($image, $imageBack, 0, 0, 0, 0, 500, 350);

$textColor = imagecolorallocate($image, 0, 0, 0);
$fontFile = './resources/font.ttf';
if (!file_exists($fontFile)) {
    echo 'Файл шрифта не найден!';
    exit;
}
$finalMark = round(5 * $_SESSION['userScore'] / $_SESSION['maxScore']);
$textTestName = $_SESSION['testName'];
$textMark = "%s , Ваша оценка: %s (набрано %s баллов из %s.)";
$textErrors = "Допущено ошибок: %s.";
$textDate = date('H:i   d.m.y');

imagettftext($image, (mb_strlen($textTestName) > 50 ? 12 : 14), 0, 60, 140, $textColor, $fontFile, $textTestName);
imagettftext($image, 14, 0, 60, 170, $textColor, $fontFile,
    sprintf($textMark, $_SESSION['userName'], ($finalMark < 2 ? 2 : $finalMark), $_SESSION['userScore'], $_SESSION['maxScore']));
imagettftext($image, 14, 0, 60, 200, $textColor, $fontFile, sprintf($textErrors, $_SESSION['errorCounts']));
imagettftext($image, 12, 0, 340, 280, $textColor, $fontFile, $textDate);


imagepng($image);
imagedestroy($image);
imagedestroy($imageBack);
