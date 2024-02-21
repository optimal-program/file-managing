<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

use Optimal\FileManaging\Resources\ImageManageResource;

class ImageResolutionSettings
{
    const EXTENSION_DEFAULT = "default";

    /** @var int|null */
    private $width;

    /** @var int|null */
    private $height;

    /**
     * ImageResolutionSettings constructor.
     * @param $width
     * @param null $height
     */
    public function __construct($width, $height = null)
    {
        $this->width = $width;
        $this->height = $height;
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

}