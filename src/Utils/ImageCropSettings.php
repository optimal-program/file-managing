<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

class ImageCropSettings
{

    private $ratio = null;
    private $minWidth;
    private $maxWidth;
    private $minHeight;
    private $maxHeight;
    private $resizable;

    private $x1;
    private $y1;
    private $x2;
    private $y2;

    function __construct()
    {
        $this->x1 = 0;
        $this->x2 = 0;
        $this->y1 = 0;
        $this->y2 = 0;
        $this->resizable = true;
        $this->minHeight = 0;
        $this->minWidth = 0;
    }

    /**
     * @return float|null
     */
    public function getRatio():?float
    {
        return $this->ratio;
    }

    /**
     * @param float $ratioW
     * @param float $ratioH
     * @return $this
     */
    public function setRatio(float $ratioW, float $ratioH)
    {
        $this->ratio = "".$ratioW.":".$ratioH."";
        return $this;
    }

    /**
     * @return int
     */
    public function getMinWidth():int
    {
        return $this->minWidth;
    }

    /**
     * @param int $minWidth
     * @return $this
     */
    public function setMinWidth(int $minWidth)
    {
        $this->minWidth = $minWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxWidth():int
    {
        return $this->maxWidth;
    }

    /**
     * @param int $maxWidth
     * @return $this
     */
    public function setMaxWidth(int $maxWidth)
    {
        $this->maxWidth = $maxWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinHeight():int
    {
        return $this->minHeight;
    }

    /**
     * @param int $minHeight
     * @return $this
     */
    public function setMinHeight(int $minHeight)
    {
        $this->minHeight = $minHeight;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxHeight()
    {
        return $this->maxHeight;
    }

    /**
     * @param mixed $maxHeight
     */
    public function setMaxHeight($maxHeight)
    {
        $this->maxHeight = $maxHeight;
    }

    /**
     * @return bool
     */
    public function isResizable():bool
    {
        return $this->resizable;
    }

    /**
     * @param bool $resizable
     * @return $this
     */
    public function setResizable(bool $resizable)
    {
        $this->resizable = $resizable;
        return $this;
    }

    /**
     * @return int
     */
    public function getX1(): int
    {
        return $this->x1;
    }

    /**
     * @param int $x1
     * @return $this
     */
    public function setX1(int $x1)
    {
        $this->x1 = $x1;
        return $this;
    }

    /**
     * @return int
     */
    public function getY1(): int
    {
        return $this->y1;
    }

    /**
     * @param int $y1
     * @return $this
     */
    public function setY1(int $y1)
    {
        $this->y1 = $y1;
        return $this;
    }

    /**
     * @return int
     */
    public function getX2(): int
    {
        return $this->x2;
    }

    /**
     * @param int $x2
     * @return $this
     */
    public function setX2(int $x2)
    {
        $this->x2 = $x2;
        return $this;
    }

    /**
     * @return int
     */
    public function getY2(): int
    {
        return $this->y2;
    }

    /**
     * @param int $y2
     * @return $this
     */
    public function setY2(int $y2)
    {
        $this->y2 = $y2;
        return $this;
    }

}