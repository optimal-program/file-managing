<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

class ImageResolutionSettings
{
    const string EXTENSION_DEFAULT = "default";
    private ?int $width;
    private ?int $height;

    public function __construct($width, $height = null)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }
}