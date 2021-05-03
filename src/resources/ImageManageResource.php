<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

use claviska\SimpleImage;
use Optimal\FileManaging\FileCommander;

abstract class ImageManageResource
{

    const RESIZE_TYPE_CROP        = "crop";
    const RESIZE_TYPE_SUPPLEMENT  = "supplement";
    const RESIZE_TYPE_SHRINK_ONLY = "shrink";

    /** @var BitmapImageFileResource */
    protected $image;

    /** @var SimpleImage */
    protected $simpleImage;

    /** @var FileCommander */
    protected $commander;

    /**
     * ImageManageResource constructor.
     * @param BitmapImageFileResource $image
     * @param FileCommander $commander
     */
    public function __construct(BitmapImageFileResource $image, FileCommander $commander)
    {
        $this->image = $image;
        $this->commander = $commander;
    }

    /**
     * @param int $newWidth
     * @param int $newHeight
     */
    protected function imageResize(int $newWidth = 0, int $newHeight = 0): void
    {

        if ($newHeight !== 0) {
            $prc = (100 * $newHeight) / $this->image->getHeight();
            $height = $newHeight;
            $width = (int)ceil(($this->image->getWidth() * $prc) / 100);
        }
        else {
            $prc = (100 * $newWidth) / $this->image->getWidth();
            $width = $newWidth;
            $height = (int)ceil(($this->image->getHeight() * $prc) / 100);
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
    abstract protected function reSampleImg(int $width, int $height, int $x = -1, int $y = -1);

    /**
     * @return BitmapImageFileResource
     */
    public function getSourceImageResource(): BitmapImageFileResource
    {
        return $this->image;
    }

    /**
     * @return BitmapImageFileResource
     * @throws \Optimal\FileManaging\Exception\DirectoryNotFoundException
     * @throws \Optimal\FileManaging\Exception\FileNotFoundException
     */
    public function getOutputImageResource(): BitmapImageFileResource
    {
        $targetSource = clone($this->image);
        $targetSource->applyNewSettings();
        return $targetSource;
    }

    /**
     * @throws \Optimal\FileManaging\Exception\DeleteFileException
     */
    public function removeOriginal(): void
    {
        $this->commander->removeFile($this->image->getNameExtension());
    }

    public function autoRotate(): void
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
     * @param int $width
     * @param int $height
     */
    public function cropImage(int $x, int $y, int $width, int $height): void
    {
        $this->resampleImg($width, $height, $x, $y);
    }

    /**
     * @param int $width
     * @param int $height
     * @param string $method
     * @throws \Exception
     */
    public function resize(int $width = 0, int $height = 0, string $method = self::RESIZE_TYPE_SHRINK_ONLY): void
    {

        if ($width !== 0 && $height !== 0) {

            $widthO = $this->image->getWidth();
            $heightO = $this->image->getHeight();

            if (($widthO / $heightO) === ($width / $height)) {
                $this->imageResize($width);
            }
            else {

                if (($widthO === $heightO) && ($widthO > $width || $heightO > $height)) {

                    if ($width > $height || abs($width / $height - 16 / 9) < 0.6) {
                        $this->imageResize(0, $height);
                    }
                    else {
                        $this->imageResize($width);
                    }

                }
                else {
                    if (($widthO > $heightO) && ($widthO > $width)) {

                        if ($method === "crop") {
                            if ($heightO > $height) {
                                $this->imageResize(0, $height);
                            }
                        }
                        else {
                            if ($heightO > $height) {
                                $this->imageResize($width);
                            }
                        }

                    }
                    else {

                        $widthOGreater = $widthO > $width;

                        if ($method === "crop" && $widthOGreater) {
                            $this->imageResize($width);
                        }
                        else {
                            if ($widthOGreater) {
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
                        }
                        else {
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
                        
                        break;
                    case "shrink":
                    case "supplement":
                        break;
                    default:
                        throw new \Exception("Wrong resize type");
                }

            }

        }
        else {

            if ($width > 0) {
                $this->imageResize($width);
            }
            elseif ($height > 0) {
                $this->imageResize(0, $height);
            }
            else {

                $widthO = $this->image->getWidth();
                $heightO = $this->image->getHeight();

                if ($width >= $widthO || $height >= $heightO) {
                    if ($widthO >= $heightO) {
                        $this->imageResize(1280);
                    }
                    else {
                        $this->imageResize(0, 720);
                    }

                }
            }
        }

    }

    abstract public function rotate(int $degree): void;

    abstract public function show(): void;

    abstract public function save(?string $myTarget = null, ?string $extension = null): void;

}