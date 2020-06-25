<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

use Optimal\FileManaging\resources\ImageManageResource;

class ImageResolutionsSettings
{

    private $resolutions = [];

    /** @var string|null */
    private $defaultExtension = null;

    public function setDefaultExtension(string $extension){
        if(in_array($extension, FilesTypes::IMAGES)) {
            $this->defaultExtension = $extension;
        }
    }

    public function addResolutionSettingsByObject(ImageResolutionSettings $settings)
    {
        array_push($this->resolutions, $settings);
    }

    public function addResolutionSettings($width, $height = 0, $extension = null, $resizeType = ImageManageResource::RESIZE_TYPE_SHRINK_ONLY)
    {
        if($extension == null && $this->defaultExtension != null){
            $extension = $this->defaultExtension;
        }
        array_push($this->resolutions, new ImageResolutionSettings($width, $height, $extension, $resizeType));
    }

    public function getResolutionsSettings()
    {
        return $this->resolutions;
    }

}