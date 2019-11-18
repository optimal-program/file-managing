<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

class ImageFileResource extends AbstractImageFileResource
{
    protected $thumbs = [];
    protected $backupResource = null;
    protected $lowPreloadQualityResource = null;

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
     * @return ImageFileResourceThumb|null
     */
    public function getMainThumb():?ImageFileResourceThumb
    {
        foreach ($this->thumbs as $thumb){
            /** @var ImageFileResourceThumb $thumb*/
            if($thumb->isMain()){
                return $thumb;
            }
        }
        return null;
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
     * @param bool $isMain
     * @throws \Exception
     */
    public function addThumb(AbstractFileResource $thumb, bool $isMain = false):void
    {
        if(!$thumb instanceof ImageFileResourceThumb){
            throw new \Exception('Wrong class type, expected ImageFileResourceThumb');
        }
        $thumb->setIsMain($isMain);
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

}