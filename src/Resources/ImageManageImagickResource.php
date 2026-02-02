<?php declare(strict_types=1);

namespace Optimal\FileManaging\Resources;

use Imagick;
use ImagickException;
use Optimal\FileManaging\FileCommander;

final class ImageManageImagickResource extends ImageManageResource
{
    public Imagick $resource;

    /**
     * @throws ImagickException
     */
    public function __construct(BitmapImageFileResource $image, FileCommander $commander)
    {
        parent::__construct($image, $commander);
        $this->resource = new Imagick($image->getFilePath());
    }

    public function cropImage(int $x, int $y, int $width, int $height): void
    {
        // TODO: Implement cropImage() method.
    }

    public function maxResize(?int $maxWidth = null, ?int $maxHeight = null): void
    {
        // TODO: Implement maxResize() method.
    }

    public function resize(?int $width = null, ?int $height = null): void
    {
        // TODO: Implement resize() method.
    }

    public function rotate(int $degree): void
    {
        // TODO: Implement rotate() method.
    }

    public function show(): void
    {
        // TODO: Implement show() method.
    }

    public function save(?string $myTarget = null, ?string $newName = null, ?string $newExtension = null): void
    {
        // TODO: Implement save() method.
    }
}