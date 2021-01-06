<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

use Optimal\FileManaging\Exception\IniException;

class FileUploaderUploadLimits
{
    private $iniMaxCount;
    private $iniMaxFileSize;
    private $iniMaxAllFilesSize;

    private $maxCount;
    private $maxFileSize;
    private $maxFileSizeStr;
    private $maxAllFilesSize;
    private $maxAllFilesSizeStr;
    private $allowedExtensions;

    function __construct()
    {
        IniInfo::init();
        $this->iniMaxCount = $this->maxCount = IniInfo::getMaxFilesCount();
        $this->iniMaxFileSize = $this->maxFileSize = IniInfo::getMaxFileSize();
        $this->iniMaxAllFilesSize = $this->maxAllFilesSize = IniInfo::getPostMaxSize();
        $this->allowedExtensions = FilesTypes::ALL_SUPPORTED_FILES;
    }

    /**
     * @param int|null $maxCount
     * @param string|null $maxFileSizeStr
     * @param string|null $maxAllFilesSizeStr
     * @throws IniException
     */
    protected function checkIni(?int $maxCount = null, ?string $maxFileSizeStr = null, ?string $maxAllFilesSizeStr = null) {

        if(!$maxCount){
            $count = $this->maxCount;
        }

        if(!$maxAllFilesSizeStr){
            $maxFileSizesBytes = $this->maxFileSize;
            $maxFileSizeStr = $this->maxFileSizeStr;
        } else {
            $maxFileSizesBytes = IniInfo::toBytes($maxAllFilesSizeStr);
        }

        if(!$maxAllFilesSizeStr){
            $maxAllFilesSizeBytes = $this->maxAllFilesSize;
            $maxAllFilesSizeStr = $this->maxAllFilesSizeStr;
        } else {
            $maxAllFilesSizeBytes = IniInfo::toBytes($maxAllFilesSizeStr);
        }

        if($maxCount > $this->iniMaxCount){
            throw new IniException("Chosen max count is greater than is allowed in php ini (".$this->maxCount.")");
        }

        if($maxFileSizesBytes > $this->iniMaxFileSize){
            throw new IniException("Chosen max file size is greater than is allowed in php ini (".IniInfo::getMaxFileSize(false).")");
        }

        if($maxAllFilesSizeBytes > $this->iniMaxAllFilesSize){
            throw new IniException("Chosen max post size is greater than is allowed in php ini (".IniInfo::getPostMaxSize(false).")");
        }

        if(($maxCount * $maxFileSizesBytes) > $maxAllFilesSizeBytes){
            throw new IniException("Number of max files * max file size (".$count."*".$maxFileSizeStr.") is greater than max post size (".$maxAllFilesSizeStr.")");
        }

    }

    /**
     * @param int $count
     * @throws IniException
     */
    public function setMaxCount(int $count)
    {
        try{
            $this->checkIni($count);
            $this->maxCount = $count;
        } catch (IniException $e){
            throw $e;
        }
    }

    /**
     * @param string $size
     * @throws IniException
     */
    public function setMaxFileSize(string $size)
    {
        try{
            $this->checkIni(null, $size);
            $this->maxFileSize = IniInfo::toBytes($size);
            $this->maxFileSizeStr = $size;
        } catch (IniException $e){
            throw $e;
        }
    }

    /**
     * @param string $size
     * @throws IniException
     */
    public function setMaxPostSize(string $size)
    {
        try{
            $this->checkIni(null, null, $size);
            $this->maxAllFilesSize = IniInfo::toBytes($size);
            $this->maxAllFilesSizeStr = $size;
        } catch (IniException $e){
            throw $e;
        }
    }

    /**
     * @param array $extensions
     */
    public function setAllowedExtensions(array $extensions)
    {
        $this->allowedExtensions = $extensions;
    }

    /**
     * @param array $extensions
     */
    public function addAllowedExtensions(array $extensions)
    {
        $intersection = array_intersect($this->allowedExtensions, $extensions);
        $this->allowedExtensions = array_merge($this->allowedExtensions, $intersection);
    }

    /**
     * @return int
     */
    public function getMaxFilesCount():int
    {
        return $this->maxCount;
    }

    /**
     * @return int
     */
    public function getMaxFileSize():int
    {
        return $this->maxFileSize;
    }

    /**
     * @return int
     */
    public function getMaxPostSize():int
    {
        return $this->maxAllFilesSize;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions():array
    {
        return $this->allowedExtensions;
    }

    /**
     * @return array
     */
    public function getDisAllowedExtensions()
    {
        return FilesTypes::DISALLOWED;
    }

}