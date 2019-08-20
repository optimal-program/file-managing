<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

final class ImageResource extends FileResource
{

    protected $width;
    protected $height;
    protected $orientation;

    /**
     * ImageResource constructor.
     * @param string $path
     * @param string $name
     * @param string|null $extension
     * @throws \Optimal\FileManaging\Exception\FileException
     */
    function __construct(string $path,string $name,?string $extension = null)
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
     * @return int
     */
    public function getWidth():int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return ImageResource
     */
    public function setWidth(int $width):ImageResource
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight():int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return ImageResource
     */
    public function setHeight(int $height):ImageResource
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrientation():int
    {
        return $this->orientation;
    }

    /**
     * @param int $orientation
     * @return ImageResource
     */
    public function setOrientation(int $orientation):ImageResource
    {
        $this->orientation = $orientation;
        return $this;
    }

    /**
     * @return bool
     */
    public function isJPG():bool{
        return $this->extension == "jpg" || $this->extension == "jpeg";
    }

    /**
     * @return bool
     */
    public function isPNG():bool{
        return $this->extension == "png";
    }

    /**
     * @return bool
     */
    public function isGIF():bool{
        return $this->extension == "gif";
    }

    /**
     * @param string $string
     * @return string
     */
    public function parseString(string $string):string
    {

        $string = parent::parseString($string);

        $string = str_replace("{width}", $this->width, $string);
        $string = str_replace("{height}", $this->height, $string);
        $string = str_replace("{orientation}", $this->orientation, $string);

        return $string;
    }

}