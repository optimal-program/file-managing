<?php declare(strict_types=1);

namespace Optimal\FileManaging\Resources;

use claviska\SimpleImage;
use Exception;
use Optimal\FileManaging\Exception\DeleteFileException;
use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\FileCommander;
use Optimal\FileManaging\Utils\ImageOutputSettings;

abstract class ImageManageResource
{
    protected BitmapImageFileResource $image;
    protected SimpleImage $simpleImage;
    protected FileCommander $commander;

    public function __construct(BitmapImageFileResource $image, FileCommander $commander)
    {
        $this->image = $image;
        $this->commander = $commander;
    }

    public function getSourceImageResource(): BitmapImageFileResource
    {
        return $this->image;
    }

    public function getOutputImageResource(): BitmapImageFileResource
    {
        return  clone($this->image);
    }

    /**
     * @throws DeleteFileException
     * @throws DirectoryNotFoundException
     */
    public function removeOriginal(): void
    {
        $this->commander->removeFile($this->image->getNameExtension());
    }

    /**
     * @throws Exception
     */
    public function autoRotate(): void
    {
        $this->simpleImage->autoOrient();
    }

    /**
     * Dimensions the image with given resolution has to be resized to, so it proportionally fits
     * into the maximum dimensions. Returns null, when the image already fits into them - smaller
     * image is never enlarged. Null maximum dimension is not limited.
     *
     * @return int[]|null [width, height]
     */
    protected function getMaxResizeDimensions(int $imageWidth, int $imageHeight, ?int $maxWidth = null, ?int $maxHeight = null): ?array
    {
        $ratio = 1.0;

        if (!is_null($maxWidth) && $imageWidth > $maxWidth) {
            $ratio = $maxWidth / $imageWidth;
        }

        if (!is_null($maxHeight) && $imageHeight > $maxHeight) {
            $ratio = min($ratio, $maxHeight / $imageHeight);
        }

        if ($ratio >= 1.0) {
            return null;
        }

        return [
            max(1, (int) round($imageWidth * $ratio)),
            max(1, (int) round($imageHeight * $ratio))
        ];
    }

    /**
     * Applies output settings on the image - resizes it into maximum dimensions and saves it
     * in required format with required quality.
     */
    public function applyOutputSettings(ImageOutputSettings $settings, ?string $myTarget = null, ?string $newName = null): void
    {
        if ($settings->isResolutionLimited()) {
            $this->maxResize($settings->getMaxWidth(), $settings->getMaxHeight());
        }

        $this->save($myTarget, $newName, $settings->getOutputExtension(), $settings->getQuality());
    }

    abstract public function cropImage(int $x, int $y, int $width, int $height): void;

    abstract public function maxResize(?int $maxWidth = null, ?int $maxHeight = null): void;

    abstract public function resize(?int $width = null, ?int $height = null): void;

    abstract public function rotate(int $degree): void;

    abstract public function show(): void;

    abstract public function save(?string $myTarget = null, ?string $newName = null, ?string $newExtension = null, ?int $quality = null): void;

}