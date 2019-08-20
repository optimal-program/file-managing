<?php

namespace Optimal\FileManaging;

use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\FileException;
use Optimal\FileManaging\Exception\FileNotFoundException;
use Optimal\FileManaging\resources\GDResource;
use Optimal\FileManaging\resources\ImageResource;
use Optimal\FileManaging\resources\ImagickResource;

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
     * @param $dir
     * @throws DirectoryNotFoundException
     */
    public function setTargetDirectory($dir){
        $this->commander->setPath($dir);
        $this->setOutputDirectory($dir);
    }

    /**
     * @param $dir
     * @throws DirectoryNotFoundException
     */
    public function setOutputDirectory($dir){
        $this->commander->checkPath($dir);
        $this->newDestination = $dir;
    }

    /**
     * @return null
     * @throws DirectoryNotFoundException
     */
    public function getTargetDirectory(){
        return $this->commander->getAbsolutePath();
    }

    /**
     * @return mixed
     */
    public function getOutputDirectory(){
        return $this->newDestination;
    }

    /**
     * @param $imgName
     * @param null $imgExtension
     * @param string $resourceType
     * @return GDResource|ImageResource|null
     * @throws DirectoryNotFoundException
     * @throws FileException
     * @throws FileNotFoundException
     */
    public function loadImageManageResource($imgName, $imgExtension = null, $resourceType = self::RESOURCE_TYPE_GD){

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
                    $resource = new GDResource($image, $this->commander);
                break;
                case "imagick":
                    $resource = new ImagickResource($image);
                break;
            }

            return $resource;

        } else {
            throw new FileNotFoundException("File: ".$imgName.".".$imgExtension." not found");
        }

    }

    /**
     * @param $width
     * @param $height
     * @param string $type
     * @return null|resource
     * @throws \Exception
     *
    public function createEmptyGD($width, $height, $type = "truecolor")
    {
        $image = null;

        switch ($type) {
            case "truecolor":
                if (is_numeric($width) && is_numeric($height)) {
                    $image = imagecreatetruecolor($width, $height);
                } else {
                    throw new \Exception("No width or height defined!");
                }
                break;
            default:
                break;
        }

        return $image;
    }
    */
}

