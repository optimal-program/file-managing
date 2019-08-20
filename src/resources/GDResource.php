<?php

namespace Optimal\FileManaging\resources;

use Optimal\FileManaging\Exception\GDException;
use Optimal\FileManaging\FileCommander;

final class GDResource extends ImageManageResource
{

    /**
     * GDResource constructor.
     * @param ImageResource $image
     * @param FileCommander $commander
     */
    function __construct(ImageResource $image, FileCommander $commander){

        parent::__construct($image, $commander);

        $this->resource = null;
        $resource = false;

        switch ($this->image->getExtension()) {
            case "jpg":
            case "jpeg":
                $resource = imagecreatefromjpeg($this->image->getPathToFile());
                break;
            case "png":
                $resource = imagecreatefrompng($this->image->getPathToFile());
                break;
            case "gif":
                $resource = imagecreatefromgif($this->image->getPathToFile());
                break;
        }

        if ($this->isValidGD($resource)) {
            $this->resource = $resource;
        }

    }

    /**
     * @param $resource
     * @return bool
     */
    private function isValidGD($resource){
        if (gettype($resource) == "resource") {
            if (get_resource_type($resource) == "gd") {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $resource
     * @return bool
     */
    public function setGDResource($resource){
        if ($this->isValidGD($resource)) {
            $this->resource = $resource;
            return true;
        }
        return false;
    }

    /**
     * @return bool|resource
     */
    public function getGDResource(){
        return $this->resource;
    }

    /**
     * @param $degree
     * @throws \Exception
     */
    public function rotate($degree)
    {
        if (isset($degree) && is_numeric($degree)) {
            $rotate = imagerotate($this->resource, $degree, 0);
            $this->resource = $rotate;
        } else {
            throw new GDException("No degree defined!");
        }
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     */
    protected function resampleImg($width, $height, $x = -1, $y = -1)
    {

        if ($x >= 0 || $y >= 0) {
            $widthO = $width;
            $heightO = $height;
        } else {
            $widthO = $this->image->getWidth();
            $heightO = $this->image->getHeight();
            $x = 0;
            $y = 0;
        }

        $newImg = imagecreatetruecolor($width, $height);

        if ($this->image->isPNG() || $this->image->isGIF()) {
            $transparent_index = imagecolortransparent($this->resource);
            if ($transparent_index >= 0) {  // GIF
                imagepalettecopy($this->resource, $newImg);
                imagefill($newImg, 0, 0, $transparent_index);
                imagecolortransparent($newImg, $transparent_index);
                imagetruecolortopalette($newImg, true, 256);
            } else {
                imagealphablending($newImg, false);
                imagesavealpha($newImg, true);
                $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
                imagefilledrectangle($newImg, 0, 0, $width, $height, $transparent);
            }
        }

        imagecopyresampled($newImg, $this->resource, 0, 0, $x, $y, $width, $height, $widthO, $heightO);

        $this->resource = $newImg;

        $this->image->setWidth($width);
        $this->image->setHeight($height);
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function transparentBackground($width, $height)
    {
        $resultPic = imagecreatetruecolor($width, $height);

        $x = ($width / 2) - ($this->image->getWidth() / 2);
        $y = ($height / 2) - ($this->image->getHeight() / 2);

        $transparent_index = imagecolortransparent($this->resource);
        if ($transparent_index >= 0) {  // GIF
            imagepalettecopy($this->resource, $resultPic);
            imagefill($resultPic, 0, 0, $transparent_index);
            imagecolortransparent($resultPic, $transparent_index);
            imagetruecolortopalette($resultPic, true, 256);
        } else {
            imagealphablending($resultPic, false);
            imagesavealpha($resultPic, true);
            $transparent = imagecolorallocatealpha($resultPic, 255, 255, 255, 127);
            imagefilledrectangle($resultPic, 0, 0, $width, $height, $transparent);
        }

        $widthO = $this->image->getWidth();
        $heightO = $this->image->getHeight();

        imagecopyresampled($resultPic, $this->resource, $x, $y, 0, 0, $widthO, $heightO, $widthO, $heightO);
        $this->resource = $resultPic;

        $this->image->setNewExtension("png");
    }

    public function cropTransparentBorders()
    {

        // Get the width and height
        $width = $this->image->getWidth();
        $height = $this->image->getHeight();

        // Find the size of the borders
        $top = 0;
        $bottom = 0;
        $left = 0;
        $right = 0;
        $bgcolor = imagecolorat($this->resource, $top, $left); // This works with any color, including transparent backgrounds
        //top
        for (; $top < $height; ++$top) {
            for ($x = 0; $x < $width; ++$x) {
                if (imagecolorat($this->resource, $x, $top) != $bgcolor) {
                    break 2; //out of the 'top' loop
                }
            }
        }
        //bottom
        for (; $bottom < $height; ++$bottom) {
            for ($x = 0; $x < $width; ++$x) {
                if (imagecolorat($this->resource, $x, $height - $bottom - 1) != $bgcolor) {
                    break 2; //out of the 'bottom' loop
                }
            }
        }
        //left
        for (; $left < $width; ++$left) {
            for ($y = 0; $y < $height; ++$y) {
                if (imagecolorat($this->resource, $left, $y) != $bgcolor) {
                    break 2; //out of the 'left' loop
                }
            }
        }
        //right
        for (; $right < $width; ++$right) {
            for ($y = 0; $y < $height; ++$y) {
                if (imagecolorat($this->resource, $width - $right - 1, $y) != $bgcolor) {
                    break 2; //out of the 'right' loop
                }
            }
        }

        //copy the contents, excluding the border
        $this->cropImage($left, $top, $width - ($left + $right), $height - ($top + $bottom));
    }


    public function show()
    {

        $extension = $this->image->getExtension();

        if($this->image->getNewExtension() != null){
            $extension = $this->image->getNewExtension();
        }

        switch ($extension) {

            case "jpg":
            case "jpeg":
                header("Content-Type: image/jpeg");
                imagejpeg($this->GDResource);
                break;
            case "png":
                header("Content-Type: image/png");
                imagepng($this->GDResource);
                break;
            case "gif":
                header("Content-Type: image/gif");
                imagegif($this->GDResource);
        }

    }

    /**
     * @param null $myTarget
     * @return mixed|void
     * @throws \Optimal\FileManaging\Exception\DeleteFileException
     * @throws \Optimal\FileManaging\Exception\DirectoryNotFoundException
     * @throws \Optimal\FileManaging\Exception\FileException
     */
    public function save($myTarget = null){

        if($this->image->getPath() == $this->image->getNewPath()){
            $pom = "_";
        } else {
            $pom = "";
        }

        $this->commander->setPath($this->image->getNewPath());

        $filesWithSameName = $this->commander->searchFiles($this->image->getName());

        if (!empty($filesWithSameName)) {
            foreach ($filesWithSameName as $file) {
                if ($file->getExtension() != $this->image->getExtension()) {
                    $this->commander->removeFile($file->getName(), [$file->getExtension()]);
                }
            }
        }

        if($this->image->getNewExtension() != null){
            $extension = $this->image->getNewExtension();
        } else {
            $extension = $this->image->getExtension();
        }

        $fileDestination = $pom.$this->image->getPathToFile();
        $finalDestination = $this->image->getPathToFile();

        switch ($extension) {
            case "jpg":
            case "jpeg":
                imagejpeg($this->resource, $fileDestination, 100);
                break;
            case "png":
                imagepng($this->resource, $fileDestination);
                break;
            case "gif":
                imagegif($this->resource, $fileDestination);
        }

        if ($pom != "") {
            $this->commander->removeFile($this->image->getNameExtension());
            rename($fileDestination,$finalDestination);
        }

    }

}