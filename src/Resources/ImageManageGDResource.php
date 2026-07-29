<?php declare(strict_types=1);

namespace Optimal\FileManaging\Resources;

use claviska\SimpleImage;
use Exception;
use Optimal\FileManaging\Exception\DeleteFileException;
use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\FileException;
use Optimal\FileManaging\Exception\GDException;
use Optimal\FileManaging\FileCommander;
use Optimal\FileManaging\Utils\FilesTypes;

final class ImageManageGDResource extends ImageManageResource
{

    /**
     * @throws Exception
     */
    public function __construct(BitmapImageFileResource $image, FileCommander $commander)
    {
        parent::__construct($image, $commander);
        $this->simpleImage = new SimpleImage($this->image->getFilePath());
    }

    public function getSimpleImage(): SimpleImage
    {
        return $this->simpleImage;
    }

    public function setSimpleImage(SimpleImage $simpleImage): void
    {
        $this->simpleImage = $simpleImage;
    }

    /**
     * @throws GDException
     * @throws Exception
     */
    public function rotate(int $degree): void
    {
        $this->simpleImage->rotate($degree);
    }

    public function maxResize(?int $maxWidth = null, ?int $maxHeight = null): void
    {
        $newDimensions = $this->getMaxResizeDimensions($this->simpleImage->getWidth(), $this->simpleImage->getHeight(), $maxWidth, $maxHeight);

        if (!is_null($newDimensions)) {
            $this->simpleImage->resize($newDimensions[0], $newDimensions[1]);
        }

        $this->image->setWidth($this->simpleImage->getWidth());
        $this->image->setHeight($this->simpleImage->getHeight());
    }

    public function resize(?int $width = null, ?int $height = null): void
    {
        $this->simpleImage->resize($width, $height);
        $this->image->setWidth($this->simpleImage->getWidth());
        $this->image->setHeight($this->simpleImage->getHeight());
    }

    public function cropImage(int $x, int $y, int $width, int $height): void
    {
        $this->simpleImage->crop($x, $y, $x + $width, $y + $height);
    }

    /**
     * @throws Exception
     */
    public function show(): void
    {
        $this->simpleImage->toScreen();
    }

    /**
     * @throws DeleteFileException
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function save(?string $myTarget = null, ?string $newName = null, ?string $newExtension = null, ?int $quality = null): void
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

        $mimeType = FilesTypes::getImageMimeType($extension);

        if (is_null($quality)) {
            $this->simpleImage->toFile($fileDestination, $mimeType);
        }
        else {
            $this->simpleImage->toFile($fileDestination, $mimeType, $quality);
        }

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

        // the saved image has different size and resolution than the source one
        $this->image->refreshFileInfo();
    }

}