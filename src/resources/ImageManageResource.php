<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

use claviska\SimpleImage;
use Optimal\FileManaging\FileCommander;

abstract class ImageManageResource
{

    /** @var BitmapImageFileResource */
    protected $image;

    /** @var SimpleImage */
    protected $simpleImage;

    /** @var FileCommander */
    protected $commander;

    /**
     * ImageManageResource constructor.
     * @param BitmapImageFileResource $image
     * @param FileCommander $commander
     */
    public function __construct(BitmapImageFileResource $image, FileCommander $commander)
    {
        $this->image = $image;
        $this->commander = $commander;
    }

    /**
     * @return BitmapImageFileResource
     */
    public function getSourceImageResource(): BitmapImageFileResource
    {
        return $this->image;
    }

    /**
     * @return BitmapImageFileResource
     */
    public function getOutputImageResource(): BitmapImageFileResource
    {
        return  clone($this->image);
    }

    /**
     * @throws \Optimal\FileManaging\Exception\DeleteFileException
     * @throws \Optimal\FileManaging\Exception\DirectoryNotFoundException
     */
    public function removeOriginal(): void
    {
        $this->commander->removeFile($this->image->getNameExtension());
    }

    public function autoRotate(): void
    {
        $this->simpleImage->autoOrient();
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     */
    abstract public function cropImage(int $x, int $y, int $width, int $height): void;

    abstract public function maxResize(int $maxWidth = null, int $maxHeight = null): void;

    abstract public function resize(?int $width = null, ?int $height = null): void;

    abstract public function rotate(int $degree): void;

    abstract public function show(): void;

    abstract public function save(?string $myTarget = null, ?string $newName = null, ?string $newExtension = null): void;

}