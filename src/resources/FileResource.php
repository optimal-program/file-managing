<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\FileException;
use Optimal\FileManaging\Exception\FileNotFoundException;
use Optimal\FileManaging\Utils\FileAdditionalInfo;
use Optimal\FileManaging\Utils\SystemPaths;

class FileResource
{

    protected $name;
    protected $newName = null;

    protected $extension;
    protected $newExtension = null;

    protected $size;

    protected $path;
    protected $newPath = null;

    protected $relativePath;
    protected $additionalInfo;

    /**
     * FileResource constructor.
     * @param string $path
     * @param string $name
     * @param string|null $extension
     * @throws FileException
     */
    function __construct(string $path,string $name,?string $extension = null){

        if(!file_exists($path)){
            throw new FileException("Path is not valid");
        }

        if(!is_dir($path)){
            $name = pathinfo($path, PATHINFO_FILENAME);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $path = pathinfo($path, PATHINFO_DIRNAME);
        } else {
            if($extension == null){
                $parts = explode(".", $name);
                $name = $parts[0];
                $extension = $parts[1];
            }
        }

        if(file_exists($path."/".$name.".".$extension)){
            $this->name = $name;
            $this->extension = $extension;
            $this->path = $path;
            $this->setFileInfo();
        } else {
            throw new FileException("File ".$name.".".$extension." not found");
        }

    }

    protected function setFileInfo(){
        $this->size = filesize($this->path."/".$this->name.".".$this->extension);
    }

    /**
     * @return string
     */
    public function getExtension():string {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getName():string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNameExtension():string {
        return $this->name.".".$this->extension;
    }

    /**
     * @return string
     */
    public function getNewNameExtension():string {
        return ($this->newName != null ? $this->newName : $this->name).".".($this->newExtension != null ? $this->newExtension : $this->extension);
    }

    /**
     * @return string
     */
    public function getFileDirectoryPath():string {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getFilePath():string {
        return $this->path."/".$this->getNameExtension();
    }

    /**
     * @return string
     */
    public function getFileRelativePath():string {
        return ltrim(str_replace(SystemPaths::getScriptPath(), "", $this->path), "/")."/". $this->name.".".$this->extension;
    }

    /**
     * @return string
     */
    public function getUrlToFile():string {
        return SystemPaths::getBaseUrl() . "/" . $this->path ."/". $this->name.".".$this->extension;
    }

    /**
     * @return int
     */
    public function getFileSize():int {
        return $this->size;
    }

    /**
     * @param string $name
     * @return FileResource
     */
    public function setNewName(string $name):FileResource{
        $this->newName = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNewName():?string
    {
        return $this->newName;
    }

    /**
     * @param string $extension
     * @return FileResource
     */
    public function setNewExtension(string $extension):FileResource{
        $this->newExtension = $extension;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNewExtension():?string {
        return $this->newExtension;
    }

    /**
     * @param string $path
     * @return FileResource
     */
    public function setNewPath(string $path):FileResource{
        $this->newPath = $path;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileNewDirectoryPath():?string {
        return $this->newPath;
    }

    /**
     * @return string|null
     */
    public function getFileNewPath():?string {

        if($this->newPath == null){
            return null;
        }

        $name = $this->getNewNameExtension();

        return $this->newPath."/".$name;
    }

    public function applyNewSettings(){
        if($this->newName != null) {
            $this->name = $this->newName;
        }
        if($this->newExtension != null) {
            $this->extension = $this->newExtension;
        }
        if($this->newPath != null) {
            if (!file_exists($this->newPath)) {
                throw new DirectoryNotFoundException("Directory " . $this->newPath . " not found");
            }
            if (!file_exists($this->newPath . "/" . $this->name . "." . $this->extension)) {
                throw new FileNotFoundException("File " . $this->name . "." . $this->extension . " not found");
            }
            $this->path = $this->newPath;
        }

        $this->setFileInfo();
    }

    /**
     * @param FileAdditionalInfo|null $info
     * @return FileResource
     */
    public function setAdditionalInfo(FileAdditionalInfo $info = null):FileResource
    {
        $this->additionalInfo = $info;
        return $this;
    }

    /**
     * @return FileAdditionalInfo
     */
    public function getAdditionalInfo():FileAdditionalInfo
    {
        return $this->additionalInfo;
    }

    /**
     * @return string|null
     */
    public function getDbId():?string
    {
        return $this->additionalInfo->getDbId();
    }

    /**
     * @return string|null
     */
    public function getDbName():?string
    {
        return $this->additionalInfo->getName();
    }

    /**
     * @return string|null
     */
    public function getDbNameExtension():?string
    {
        return $this->additionalInfo->getName() . "." . $this->getRealExtension();
    }

    /**
     * @return string|null
     */
    public function getFileDescription():?string
    {
        return $this->additionalInfo->getDescription();
    }

    /**
     * @return string|null
     */
    public function getFileTitle():?string
    {
        return $this->additionalInfo->getDescription();
    }

    /**
     * @param string $string
     * @return string
     */
    public function parseString(string $string):string
    {

        $string = str_replace("{realName}", $this->getName(), $string);
        $string = str_replace("{realExtension}", $this->getExtension(), $string);
        $string = str_replace("{realNameEx}", $this->getNameExtension(), $string);
        $string = str_replace("{realFileSize}", $this->getFileSize(), $string);
        $string = str_replace("{name}", $this->getDbName(), $string);
        $string = str_replace("{nameEx}", $this->getDbNameExtension(), $string);
        $string = str_replace("{description}", $this->getFileDescription(), $string);

        return $string;
    }

}