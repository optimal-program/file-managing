<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

class ImageResolutionsSettings
{
    private array $resolutions = [];

    public function addResolutionSettingsByObject(ImageResolutionSettings $settings):void
    {
        $this->resolutions[] = $settings;
    }

    public function addResolutionSettings($width, $height = null):void
    {
        $this->resolutions[] = new ImageResolutionSettings($width, $height);
    }

    public function getResolutionsSettings():array
    {
        return $this->resolutions;
    }
}