<?php

namespace Optimal\FileManaging;

use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\FileException;
use Optimal\FileManaging\Exception\GDException;
use Optimal\FileManaging\resources\GDImage;

class ImagesManager
{
    private $newDestination;
    private $commander;

    function __construct()
    {
        $this->commander = new FileCommander();
    }

    /**
     * @param $destination
     * @return DirectoryNotFoundException
     */
    public function setDestination($destination)
    {
        $this->commander->setPath($destination);
        $this->newDestination = $destination;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->commander->getActualPath();
    }

    /**
     * @param $destination
     * @throws DirectoryNotFoundException
     */
    public function setOutputDestination($destination)
    {
        $this->commander->checkPath($destination);
        $this->newDestination = $destination;
    }

    /**
     * @return mixed
     */
    public function getOutputDestination()
    {
        return $this->newDestination;
    }

    /**
     * @param $source
     * @return bool
     */
    public function isValidIMG($source)
    {
        if (gettype($source) == "resource") {
            if (get_resource_type($source) == "gd") {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $imageName
     * @param $imageExtension
     * @return GDImage
     * @throws \Exception
     */
    public function loadGDImage($imageName, $imageExtension)
    {
        if ($this->commander->getActualPath() != '') {
            if ($imageName != '') {
                if ($imageExtension != '') {

                    $destinationImg = $this->commander->searchFile($imageName, $imageExtension);

                    $targetImg = clone($destinationImg);
                    $targetImg->setRealDestination($this->newDestination);

                    $GDResource = $this->createGDResourceFrom($imageName, $imageExtension);
                    $image = new GDImage($destinationImg, $targetImg, $GDResource);

                    return $image;

                } else {
                    throw new FileException("No image: " . $imageName . " extension defined!");
                }
            } else {
                throw new FileException("No image name defined!");
            }
        } else {
            throw new FileException("No images destination defined!");
        }

    }

    /**
     * @param $imageName
     * @param $imageExtension
     * @param $GDResource
     * @return GDImage
     * @throws FileException
     * @throws GDException
     */
    public function loadGDImageViaGD($imageName, $imageExtension, $GDResource)
    {

        if ($this->isValidIMG($GDResource)) {
            if ($imageName != '') {
                if ($imageExtension != '') {
                    $destinationImg = $this->commander->searchFile($imageName, $imageExtension);
                    $targetImg = clone($destinationImg);
                    $targetImg->setRealDestination($this->newDestination);
                    return new GDImage($destinationImg, $targetImg, $GDResource);
                } else {
                    throw new FileException("No image: " . $imageName . " extension defined!");
                }
            } else {
                throw new FileException("No image name defined!");
            }
        } else {
            throw new GDException("GD resource is not valid");
        }

    }

    /**
     * @param $imgName
     * @param $imgExtension
     * @return bool|resource
     * @throws GDException
     */
    public function createGDResourceFrom($imgName, $imgExtension)
    {
        $image = false;

        switch (strtolower($imgExtension)) {
            case "jpg":
            case "jpeg":
                $image = imagecreatefromjpeg($this->commander->getActualPath() . "/" . $imgName . "." . $imgExtension);
                break;
            case "png":
                $image = imagecreatefrompng($this->commander->getActualPath() . "/" . $imgName . "." . $imgExtension);
                break;
            case "gif":
                $image = imagecreatefromgif($this->commander->getActualPath() . "/" . $imgName . "." . $imgExtension);
        }

        if (!$this->isValidIMG($image)) {
            throw new GDException("Creation of resource was not successful");
        }

        return $image;
    }

    /**
     * @param $width
     * @param $height
     * @param string $type
     * @return null|resource
     * @throws \Exception
     */
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

    /**
     * @param $imgNameExt
     * @return mixed|null
     * @throws \Exception
     */
    public function getIMGInHTML($imgNameExt)
    {

        if ($this->commander->getActualPath() != '') {
            $explode = explode(".", $imgNameExt);
            $img = $this->commander->searchFile($explode[ 0 ], $explode[ 1 ]);
            return $img;
        } else {
            throw new DirectoryNotFoundException("No images destination defined");
        }
    }

}

