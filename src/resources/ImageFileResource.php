<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

class ImageFileResource extends AbstractImageFileResource
{
    protected $thumb = null;
    protected $backupResource = null;
    protected $lowPreloadQualityResource = null;
    protected $main;

    protected $alt;
    protected $caption;

    /**
     * ImageResource constructor.
     * @param string $path
     * @param string|null $name
     * @param string|null $extension
     * @throws \Optimal\FileManaging\Exception\FileException
     */
    function __construct(string $path, ?string $name = null,?string $extension = null)
    {
        parent::__construct($path, $name, $extension);
    }

    /**
     * @param int $index
     * @return ImageFileResourceThumb|null
     */
    public function getThumb(): ?ImageFileResourceThumb
    {
        return $this->thumb;
    }

    /**
     * @param AbstractFileResource $thumb
     * @throws \Exception
     */
    public function setThumb(?AbstractFileResource $thumb = null):void
    {
        if($thumb != null && !$thumb instanceof ImageFileResourceThumb){
            throw new \Exception('Wrong class type, expected ImageFileResourceThumb');
        }
        $this->thumb = $thumb;
    }

    /**
     * @return ImageFileResourceBackup|null
     */
    public function getBackupResource(): ?ImageFileResourceBackup
    {
        return $this->backupResource;
    }

    /**
     * @param AbstractFileResource $backupResource
     * @throws \Exception
     */
    public function setBackupResource(AbstractFileResource $backupResource):void
    {
        if(!$backupResource instanceof AbstractImageFileResource){
            throw new \Exception('Wrong class type, expected ImageFileResourceThumb');
        }
        $this->backupResource = $backupResource;
    }

    /**
     * @return ImageFileResource|null
     */
    public function getLowPreloadQualityResource(): ?ImageFileResource
    {
        return $this->lowPreloadQualityResource;
    }

    /**
     * @param AbstractFileResource $lowQResource
     * @throws \Exception
     */
    public function setLowPreloadQualityResource(AbstractFileResource $lowQResource):void
    {
        if(!$lowQResource instanceof ImageFileResource){
            throw new \Exception('Wrong class type, expected ImageFileResourceThumb');
        }
        $this->lowPreloadQualityResource = $lowQResource;
    }

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->main;
    }

    /**
     * @param bool $isMain
     */
    public function setIsMain(bool $isMain): void
    {
        $this->main = $isMain;
    }

    /**
     * @return mixed
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * @param mixed $alt
     */
    public function setAlt($alt): void
    {
        $this->alt = $alt;
    }

    /**
     * @return mixed
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param mixed $caption
     */
    public function setCaption($caption): void
    {
        $this->caption = $caption;
    }

}