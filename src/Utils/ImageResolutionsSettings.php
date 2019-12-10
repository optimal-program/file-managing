<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

use Optimal\FileManaging\resources\ImageManageResource;

class ImageResolutionsSettings
{

    private $resolutions = [];

    public function addResolutionSettingsByObject(ImageResolutionSettings $settings){
        array_push($this->resolutions, $settings);
    }

    public function addResolutionSettings($width, $height = 0, $extension = ImageResolutionSettings::EXTENSION_DEFAULT, $resizeType = ImageManageResource::RESIZE_TYPE_SHRINK_ONLY){
        array_push($this->resolutions, new ImageResolutionSettings($width, $height, $resizeType, $extension));
    }

    public function getResolutionsSettings()
    {
        return $this->resolutions;
    }

}