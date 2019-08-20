<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

class ImagesResizeSettings
{

    const RESIZE_TYPE_CROP = "crop";
    const RESIZE_TYPE_SUPPLEMENT = "supplement";

    private $width = null;
    private $height = null;
    private $resizeType = null;

    function __construct()
    {
        $this->width = 1280;
        $this->height = 0;
        $this->resizeType = self::RESIZE_TYPE_CROP;
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setResizeWidth(int $width){
        $this->width = $width;
        return $this;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setResizeHeight(int $height){
        $this->height = $height;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     * @throws \Exception
     */
    public function setResizeType(string $type = self::RESIZE_TYPE_CROP){

        if($type != self::RESIZE_TYPE_CROP && $type != self::RESIZE_TYPE_SUPPLEMENT){
            throw new \Exception("Wrong crop type");
        }

        $this->resizeType = $type;
        return $this;

    }

    /**
     * @return int
     */
    public function getResizeWidth():int{
        return $this->width;
    }

    /**
     * @return int
     */
    public function getResizeHeight():int{
        return $this->height;
    }

    /**
     * @return string
     */
    public function getResizeType():string {
        return $this->resizeType;
    }

}