<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

use claviska\SimpleImage;
use Optimal\FileManaging\Exception\GDException;
use Optimal\FileManaging\FileCommander;

final class ImageManageGDResource extends ImageManageResource
{

    /**
     * ImageManageGDResource constructor.
     * @param BitmapImageFileResource $image
     * @param FileCommander $commander
     * @throws \Exception
     */
    public function __construct(BitmapImageFileResource $image, FileCommander $commander)
    {
        parent::__construct($image, $commander);
        $this->simpleImage = new SimpleImage($this->image->getFilePath());
    }

    /**
     * @return SimpleImage
     */
    public function getSimpleImage(): SimpleImage
    {
        return $this->simpleImage;
    }

    /**
     * @param SimpleImage $simpleImage
     */
    public function setSimpleImage(SimpleImage $simpleImage): void
    {
        $this->simpleImage = $simpleImage;
    }

    /**
     * @param int $degree
     * @throws GDException
     */
    public function rotate(int $degree): void
    {
        if (isset($degree) && is_numeric($degree)) {
            $this->simpleImage->rotate($degree);
        }
        else {
            throw new GDException("No degree defined!");
        }
    }

    /**
     * @param int|null $maxWidth
     * @param int|null $maxHeight
     */
    public function maxResize(int $maxWidth = null, int $maxHeight = null): void
    {
        $imgWidth = $this->simpleImage->getWidth();
        $imgHeight = $this->simpleImage->getHeight();

        if ($imgWidth > $maxWidth || $imgHeight > $maxHeight) {

            if ($imgWidth >= $imgHeight) {
                $this->simpleImage->resize($maxWidth);
            }
            else {
                $this->simpleImage->resize(null, $maxWidth);
            }

        }

        $this->image->setWidth($this->simpleImage->getWidth());
        $this->image->setHeight($this->simpleImage->getHeight());
    }

    /**
     * @param int|null $width
     * @param int|null $height
     */
    public function resize(?int $width = null, ?int $height = null): void
    {
        $this->simpleImage->resize($width, $height);
        $this->image->setWidth($this->simpleImage->getWidth());
        $this->image->setHeight($this->simpleImage->getHeight());
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     */
    public function cropImage(int $x, int $y, int $width, int $height): void
    {
        $this->simpleImage->crop($x, $y, $x + $width, $y + $height);
    }

    public function show(): void
    {
        $this->simpleImage->toScreen();
    }

    /**
     * @param string|null $myTarget
     * @param string|null $newName
     * @param string|null $newExtension
     * @throws \Optimal\FileManaging\Exception\DeleteFileException
     * @throws \Optimal\FileManaging\Exception\DirectoryNotFoundException
     * @throws \Optimal\FileManaging\Exception\FileException
     */
    public function save(?string $myTarget = null, ?string $newName = null, ?string $newExtension = null): void
    {

        $sameNameInSameDir = false;

        if ((is_null($newName) || $this->image->getName() === $newName) && $this->image->getFileDirectoryPath() === $myTarget) {
            $sameNameInSameDir = true;
        }

        $pom = "";
        if ($sameNameInSameDir && $this->image->getFileDirectoryPath() === $myTarget) {
            $pom = "_";
        }

        if (!is_null($myTarget)) {
            $this->commander->setPath($myTarget);
        }
        else {
            $this->commander->setPath($this->image->getFileDirectoryPath());
        }

        if (!is_null($newName)) {
            $filesWithSameName = $this->commander->searchImages($newName);
        }
        else {
            $filesWithSameName = $this->commander->searchImages($this->image->getName());
        }

        if (!empty($filesWithSameName)) {
            foreach ($filesWithSameName as $file) {
                if ($file->getExtension() !== $this->image->getExtension()) {
                    $this->commander->removeFile($file->getNameExtension());
                }
            }
        }

        $extension = $newExtension ?? $this->image->getExtension();
        $name = $newName ?? $this->image->getName();

        $fileDestination = $this->commander->getAbsolutePath() . "/" . $pom . $name . '.' . $extension;
        $finalDestination = $this->commander->getAbsolutePath() . "/" . $name . '.' . $extension;

        $this->simpleImage->toFile($fileDestination, 'image/' . (($extension === "jpg" || $extension === 'jpeg') ? 'jpeg' : $extension));

        if ($sameNameInSameDir) {
            $this->commander->removeFile($this->image->getNameExtension());
            rename($fileDestination, $finalDestination);
        }

        $this->commander->setPath($this->image->getFileDirectoryPath());

        if (!is_null($newName)) {
            $this->image->setName($newName);
        }
        if (!is_null($newExtension)) {
            $this->image->setExtension($newExtension);
        }
        if (!is_null($myTarget)) {
            $this->image->setFileDirectoryPath($myTarget);
        }
    }

}