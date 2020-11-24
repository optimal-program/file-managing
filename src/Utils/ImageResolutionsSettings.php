<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

use Optimal\FileManaging\resources\ImageManageResource;

class ImageResolutionsSettings
{

    private $resolutions = [];

    /** @var array */
    private $defaultExtensions = [];

    /**
     * @param array $extensions
     */
    public function setDefaultExtensions(array $extensions){
        $this->defaultExtensions = $extensions;
    }

    /**
     * @param ImageResolutionSettings $settings
     */
    public function addResolutionSettingsByObject(ImageResolutionSettings $settings)
    {
        array_push($this->resolutions, $settings);
    }

    /**
     * @param $width
     * @param null $height
     * @param array $extensions
     * @param string $resizeType
     */
    public function addResolutionSettings($width, $height = null, $extensions = [], $resizeType = ImageManageResource::RESIZE_TYPE_SHRINK_ONLY)
    {
        if(empty($extensions) && !empty($this->defaultExtensions)){
            $extensions = $this->defaultExtensions;
        }
        array_push($this->resolutions, new ImageResolutionSettings($width, $height, $extensions, $resizeType));
    }

    public function getResolutionsSettings()
    {
        return $this->resolutions;
    }

}