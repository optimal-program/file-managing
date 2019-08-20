<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 20.08.2019
 * Time: 14:46
 */

namespace Optimal\FileManaging\resources;

use Optimal\FileManaging\FileCommander;

final class ImagickResource extends ImageManageResource
{

    /**
     * ImagickResource constructor.
     * @param ImageResource $image
     * @param FileCommander $commander
     * @throws \ImagickException
     */
    function __construct(ImageResource $image, FileCommander $commander)
    {
        parent::__construct($image, $commander);
        $this->resource = new \Imagick($image->getFilePath());
    }

    /**
     * TODO
     */

}