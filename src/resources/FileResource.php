<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 08.10.2017
 * Time: 14:09
 */

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
     * @param $path
     * @param $name
     * @param null $extension
     * @throws FileException
     */
    function __construct($path, $name, $extension = null){

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
    public function getExtension(){
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getName(){
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNameExtension(){
        return $this->name.".".$this->extension;
    }

    /**
     * @return string
     */
    public function getNewNameExtension(){
        return ($this->newName != null ? $this->newName : $this->name).".".($this->newExtension != null ? $this->newExtension : $this->extension);
    }

    /**
     * @return string
     */
    public function getPath(){
        return $this->path;
    }

    /**
     * @return string
     */
    public function getPathToFile(){
        return $this->path."/".$this->getNameExtension();
    }

    /**
     * @return string
     */
    public function getRelativePathToFile(){
        return ltrim(str_replace(SystemPaths::getScriptPath(), "", $this->path), "/")."/". $this->name.".".$this->extension;
    }

    /**
     * @return string
     */
    public function getUrlToFile(){
        return SystemPaths::getBaseUrl() . "/" . $this->path ."/". $this->name.".".$this->extension;
    }

    /**
     * @return integer
     */
    public function getFileSize(){
        return $this->size;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setNewName($name){
        $this->newName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewName()
    {
        return $this->newName;
    }

    /**
     * @param string $extension
     * @return $this
     */
    public function setNewExtension($extension){
        $this->newExtension = $extension;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewExtension(){
        return $this->newExtension;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setNewPath($path){
        $this->newPath = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewPath(){
        return $this->newPath;
    }

    /**
     * @return string
     */
    public function getNewPathToFile(){

        if($this->newPath == null){
            return null;
        }

        $name = $this->getNewNameExtension();

        return $this->newPath."/".$name;
    }

    public function applyNewSettings(){
        $this->name = $this->newName;
        $this->extension = $this->newExtension;

        if(!file_exists($this->newPath)){
            throw new DirectoryNotFoundException("Directory ".$this->newPath." not found");
        }

        if(!file_exists($this->newPath."/".$this->name.".".$this->extension)){
            throw new FileNotFoundException("File ".$this->name.".".$this->extension." not found");
        }

        $this->path = $this->newPath;
        $this->setFileInfo();
    }

    /**
     * @param FileAdditionalInfo $info
     * @return $this
     */
    public function setAdditionalInfo(FileAdditionalInfo $info = null)
    {
        $this->additionalInfo = $info;
        return $this;
    }

    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }

    public function getDbId()
    {
        return $this->additionalInfo->getDbId();
    }

    public function getDbName()
    {
        return $this->additionalInfo->getName();
    }

    public function getDbNameExtension()
    {
        return $this->additionalInfo->getName() . "." . $this->getRealExtension();
    }

    public function getFileDescription()
    {
        return $this->additionalInfo->getDescription();
    }

    public function getFileTitle()
    {
        return $this->additionalInfo->getDescription();
    }

    /**
     * @param $string
     * @return mixed
     */
    public function parseString($string)
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
