<?php


namespace Optimal\FileManaging\Utils;


use Optimal\FileManaging\resources\ImageManageResource;

class ImageResolutionSettings
{
    const EXTENSION_DEFAULT = "default";

    /** @var int|null */
    private $width;

    /** @var int|null */
    private $height;

    /** @var string */
    private $resizeType;

    /** @var array */
    private $extensions;

    /**
     * ImageResolutionSettings constructor.
     * @param $width
     * @param int $height
     * @param array $extensions
     * @param string $resizeType
     */
    public function __construct($width, $height = null, $extensions = [], $resizeType = ImageManageResource::RESIZE_TYPE_SHRINK_ONLY)
    {
        if(empty($extensions)){
            $extensions = [self::EXTENSION_DEFAULT];
        }

        $this->width = $width;
        $this->height = $height;
        $this->resizeType = $resizeType;
        $this->extensions = $extensions;
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
     * @return string
     */
    public function getResizeType(): string
    {
        return $this->resizeType;
    }

    /**
     * @param string $resizeType
     */
    public function setResizeType(string $resizeType): void
    {
        $this->resizeType = $resizeType;
    }

    /**
     * @return array
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * @param array $extensions
     */
    public function setExtensions(array $extensions): void
    {
        $this->extensions = $extensions;
    }

}