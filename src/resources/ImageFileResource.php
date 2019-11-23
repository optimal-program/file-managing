<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

class ImageFileResource extends AbstractImageFileResource
{
    protected $thumbs = [];
    protected $backupResource = null;
    protected $lowPreloadQualityResource = null;
    protected $main;

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
    public function getThumb(int $index): ?ImageFileResourceThumb
    {
        if(isset($this->thumbs[$index])){
            return $this->thumbs[$index];
        }
        return null;
    }

    /**
     * @param AbstractFileResource $thumb
     * @throws \Exception
     */
    public function addThumb(AbstractFileResource $thumb):void
    {
        if(!$thumb instanceof ImageFileResourceThumb){
            throw new \Exception('Wrong class type, expected ImageFileResourceThumb');
        }
        $this->thumbs[] = $thumb;
    }

    /**
     * @param int $index
     */
    public function removeThumb(int $index){
        if(isset($this->thumbs[$index])){
            unset($this->thumbs[$index]);
        }
        $this->thumbs = array_values($this->thumbs);
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

}