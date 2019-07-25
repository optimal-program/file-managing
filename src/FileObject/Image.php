<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 08.10.2017
 * Time: 14:09
 */

namespace Optimal\FileManaging\FileObject;

final class Image extends File
{

    function __construct()
    {
        parent::__construct();
    }

    protected function setFileInfo()
    {
        parent::setFileInfo();

        if (isset($this->fileData[ "full" ])) {
            if (file_exists($this->fileData[ "destination" ] . "/" . $this->fileData[ "full" ])) {

                list($width, $height) = getimagesize($this->fileData[ "destination" ] . "/" . $this->fileData[ "full" ]);
                $this->fileData[ "width" ] = $width;
                $this->fileData[ "height" ] = $height;
                if ($this->fileData[ "extension" ] == "jpg") {
                    $exif = @exif_read_data($this->fileData[ "destination" ] . "/" . $this->fileData[ "full" ]);
                    if (isset($exif[ "COMPUTED" ][ "Orientation" ])) {
                        $this->fileData[ "orientation" ] = $exif[ "COMPUTED" ][ "Orientation" ];
                    } else {
                        $this->fileData[ "orientation" ] = 1;
                    }
                }
            } else {
                $this->fileData[ "width" ] = 1;
                $this->fileData[ "height" ] = 1;
                $this->fileData[ "orientation" ] = 1;
            }
        }

    }

    /**
     * @return mixed
     */
    public function getRealWidth()
    {
        return $this->fileData[ "width" ];
    }

    /**
     * @param $width
     * @return $this
     */
    public function setRealWidth($width)
    {
        $this->fileData[ "width" ] = $width;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRealHeight()
    {
        return $this->fileData[ "height" ];
    }

    /**
     * @param $height
     * @return $this
     */
    public function setRealHeight($height)
    {
        $this->fileData[ "height" ] = $height;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRealOrientation()
    {
        return $this->fileData[ "orientation" ];
    }

    /**
     * @param $orientation
     * @return $this
     */
    public function setRealOrientation($orientation)
    {
        $this->fileData[ "orientation" ] = $orientation;
        return $this;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function parseAdditionalInformation($data)
    {

        $data = parent::parseAdditionalInformation($data);

        $data = str_replace("{realWidth}", $this->getRealWidth(), $data);
        $data = str_replace("{realHeight}", $this->getRealHeight(), $data);
        $data = str_replace("{realOrientation}", $this->getRealOrientation(), $data);

        return $data;
    }

}