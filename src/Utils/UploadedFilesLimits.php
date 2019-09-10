<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

use Optimal\FileManaging\Exception\IniException;

class UploadedFilesLimits
{
    private $maxCount;
    private $maxFileSize;
    private $maxAllFilesSize;
    private $allowedExtensions;

    function __construct(){
        IniInfo::init();

        $this->maxCount = IniInfo::getMaxFilesCount();
        $this->maxFileSize = IniInfo::getMaxFileSize();
        $this->maxAllFilesSize = IniInfo::getPostMaxSize();
        $this->allowedExtensions = FilesTypes::ALL_SUPPORTED_FILES;
    }

    /**
     * @throws IniException
     */
    protected function checkIni(){

        if($this->maxCount > IniInfo::getMaxFilesCount()){
            throw new IniException("Chosen max count is greater than is allowed in php ini (".IniInfo::getMaxFilesCount().")");
        }

        if($this->maxFileSize > IniInfo::getMaxFileSize()){
            throw new IniException("Chosen max file size is greater than is allowed in php ini");
        }

        if($this->maxCount * $this->maxFileSize > IniInfo::getPostMaxSize()){
            throw new IniException("Chosen max count * max file size (".$this->maxCount."*".$this->maxFileSize.") is greater than ini max post size (".IniInfo::getPostMaxSize().")");
        }

        if($this->maxAllFilesSize > IniInfo::getPostMaxSize()){
            throw new IniException("Chosen max post size is greater than is allowed in php ini (".IniInfo::getPostMaxSize().")");
        }

    }

    /**
     * @param int $count
     * @throws IniException
     */
    public function setMaxCount(int $count){

        $oldCount = $this->maxCount;
        $this->maxCount = $count;

        try{
            $this->checkIni();
        } catch (IniException $e){
            $this->maxCount = $oldCount;
            throw $e;
        }
    }

    /**
     * @param string $size
     * @throws IniException
     */
    public function setMaxFileSize(string $size){

        $bytes = IniInfo::toBytes($size);
        $oldFileMaxSize = $this->maxFileSize;
        $this->maxFileSize = $bytes;

        try{
            $this->checkIni();
        } catch (IniException $e){
            $this->maxFileSize = $oldFileMaxSize;
            throw $e;
        }

    }

    /**
     * @param string $size
     * @throws IniException
     */
    public function setMaxPostSize(string $size){

        $bytes = IniInfo::toBytes($size);

        $oldAllFilesMaxSize = $this->maxAllFilesSize;
        $this->maxAllFilesSize = $bytes;

        try{
            $this->checkIni();
        } catch (IniException $e){
            $this->maxAllFilesSize = $oldAllFilesMaxSize;
            throw $e;
        }

    }

    /**
     * @param array $extensions
     */
    public function addAllowedExtensions(array $extensions){
        $intersection = array_intersect($this->allowedExtensions, $extensions);
        $this->allowedExtensions = array_merge($this->allowedExtensions, $intersection);
    }

    /**
     * @return int
     */
    public function getMaxFilesCount():int{
        return $this->maxCount;
    }

    /**
     * @return int
     */
    public function getMaxFileSize():int{
        return $this->maxFileSize;
    }

    /**
     * @return int
     */
    public function getMaxPostSize():int{
        return $this->maxAllFilesSize;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions():array {
        return $this->allowedExtensions;
    }

    /**
     * @return array
     */
    public function getDisAllowedExtensions(){
        return FilesTypes::DISALLOWED;
    }

}