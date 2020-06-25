<?php


namespace Optimal\FileManaging\Utils;


use Optimal\FileManaging\resources\ImageManageResource;

class ImageResolutionSettings
{
    const EXTENSION_DEFAULT = "default";

    /** @var int|null */
    private $width = null;
    /** @var int|null */
    private $height = null;
    /** @var string|null */
    private $resizeType = null;
    /** @var string|null */
    private $extension = null;

    /**
     * ImageResolutionSettings constructor.
     * @param $width
     * @param $height
     * @param string $resizeType
     * @param string $extension
     */
    public function __construct($width, $height = 0, $extension = null, $resizeType = ImageManageResource::RESIZE_TYPE_SHRINK_ONLY)
    {
        if($extension == null){
            $extension = self::EXTENSION_DEFAULT;
        }

        $this->width = $width;
        $this->height = $height;
        $this->resizeType = $resizeType;
        $this->extension = $extension;
    }

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     */
    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int|null $height
     */
    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    /**
     * @return string|null
     */
    public function getResizeType(): ?string
    {
        return $this->resizeType;
    }

    /**
     * @param string|null $resizeType
     */
    public function setResizeType(?string $resizeType): void
    {
        $this->resizeType = $resizeType;
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @param string|null $extension
     */
    public function setExtension(?string $extension): void
    {
        $this->extension = $extension;
    }

}