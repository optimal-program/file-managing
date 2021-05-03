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
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     */
    protected function reSampleImg(int $width, int $height, int $x = 0, int $y = 0): void
    {

        if ($x >= 0 || $y >= 0) {
            $this->cropImage($x, $y, $width, $height);
        }
        else {
            $this->simpleImage->resize($width, $height);
        }

        $this->image->setWidth($this->simpleImage->getWidth());
        $this->image->setHeight($this->simpleImage->getHeight());

        $mime = $this->simpleImage->getMimeType();
        $mimes = new \Mimey\MimeTypes();

        $this->image->setNewExtension($mimes->getExtension($mime));
    }

    public function show(): void
    {
        $this->simpleImage->toScreen();
    }

    /**
     * @param string|null $myTarget
     * @param string|null $extension
     * @throws \Optimal\FileManaging\Exception\DeleteFileException
     * @throws \Optimal\FileManaging\Exception\DirectoryNotFoundException
     * @throws \Optimal\FileManaging\Exception\FileException
     */
    public function save(?string $myTarget = null, ?string $extension = null): void
    {

        $sameNameInSameDir = false;
        $newName = $this->image->getNewName();

        if ((is_null($newName) || $this->image->getName() === $newName) && $this->image->getFileDirectoryPath() === $this->image->getFileNewDirectoryPath()) {
            $sameNameInSameDir = true;
        }

        $pom = "";
        if ($sameNameInSameDir && $this->image->getFileDirectoryPath() === $this->image->getFileNewDirectoryPath()) {
            $pom = "_";
        }

        if (!is_null($myTarget)) {
            $this->commander->setPath($myTarget);
            $this->image->setNewPath($myTarget);
        }
        else {
            $this->commander->setPath($this->image->getFileNewDirectoryPath());
        }

        if (!is_null($extension)) {
            $this->image->setNewExtension($extension);
        }

        if (!is_null($this->image->getNewName())) {
            $filesWithSameName = $this->commander->searchImages($this->image->getNewName());
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

        if (!is_null($this->image->getNewExtension())) {
            $extension = $this->image->getNewExtension();
        }
        else {
            $extension = $this->image->getExtension();
        }

        $fileDestination = $this->commander->getAbsolutePath() . "/" . $pom . $this->image->getNewNameExtension();
        $finalDestination = $this->commander->getAbsolutePath() . "/" . $this->image->getNewNameExtension();

        $this->simpleImage->toFile($finalDestination, $extension);

        if ($sameNameInSameDir) {
            $this->commander->removeFile($this->image->getNameExtension());
            rename($fileDestination, $finalDestination);
        }

        $this->commander->setPath($this->image->getFileDirectoryPath());

    }

}