<?php

$SECURIMAGE_PATH = __DIR__ . '/packages/vendor/dapphp/securimage/';

require $SECURIMAGE_PATH . 'securimage.php';

$IMAGE_WIDTH = 250;
$IMAGE_HEIGHT = 80;
$IMAGE_RATIO = 2.75;
$IMAGE_MIN_WIDTH = 132;
$IMAGE_MIN_HEIGHT = 48;

$backgrounds = ['bg3.jpg', 'bg4.jpg'];

$SI = new Securimage();

$namespace = (string) filter_input(INPUT_GET, 'ns');
$namespace && $SI->setNamespace($namespace);

// @link https://www.phpcaptcha.org/documentation/customizing-securimage/

$imgWidth  = filter_input(INPUT_GET, 'w', FILTER_VALIDATE_INT);
$imgHeight = filter_input(INPUT_GET, 'h', FILTER_VALIDATE_INT);

if ($imgWidth && $imgHeight) {
    $SI->image_width = max($imgWidth, $IMAGE_MIN_WIDTH);
    $SI->image_height = max($imgHeight, $IMAGE_MIN_HEIGHT);
} elseif ($imgWidth) {
    $SI->image_width = max($imgWidth, $IMAGE_MIN_WIDTH);
    $SI->image_height = (int) ($SI->image_width / $IMAGE_RATIO);
} elseif ($imgHeight) {
    $SI->image_height = max($imgHeight, $IMAGE_MIN_HEIGHT);
    $SI->image_width = (int) ($SI->image_height * $IMAGE_RATIO);
} else {
    $SI->image_width = $IMAGE_WIDTH;
    $SI->image_height = $IMAGE_HEIGHT;
}

$SI->text_color = new Securimage_Color(rand(0, 128), rand(0, 128), rand(0, 128));

$SI->num_lines = 0;

$SI->show($SECURIMAGE_PATH . 'backgrounds' . DIRECTORY_SEPARATOR . $backgrounds[ array_rand($backgrounds) ]);
