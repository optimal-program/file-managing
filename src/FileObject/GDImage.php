<?php

namespace Optimal\FileManaging\FileObject;

use Optimal\FileManaging\FileCommander;
use Optimal\FileManaging\ImagesManager;

final class GDImage
{
    private $destination;
    private $target;
    private $GDResource;
    private $imageType;

    function __construct(Image $destination, Image $target, $resource)
    {

        if (gettype($resource) != "resource" && get_resource_type($resource) != "gd") {
            throw new \Exception("Resource is not a GD resource!");
        }

        $this->GDResource = $resource;
        $this->target = $target;
        $this->destination = $destination;

        if (function_exists("exif_imagetype")) {
            $this->imageType = exif_imagetype($destination->getRealPath());
        } else {
            $info = getimagesize($destination->getRealPath());
            $this->imageType = $info[ 2 ];
        }
    }

    /**
     * @return bool
     */
    public function isJPG()
    {
        return $this->imageType == 2;
    }

    /**
     * @return bool
     */
    public function isPNG()
    {
        return $this->imageType == 3;
    }

    /**
     * @return bool
     */
    public function isGIF()
    {
        return $this->imageType == 1 || $this->imageType == 0;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->target->getRealName();
    }

    public function setName($name)
    {
        $this->target->setRealName($name);
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->target->getRealExtension();
    }

    /**
     * @return mixed
     */
    public function getOrigExtension()
    {
        return $this->destination->getRealExtension();
    }

    /**
     * @return mixed
     */
    public function getGDImage()
    {
        return $this->GDResource;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->target->getRealWidth();
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->target->getRealHeight();
    }

    /**
     * @return mixed
     */
    public function getOrigWidth()
    {
        return $this->target->getRealWidth();
    }

    /**
     * @return mixed
     */
    public function getOrigHeight()
    {
        return $this->target->getRealHeight();
    }

    public function getTargetFile()
    {
        return $this->target;
    }

    /**
     * @method - remove original image
     */
    public function removeOriginal()
    {
        if ($this->target->getRealDestination() != $this->destination->getRealDestination()) {
            $cmd = new FileCommander();
            $cmd->setPath($this->destination->getRealDestination());
            $cmd->removeFile($this->destination->getRealName(), [$this->destination->getRealExtension()]);
        }
    }

    /**
     * @param $degree
     * @throws \Exception
     */
    public function rotate($degree)
    {
        if (isset($degree) && is_numeric($degree)) {
            $rotate = imagerotate($this->GDResource, $degree, 0);
            $this->GDResource = $rotate;
        } else {
            throw new \Exception("No degree defined!");
        }
    }

    /**
     * @method - rotate image into correct angle
     */
    public function autoRotate()
    {

        if (!$this->isJPG()) {
            return;
        }

        $orientation = $this->target->getRealOrientation();

        switch ($orientation) {
            case 3:
                $rotate = imagerotate($this->GDResource, 180, 0);
                break;
            case 6:
                $rotate = imagerotate($this->GDResource, -90, 0);
                break;
            case 8:
                $rotate = imagerotate($this->GDResource, 90, 0);
                break;
            default:
                $rotate = $this->GDResource;
                break;
        }

        $this->GDResource = $rotate;

    }

    /**
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     */
    private function resampleImg($width, $height, $x = -1, $y = -1)
    {

        if ($x >= 0 || $y >= 0) {
            $widthO = $width;
            $heightO = $height;
        } else {
            $widthO = $this->target->getRealWidth();
            $heightO = $this->target->getRealHeight();
            $x = 0;
            $y = 0;
        }

        $imagesManager = new ImagesManager();
        $newImg = $imagesManager->createEmptyGD($width, $height);

        if ($this->isPNG() || $this->isGIF()) {
            $transparent_index = imagecolortransparent($this->GDResource);
            if ($transparent_index >= 0) {  // GIF
                imagepalettecopy($this->GDResource, $newImg);
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

        imagecopyresampled($newImg, $this->GDResource, 0, 0, $x, $y, $width, $height, $widthO, $heightO);

        $this->GDResource = $newImg;

        $this->target->setRealWidth($width);
        $this->target->setRealHeight($height);
    }

    /**
     * @param int $x
     * @param int $y
     * @param $width
     * @param $height
     */
    public function cropImage($x, $y, $width, $height)
    {
        $this->resampleImg($width, $height, $x, $y);
    }

    /**
     * @param int $new_width
     * @param int $new_height
     */
    private function imageResize($new_width = 0, $new_height = 0)
    {

        if ($new_height != 0) {
            $prc = (100 * $new_height) / $this->target->getRealHeight();
            $height = $new_height;
            $width = ceil(($this->target->getRealWidth() * $prc) / 100);
        } else {
            $prc = (100 * $new_width) / $this->target->getRealWidth();
            $width = $new_width;
            $height = ceil($this->target->getRealHeight() * $prc) / 100;
        }

        $this->resampleImg($width, $height);

    }

    /**
     * @param int $width
     * @param int $height
     */
    public function transparentBackground($width, $height)
    {

        $imagesManager = new ImagesManager();
        $resultPic = $imagesManager->createEmptyGD($width, $height);

        $x = ($width / 2) - ($this->target->getRealWidth() / 2);
        $y = ($height / 2) - ($this->target->getRealHeight() / 2);

        $transparent_index = imagecolortransparent($this->GDResource);
        if ($transparent_index >= 0) {  // GIF
            imagepalettecopy($this->GDResource, $resultPic);
            imagefill($resultPic, 0, 0, $transparent_index);
            imagecolortransparent($resultPic, $transparent_index);
            imagetruecolortopalette($resultPic, true, 256);
        } else {
            imagealphablending($resultPic, false);
            imagesavealpha($resultPic, true);
            $transparent = imagecolorallocatealpha($resultPic, 255, 255, 255, 127);
            imagefilledrectangle($resultPic, 0, 0, $width, $height, $transparent);
        }

        $widthO = $this->target->getRealWidth();
        $heightO = $this->target->getRealHeight();

        imagecopyresampled($resultPic, $this->GDResource, $x, $y, 0, 0, $widthO, $heightO, $widthO, $heightO);
        $this->GDResource = $resultPic;

        $this->imageType = 3;
        $this->target->setRealExtension("png");

    }

    /**
     * @param int $width
     * @param int $height
     * @param string $method [crop,supplement]
     * @throws \Exception
     */
    public function resize($width = 0, $height = 0, $method = ImagesResizeSettings::CROP_TYPE_CROP)
    {

        if ($width != 0 && $height != 0) {

            $widthO = $this->target->getRealWidth();
            $heightO = $this->target->getRealHeight();

            if (($widthO / $heightO) == ($width / $height)) {
                $this->imageResize($width);
            } else {

                if (($widthO == $heightO) && ($widthO > $width || $heightO > $height)) {

                    if ($width > $height || abs($width / $height - 16 / 9) < 0.6) {
                        $this->imageResize(0, $height);
                    } else {
                        $this->imageResize($width);
                    }

                } else {
                    if (($widthO > $heightO) && ($widthO > $width)) {

                        if ($method == "crop") {
                            if ($heightO > $height) {
                                $this->imageResize(0, $height);
                            }
                        } else {
                            if ($heightO > $height) {
                                $this->imageResize($width);
                            }
                        }

                    } else {

                        if ($method == "crop") {
                            if ($widthO > $width) {
                                $this->imageResize($width);
                            }
                        } else {
                            if ($widthO > $width) {
                                $this->imageResize(0, $height);
                            }
                        }

                    }
                }

                $widthO = $this->target->getRealWidth();
                $heightO = $this->target->getRealHeight();

                switch ($method) {

                    case "crop":

                        if ($widthO > $heightO) {

                            if ($widthO > $width) {

                                $x = ($widthO / 2) - ($width / 2);
                                $y = 0;

                                $this->cropImage($x, $y, $width, $heightO);
                            }

                            if ($heightO > $height) {

                                $x = 0;
                                $y = ($heightO / 2) - ($height / 2);

                                $this->cropImage($x, $y, $widthO, $height);
                            }

                        } else {

                            if ($heightO > $height) {

                                $x = 0;
                                $y = ($heightO / 2) - ($height / 2);

                                $this->cropImage($x, $y, $widthO, $height);
                            }

                            if ($widthO > $width) {

                                $x = ($widthO / 2) - ($width / 2);
                                $y = 0;

                                $this->cropImage($x, $y, $width, $heightO);
                            }

                        }

                        $widthO = $this->target->getRealWidth();
                        $heightO = $this->target->getRealHeight();

                        if (($widthO < $width) || ($heightO < $height)) {
                            $this->transparentBackground($width, $height);
                        }

                        break;
                    case "supplement":

                        $this->transparentBackground($width, $height);

                        break;
                    default:
                        throw new \Exception("Špatně defnovaný způsob oříznutí obrázků (" . $method . ").");
                        break;
                }

            }

        } else {

            if ($width > 0) {
                $this->imageResize($width);
            } elseif ($height > 0) {
                $this->imageResize(0, $height);
            } else {

                $widthO = $this->target->getRealWidth();
                $heightO = $this->target->getRealHeight();

                if ($width >= $widthO || $height >= $heightO) {
                    if ($widthO >= $heightO) {
                        $this->imageResize(1280);
                    } else {
                        $this->imageResize(0, 720);
                    }

                }
            }
        }

    }

    public function cropTransparentBorders()
    {

        // Get the width and height
        $width = $this->target->getRealWidth();
        $height = $this->target->getRealHeight();

        // Find the size of the borders
        $top = 0;
        $bottom = 0;
        $left = 0;
        $right = 0;
        $bgcolor = imagecolorat($this->GDResource, $top,
            $left); // This works with any color, including transparent backgrounds
        //top
        for (; $top < $height; ++$top) {
            for ($x = 0; $x < $width; ++$x) {
                if (imagecolorat($this->GDResource, $x, $top) != $bgcolor) {
                    break 2; //out of the 'top' loop
                }
            }
        }
        //bottom
        for (; $bottom < $height; ++$bottom) {
            for ($x = 0; $x < $width; ++$x) {
                if (imagecolorat($this->GDResource, $x, $height - $bottom - 1) != $bgcolor) {
                    break 2; //out of the 'bottom' loop
                }
            }
        }
        //left
        for (; $left < $width; ++$left) {
            for ($y = 0; $y < $height; ++$y) {
                if (imagecolorat($this->GDResource, $left, $y) != $bgcolor) {
                    break 2; //out of the 'left' loop
                }
            }
        }
        //right
        for (; $right < $width; ++$right) {
            for ($y = 0; $y < $height; ++$y) {
                if (imagecolorat($this->GDResource, $width - $right - 1, $y) != $bgcolor) {
                    break 2; //out of the 'right' loop
                }
            }
        }

        //copy the contents, excluding the border
        $this->cropImage($left, $top, $width - ($left + $right), $height - ($top + $bottom));
    }

    public function show()
    {

        switch (strtolower($this->destination->getRealExtension())) {

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
                imagegif($this->GDResource
                );
        }

    }

    public function save()
    {

        if ($this->destination->getRealDestination() == $this->target->getRealDestination()) {
            $pom = "_";
        } else {
            $pom = "";
        }

        $cmd = new FileCommander();
        $cmd->setPath($this->target->getRealDestination());
        $files = $cmd->getFiles($this->target->getRealName());

        if (!empty($files)) {
            foreach ($files as $file) {
                if ($file->getRealExtension() != $this->target->getRealExtension()) {
                    $cmd->removeFile($file->getRealName(), [$file->getRealExtension()]);
                }
            }
        }

        switch ($this->target->getRealExtension()) {
            case "jpg":
            case "jpeg":
                imagejpeg($this->GDResource,
                    $this->target->getRealDestination() . "/" . $this->target->getName() . $pom . "." . $this->target->getRealExtension(),
                    100);
                break;
            case "png":
                imagepng($this->GDResource,
                    $this->target->getRealDestination() . "/" . $this->target->getName() . $pom . "." . $this->target->getRealExtension());
                break;
            case "gif":
                imagegif($this->GDResource,
                    $this->target->getRealDestination() . "/" . $this->target->getName() . $pom . "." . $this->target->getRealExtension());
        }

        if ($pom != "") {
            $cmd->setPath($this->destination->getRealDestination());
            $cmd->removeFile($this->destination->getName(), [$this->destination->getRealExtension()]);
            rename($this->target->getRealDestination() . "/" . $this->target->getRealName() . $pom . "." . $this->target->getRealExtension(),
                $this->target->getRealDestination() . "/" . $this->target->getRealName() . "." . $this->target->getRealExtension());
        }

    }
}