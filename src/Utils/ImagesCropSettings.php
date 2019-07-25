<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 10.11.2017
 * Time: 18:24
 */

namespace Optimal\FileManaging\Utils;


class ImagesCropSettings
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
     * @return null
     */
    public function getRatio()
    {
        return $this->ratio;
    }

    /**
     * @param null $ratioW
     * @param null $ratioH
     */
    public function setRatio($ratioW, $ratioH)
    {
        $this->ratio = "".$ratioW.":".$ratioH."";
    }

    /**
     * @return int
     */
    public function getMinWidth()
    {
        return $this->minWidth;
    }

    /**
     * @param int $minWidth
     */
    public function setMinWidth($minWidth)
    {
        $this->minWidth = $minWidth;
    }

    /**
     * @return mixed
     */
    public function getMaxWidth()
    {
        return $this->maxWidth;
    }

    /**
     * @param mixed $maxWidth
     */
    public function setMaxWidth($maxWidth)
    {
        $this->maxWidth = $maxWidth;
    }

    /**
     * @return int
     */
    public function getMinHeight()
    {
        return $this->minHeight;
    }

    /**
     * @param int $minHeight
     */
    public function setMinHeight($minHeight)
    {
        $this->minHeight = $minHeight;
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
    public function isResizable()
    {
        return $this->resizable;
    }

    /**
     * @param bool $resizable
     */
    public function setResizable($resizable)
    {
        $this->resizable = $resizable;
    }

    /**
     * @return int
     */
    public function getX1()
    {
        return $this->x1;
    }

    /**
     * @param int $x1
     */
    public function setX1($x1)
    {
        $this->x1 = $x1;
    }

    /**
     * @return int
     */
    public function getY1()
    {
        return $this->y1;
    }

    /**
     * @param int $y1
     */
    public function setY1($y1)
    {
        $this->y1 = $y1;
    }

    /**
     * @return int
     */
    public function getX2()
    {
        return $this->x2;
    }

    /**
     * @param int $x2
     */
    public function setX2($x2)
    {
        $this->x2 = $x2;
    }

    /**
     * @return int
     */
    public function getY2()
    {
        return $this->y2;
    }

    /**
     * @param int $y2
     */
    public function setY2($y2)
    {
        $this->y2 = $y2;
    }

}