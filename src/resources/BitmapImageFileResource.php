<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

use Optimal\FileManaging\Utils\FilesTypes;

class BitmapImageFileResource extends AbstractImageFileResource
{

    protected $width;
    protected $height;

    protected function setFileInfo():void
    {
        parent::setFileInfo();

        list($width, $height) = getimagesize($this->path . "/" . $this->name . "." . $this->extension);
        $this->width = $width;
        $this->height = $height;

    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth(int $width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight(int $height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWebp(): bool
    {
        return in_array($this->extension, FilesTypes::IMAGES_WEBP);
    }

    /**
     * @return bool
     */
    public function isJPG(): bool
    {
        return in_array($this->extension, FilesTypes::IMAGES_JPG);
    }

    /**
     * @return bool
     */
    public function isPNG(): bool
    {
        return in_array($this->extension, FilesTypes::IMAGES_PNG);
    }

    /**
     * @return bool
     */
    public function isGIF(): bool
    {
        return in_array($this->extension, FilesTypes::IMAGES_GIF);
    }

    /**
     * @param string $string
     * @return string
     */
    public function parseString(string $string): string
    {

        $string = parent::parseString($string);

        $string = str_replace("{width}", $this->width, $string);
        $string = str_replace("{height}", $this->height, $string);
        $string = str_replace("{orientation}", $this->orientation, $string);

        return $string;
    }

}