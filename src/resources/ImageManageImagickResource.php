<?php

namespace Optimal\FileManaging\resources;

use Optimal\FileManaging\FileCommander;

final class ImageManageImagickResource extends ImageManageResource
{

    /**
     * ImagickResource constructor.
     * @param BitmapImageFileResource $image
     * @param FileCommander $commander
     * @throws \ImagickException
     */
    function __construct(BitmapImageFileResource $image, FileCommander $commander)
    {
        parent::__construct($image, $commander);
        $this->resource = new \Imagick($image->getFilePath());
    }

    /**
     * TODO
     */

}