<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\FileException;
use Optimal\FileManaging\Exception\FileNotFoundException;
use Optimal\FileManaging\Utils\FileAdditionalInfo;
use Optimal\FileManaging\Utils\SystemPaths;

abstract class AbstractFileResource
{

    protected $name;
    protected $newName = null;

    protected $extension;
    protected $newExtension = null;

    protected $size;

    protected $path;
    protected $newPath = null;

    protected $relativePath;

    private $dbId = null;
    private $customName = "";
    private $title = "";
    private $description = "";

    /**
     * AbstractFileResource constructor.
     * @param string $path
     * @param string|null $name
     * @param string|null $extension
     * @throws FileException
     */
    function __construct(string $path,?string $name = null,?string $extension = null){

        if(!file_exists($path)){
            throw new FileException("Path: ".$path." is not valid");
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
     * @return $this
     */
    public function setNewName(string $name){
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
     * @return $this
     */
    public function setNewExtension(string $extension){
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
     * @return $this
     */
    public function setNewPath(string $path){
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
     * @return mixed
     */
    public function getDbId()
    {
        return $this->dbId;
    }

    /**
     * @param mixed $dbId
     */
    public function setDbId($dbId): void
    {
        $this->dbId = $dbId;
    }

    /**
     * @return mixed
     */
    public function getCustomName()
    {
        return $this->customName;
    }

    /**
     * @param mixed $customName
     */
    public function setCustomName($customName): void
    {
        $this->customName = $customName;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
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
        $string = str_replace("{name}", $this->getDbId(), $string);
        $string = str_replace("{nameEx}", $this->getCustomName(), $string);
        $string = str_replace("{description}", $this->getDescription(), $string);

        return $string;
    }

}