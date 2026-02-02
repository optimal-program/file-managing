<?php declare(strict_types=1);

namespace Optimal\FileManaging\Resources;

use Optimal\FileManaging\Utils\FilesTypes;

class BitmapImageFileResource extends AbstractImageFileResource
{

    protected int $width;
    protected int $height;

    protected function setFileInfo(): void
    {
        parent::setFileInfo();

        [
            $this->width,
            $this->height
        ] = getimagesize($this->path . "/" . $this->name . "." . $this->extension);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function isWebp(): bool
    {
        return in_array($this->extension, FilesTypes::IMAGES_WEBP, true);
    }

    public function isJPG(): bool
    {
        return in_array($this->extension, FilesTypes::IMAGES_JPG, true);
    }

    public function isPNG(): bool
    {
        return in_array($this->extension, FilesTypes::IMAGES_PNG, true);
    }

    public function isGIF(): bool
    {
        return in_array($this->extension, FilesTypes::IMAGES_GIF, true);
    }

    public function parseString(string $string): string
    {
        return strtr($string, [
            '{width}'       => $this->width,
            '{height}'      => $this->height,
        ]);
    }
}