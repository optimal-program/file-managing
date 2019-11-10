<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

class ImageResource extends FileResource
{

    const EXTENSION_JPG = "jpg";
    const EXTENSION_PNG = "png";
    const EXTENSION_GIF = "gif";
    const EXTENSION_WEBP = "webp";

    protected $width;
    protected $height;
    protected $orientation;

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

    protected function setFileInfo()
    {
        parent::setFileInfo();

        $exif = @exif_read_data($this->path."/".$this->name.".".$this->extension);

        if ($exif) {
            if (isset($exif["COMPUTED"]["Orientation"])) {
                $this->orientation = $exif["COMPUTED"]["Orientation"];
            } else {
                $this->orientation = 1;
            }
        }

        list($width, $height) = getimagesize($this->path."/".$this->name.".".$this->extension);
        $this->width = $width;
        $this->height = $height;

    }

    /**
     * @return int
     */
    public function getWidth():int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return ImageResource
     */
    public function setWidth(int $width):ImageResource
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param int $index
     * @return ImageThumbResource|null
     */
    public function getThumb(int $index): ?ImageThumbResource
    {
        if(isset($this->thumbs[$index])){
            return $this->thumbs[$index];
        }
        return null;
    }

    /**
     * @param ImageThumbResource $thumb
     * @param bool $main
     */
    public function addThumb(ImageThumbResource $thumb, bool $main = false):void
    {
        $thumb->setMain($main);
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
     * @return ImageBackupResource|null
     */
    public function getBackupResource(): ?ImageBackupResource
    {
        return $this->backupResource;
    }

    /**
     * @param ImageBackupResource $backupResource
     */
    public function setBackupResource(ImageBackupResource $backupResource):void
    {
        $this->backupResource = $backupResource;
    }

    /**
     * @return ImageResource|null
     */
    public function getLowPreloadQualityResource(): ?ImageResource
    {
        return $this->lowPreloadQualityResource;
    }

    /**
     * @param ImageResource $lowQResource
     */
    public function setLowPreloadQualityResource(ImageResource $lowQResource):void
    {
        $this->lowPreloadQualityResource = $lowQResource;
    }

    /**
     * @return int
     */
    public function getHeight():int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return ImageResource
     */
    public function setHeight(int $height):ImageResource
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrientation():int
    {
        return $this->orientation;
    }

    /**
     * @param int $orientation
     * @return ImageResource
     */
    public function setOrientation(int $orientation):ImageResource
    {
        $this->orientation = $orientation;
        return $this;
    }

    /**
     * @return bool
     */
    public function isJPG():bool{
        return $this->extension == "jpg" || $this->extension == "jpeg";
    }

    /**
     * @return bool
     */
    public function isPNG():bool{
        return $this->extension == "png";
    }

    /**
     * @return bool
     */
    public function isGIF():bool{
        return $this->extension == "gif";
    }

    /**
     * @param string $string
     * @return string
     */
    public function parseString(string $string):string
    {

        $string = parent::parseString($string);

        $string = str_replace("{width}", $this->width, $string);
        $string = str_replace("{height}", $this->height, $string);
        $string = str_replace("{orientation}", $this->orientation, $string);

        return $string;
    }

    public function castAs($newClass) {
        $obj = new $newClass;
        foreach (get_object_vars($this) as $key => $name) {
            $obj->$key = $name;
        }
        return $obj;
    }

}