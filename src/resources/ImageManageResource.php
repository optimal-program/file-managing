<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 20.08.2019
 * Time: 14:48
 */

namespace Optimal\FileManaging\resources;


use Optimal\FileManaging\FileCommander;

abstract class ImageManageResource
{

    const CROP_TYPE_CROP = "crop";
    const CROP_TYPE_SUPPLEMENT = "supplement";

    protected $image;
    protected $resource;
    protected $commander;

    /**
     * ImageManageResource constructor.
     * @param ImageResource $image
     * @param FileCommander $commander
     */
    function __construct(ImageResource $image, FileCommander $commander){
        $this->image = $image;
        $this->commander = $commander;
    }

    /**
     * @param int $new_width
     * @param int $new_height
     */
    protected function imageResize($new_width = 0, $new_height = 0) {

        if ($new_height != 0) {
            $prc = (100 * $new_height) / $this->image->getHeight();
            $height = $new_height;
            $width = ceil(($this->image->getWidth() * $prc) / 100);
        } else {
            $prc = (100 * $new_width) / $this->image->getWidth();
            $width = $new_width;
            $height = ceil($this->image->getHeight() * $prc) / 100;
        }

        $this->resampleImg($width, $height);

    }

    /**
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     * @return mixed
     */
    abstract protected function resampleImg($width, $height, $x = -1, $y = -1);

    /**
     * @return ImageResource
     */
    public function getImageResource(){
        return $this->image;
    }

    /**
     * @throws \Optimal\FileManaging\Exception\DeleteFileException
     */
    public function removeOriginal()
    {
        $this->commander->removeFile($this->image->getNameExtension());
    }

    /**
     * @method - rotate image into correct angle
     */
    public function autoRotate()
    {

        if (!$this->image->isJPG()) {
            return;
        }

        $orientation = $this->image->getOrientation();

        switch ($orientation) {
            case 3:
                $this->rotate(180);
                break;
            case 6:
                $this->rotate(-90);
                break;
            case 8:
                $this->rotate(90);
                break;
            default:
                break;
        }

        $this->image->setOrientation(1);
    }

    /**
     * @param int $x
     * @param int $y
     * @param $width
     * @param $height
     */
    public function cropImage($x, $y, $width, $height) {
        $this->resampleImg($width, $height, $x, $y);
    }

    /**
     * @param int $width
     * @param int $height
     * @param string $method [crop,supplement]
     * @throws \Exception
     */
    public function resize($width = 0, $height = 0, $method = self::CROP_TYPE_CROP)
    {

        if ($width != 0 && $height != 0) {

            $widthO = $this->image->getWidth();
            $heightO = $this->image->getHeight();

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

                $widthO = $this->image->getWidth();
                $heightO = $this->image->getHeight();

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

                        $widthO = $this->image->getWidth();
                        $heightO = $this->image->getHeight();

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

                $widthO = $this->image->getWidth();
                $heightO = $this->image->getHeight();

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

    /**
     * @param int $degree
     * @return mixed
     */
    abstract public function rotate($degree);

    /**
     * @param int $width
     * @param int $height
     * @return mixed
     */
    abstract public function transparentBackground($width, $height);

    /**
     * @return mixed
     */
    abstract public function show();

    /**
     * @return mixed
     */
    abstract public function save();

}