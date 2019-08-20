<?php

namespace Optimal\FileManaging\Utils;

class ImagesResizeSettings
{

    const CROP_TYPE_CROP = "crop";
    const CROP_TYPE_SUPPLEMENT = "supplement";

    private $width = null;
    private $height = null;
    private $cropType = null;

    function __construct()
    {
        $this->width = 1280;
        $this->height = 0;
        $this->cropType = self::CROP_TYPE_CROP;
    }

    /**
     * @param $width
     */
    public function setResizeWidth($width){
        $this->width = $width;
    }

    /**
     * @param $height
     */
    public function setResizeHeight($height){
        $this->height = $height;
    }

    /**
     * @param $type
     * @throws \Exception
     */
    public function setResizeType($type){

        if($type != self::CROP_TYPE_CROP && $type != self::CROP_TYPE_SUPPLEMENT){
            throw new \Exception("Wrong crop type");
        }

        $this->cropType = $type;

    }

    /**
     * @return int|null
     */
    public function getResizeWidth(){
        return $this->width;
    }

    /**
     * @return int|null
     */
    public function getResizeHeight(){
        return $this->height;
    }

    /**
     * @return string|null
     */
    public function getResizeType(){
        return $this->cropType;
    }

}