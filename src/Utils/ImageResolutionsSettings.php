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
    public function setDefaultExtensions(array $extensions):void
    {
        $this->defaultExtensions = $extensions;
    }

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
     * @param array $extensions
     * @param string $resizeType
     */
    public function addResolutionSettings($width, $height = null, $extensions = [])
    {
        if (empty($extensions) && !empty($this->defaultExtensions)) {
            $extensions = $this->defaultExtensions;
        }
        $this->resolutions[] = new ImageResolutionSettings($width, $height, $extensions);
    }

    /**
     * @return array
     */
    public function getResolutionsSettings():array
    {
        return $this->resolutions;
    }

}