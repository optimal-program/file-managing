<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 08.10.2017
 * Time: 14:09
 */

namespace Optimal\FileManaging\resources;

final class ImageResource extends FileResource
{

    protected $width;
    protected $height;
    protected $orientation;

    function __construct($path, $name, $extension = null)
    {
        parent::__construct($path, $name, $extension);
    }

    protected function setFileInfo()
    {
        parent::setFileInfo();

        $exif = @exif_read_data($this->path."/".$this->name.".".$this->extension);

        if ($this->extension == "jpg") {
            if (isset($exif["COMPUTED"]["Orientation"])) {
                $this->orientation = $exif["COMPUTED"]["Orientation"];
            } else {
                $this->orientation = 1;
            }
        }

        list($width, $height) = getimagesize($this->path."/".$this->name.".".$this->extension);
        $this->width = $width;
        $this->height = $height;

    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * @param $orientation
     * @return $this
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;
        return $this;
    }

    /**
     * @return bool
     */
    public function isJPG(){
        return $this->extension == "jpg" || $this->extension == "jpeg";
    }

    /**
     * @return bool
     */
    public function isPNG(){
        return $this->extension == "png";
    }

    /**
     * @return bool
     */
    public function isGIF(){
        return $this->extension == "gif";
    }

    /**
     * @param $string
     * @return mixed
     */
    public function parseString($string)
    {

        $string = parent::parseString($string);

        $string = str_replace("{width}", $this->width, $string);
        $string = str_replace("{height}", $this->height, $string);
        $string = str_replace("{orientation}", $this->orientation, $string);

        return $string;
    }

}