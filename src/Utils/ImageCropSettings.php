<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

class ImageCropSettings
{
    private ?string $ratio = null;
    private int $minWidth;
    private int $maxWidth;
    private int $minHeight;
    private mixed $maxHeight;
    private bool $resizable;

    private int $x1;
    private int $y1;
    private int $x2;
    private int $y2;

    public function __construct()
    {
        $this->x1 = 0;
        $this->x2 = 0;
        $this->y1 = 0;
        $this->y2 = 0;
        $this->resizable = true;
        $this->minHeight = 0;
        $this->minWidth = 0;
    }

    public function getRatio(): ?float
    {
        return $this->ratio;
    }

    public function setRatio(float $ratioW, float $ratioH): self
    {
        $this->ratio = "{$ratioW}:{$ratioH}";
        return $this;
    }

    public function getMinWidth(): int
    {
        return $this->minWidth;
    }

    public function setMinWidth(int $minWidth): self
    {
        $this->minWidth = $minWidth;
        return $this;
    }

    public function getMaxWidth(): int
    {
        return $this->maxWidth;
    }

    public function setMaxWidth(int $maxWidth): self
    {
        $this->maxWidth = $maxWidth;
        return $this;
    }

    public function getMinHeight(): int
    {
        return $this->minHeight;
    }

    public function setMinHeight(int $minHeight): self
    {
        $this->minHeight = $minHeight;
        return $this;
    }

    public function getMaxHeight(): self
    {
        return $this->maxHeight;
    }

    public function setMaxHeight($maxHeight): self
    {
        $this->maxHeight = $maxHeight;
        return $this;
    }

    public function isResizable(): bool
    {
        return $this->resizable;
    }

    public function setResizable(bool $resizable): self
    {
        $this->resizable = $resizable;
        return $this;
    }

    public function getX1(): int
    {
        return $this->x1;
    }

    public function setX1(int $x1): self
    {
        $this->x1 = $x1;
        return $this;
    }

    public function getY1(): int
    {
        return $this->y1;
    }

    public function setY1(int $y1): self
    {
        $this->y1 = $y1;
        return $this;
    }

    public function getX2(): int
    {
        return $this->x2;
    }

    public function setX2(int $x2): self
    {
        $this->x2 = $x2;
        return $this;
    }

    public function getY2(): int
    {
        return $this->y2;
    }

    public function setY2(int $y2): self
    {
        $this->y2 = $y2;
        return $this;
    }
}