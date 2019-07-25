<?php
require __DIR__ . '/../vendor/autoload.php';

use Optimal\FileManaging\ImagesManager;

$imgManager = new ImagesManager();


$imgManager->setDestination(__DIR__ . "/images");
$imgManager->setOutputDestination(__DIR__ . "/output");

$imgManager->setRelativeDestination();

$gdImage = $imgManager->loadGDImage("vydra", "jpg");

$gdImage->rotate(50);
$gdImage->resize(400, 300);

$gdImage->save();

/*
print_r(
    $vatCounter->getOne() . PHP_EOL
);*/