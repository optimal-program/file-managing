# file-managing
Simple package for file manipulation.

## Image output settings

Uploaded images can be resized into maximum dimensions, saved in another format and with
required compression quality. Every setting is optional - `null` maximum dimension is not
limited, `null` extension keeps the format the image was uploaded in and `null` quality means
the default quality of the used image library.

```php
use Optimal\FileManaging\FileUploader;
use Optimal\FileManaging\Utils\ImageOutputSettings;

$uploader = FileUploader::getInstance();

// resize into 512x512 px, save as webp with quality 85
$uploader->setImageOutputSettings(new ImageOutputSettings(512, 512, 'webp', 85));

// the same written by separate setters
$uploader->setMaxImageWidth(512);
$uploader->setMaxImageHeight(512);
$uploader->setImageOutputFormat('webp', 85);
```

Images are only downscaled - a smaller image is never enlarged and its aspect ratio is always
kept, so the resulting image fits into the maximum dimensions. Default maximum dimensions of
the uploader are `FileUploader::DEFAULT_MAX_IMAGE_WIDTH` x `FileUploader::DEFAULT_MAX_IMAGE_HEIGHT`.

The same settings can be applied on any already existing image:

```php
use Optimal\FileManaging\ImagesManager;
use Optimal\FileManaging\Utils\ImageOutputSettings;

$imagesManager = new ImagesManager();
$imagesManager->setSourceDirectory('images/source');

$image = $imagesManager->loadImageManageResource('photo', 'png');
$image->applyOutputSettings(new ImageOutputSettings(1920, 1080, 'webp', 80), 'images/target');

// resource of the resulting image - with new extension, resolution and file size
$outputImage = $image->getOutputImageResource();
```

## Tests

```
composer install
php test/index.php
```
