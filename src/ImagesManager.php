<?php declare(strict_types=1);

namespace Optimal\FileManaging;

use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\FileException;
use Optimal\FileManaging\Exception\FileNotFoundException;
use Optimal\FileManaging\resources\ImageManageGDResource;
use Optimal\FileManaging\resources\ImageManageResource;
use Optimal\FileManaging\resources\ImageResource;
use Optimal\FileManaging\resources\ImageManageImagickResource;

class ImagesManager
{

    const RESOURCE_TYPE_GD = "gd";
    const RESOURCE_TYPE_IMAGICK = "imagick";

    private $newDestination;
    private $commander;

    function __construct(){
        $this->commander = new FileCommander();
    }

    /**
     * @param string $dir
     * @throws DirectoryNotFoundException
     */
    public function setTargetDirectory(string $dir){
        $this->commander->setPath($dir);
        $this->setOutputDirectory($dir);
    }

    /**
     * @param string $dir
     * @throws DirectoryNotFoundException
     */
    public function setOutputDirectory(string $dir){
        $this->commander->checkPath($dir);
        $this->newDestination = $dir;
    }

    /**
     * @return string
     * @throws DirectoryNotFoundException
     */
    public function getTargetDirectory():string {
        return $this->commander->getAbsolutePath();
    }

    /**
     * @return string
     */
    public function getOutputDirectory():string {
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
     * @throws Exception\GDException|\ImagickException
     */
    public function loadImageManageResource(string $imgName,?string $imgExtension = null,string $resourceType = self::RESOURCE_TYPE_GD):ImageManageResource
    {

        if(empty($imgName)){
            throw new FileException("Image name is required");
        }

        if($imgExtension == null){
            $parts = explode(".", $imgName);
            $imgName = $parts[0];
            $imgExtension = $parts[1];
        }

        if($this->commander->fileExists($imgName, $imgExtension)){

            $image = $this->commander->getImage($imgName, $imgExtension);
            $image->setNewPath($this->newDestination);

            $resource = null;

            switch ($resourceType){
                case "gd":
                    $resource = new ImageManageGDResource($image, $this->commander);
                break;
                case "imagick":
                    $resource = new ImageManageImagickResource($image, $this->commander);
                break;
            }

            return $resource;

        } else {
            throw new FileNotFoundException("File: ".$imgName.".".$imgExtension." not found");
        }

    }

}