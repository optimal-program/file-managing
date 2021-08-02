<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

class ImageResolutionsSettings
{

    private $resolutions = [];

    /**
     * @param ImageResolutionSettings $settings
     */
    public function addResolutionSettingsByObject(ImageResolutionSettings $settings):void
    {
        $this->resolutions[] = $settings;
    }

    /**
     * @param $width
     * @param null $height
     */
    public function addResolutionSettings($width, $height = null):void
    {
        $this->resolutions[] = new ImageResolutionSettings($width, $height);
    }

    /**
     * @return array
     */
    public function getResolutionsSettings():array
    {
        return $this->resolutions;
    }

}