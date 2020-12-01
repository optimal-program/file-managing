<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

class FileResource extends AbstractFileResource
{

    /** @var BitmapImageFileResource */
    protected $previewImage;

    /**
     * @return AbstractFileResource
     */
    public function getPreviewImage(): AbstractFileResource
    {
        return $this->previewImage;
    }

    /**
     * @param AbstractFileResource $previewImage
     * @throws \Exception
     */
    public function setPreviewImage(AbstractFileResource $previewImage): void
    {
        if(!$previewImage instanceof BitmapImageFileResource){
            throw new \Exception('Wrong object type, ImageFileResource is required');
        }
        $this->previewImage = $previewImage;
    }

}