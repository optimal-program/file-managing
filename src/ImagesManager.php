<?php declare(strict_types=1);

namespace Optimal\FileManaging;

use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\FileException;
use Optimal\FileManaging\Exception\FileNotFoundException;
use Optimal\FileManaging\resources\BitmapImageFileResource;
use Optimal\FileManaging\resources\ImageManageGDResource;
use Optimal\FileManaging\resources\ImageManageResource;
use Optimal\FileManaging\resources\ImageManageImagickResource;

class ImagesManager
{

    const RESOURCE_TYPE_GD      = "gd";
    const RESOURCE_TYPE_IMAGICK = "imagick";

    private $newDestination;
    private $commander;

    public function __construct()
    {
        $this->commander = new FileCommander();
    }

    /**
     * @param string $dir
     * @throws DirectoryNotFoundException
     */
    public function setSourceDirectory(string $dir): void
    {
        $validPath = FileCommander::checkPath($dir);
        $this->commander->setPath($validPath);
        $this->setOutputDirectory($validPath);
    }

    /**
     * @param string $dir
     * @throws DirectoryNotFoundException
     */
    public function setOutputDirectory(string $dir): void
    {
        $validPath = FileCommander::checkPath($dir);
        $this->newDestination = $validPath;
    }

    /**
     * @return string
     * @throws DirectoryNotFoundException
     */
    public function getSourceDirectory(): string
    {
        return $this->commander->getAbsolutePath();
    }

    /**
     * @return string
     */
    public function getOutputDirectory(): string
    {
        return $this->newDestination;
    }

    /**
     * @param string $imgName
     * @param string|null $imgExtension
     * @param string $resourceType
     * @return ImageManageResource
     * @throws DirectoryNotFoundException
     * @throws FileException
     * @throws FileNotFoundException
     * @throws \ImagickException
     */
    public function loadImageManageResource(string $imgName, ?string $imgExtension = null, string $resourceType = self::RESOURCE_TYPE_GD): ImageManageResource
    {

        if (empty($imgName)) {
            throw new FileException("Image name is required");
        }

        if (is_null($imgExtension)) {
            $imgExtension = pathinfo($this->commander->getAbsolutePath() . "/" . $imgName, PATHINFO_EXTENSION);
            $imgName = pathinfo($this->commander->getAbsolutePath() . "/" . $imgName, PATHINFO_FILENAME);
        }

        if ($this->commander->fileExists($imgName, $imgExtension)) {

            $image = $this->commander->getImage($imgName, $imgExtension);
            $image->setNewPath($this->newDestination);

            $resource = null;

            if ($resourceType === self::RESOURCE_TYPE_GD && $image instanceof BitmapImageFileResource) {
                return new ImageManageGDResource($image, $this->commander);
            }

            if ($resourceType === self::RESOURCE_TYPE_IMAGICK && $image instanceof BitmapImageFileResource) {
                return new ImageManageImagickResource($image, $this->commander);
            }

            if ($resourceType !== self::RESOURCE_TYPE_GD && $resourceType !== self::RESOURCE_TYPE_IMAGICK) {
                throw new \Exception('Wrong resource type');
            }

            throw new \Exception('Image is not bitmap!');
        }

        throw new FileNotFoundException("File: " . $imgName . "." . $imgExtension . " not found in " . $this->commander->getRelativePath());

    }

}