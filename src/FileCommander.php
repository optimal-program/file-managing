<?php

namespace Optimal\FileManaging;

use Optimal\FileManaging\Exception\CreateDirectoryException;
use Optimal\FileManaging\Exception\CreateFileException;
use Optimal\FileManaging\Exception\DeleteDirectoryException;
use Optimal\FileManaging\Exception\DeleteFileException;
use Optimal\FileManaging\Exception\DirectoryException;
use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\FileException;
use Optimal\FileManaging\Exception\FileNotFoundException;
use Optimal\FileManaging\resources\ImageResource;
use Optimal\FileManaging\Utils\SystemPaths;
use Optimal\FileManaging\Utils\UploadedFilesLimits;
use Optimal\FileManaging\resources\FileResource;

class FileCommander
{
    private $actualPath = null;
    private $basePath = null;

    private $dirs  = [];
    private $files = [];

    /**
     * @param $path
     * @return string
     * @throws DirectoryNotFoundException
     */
    public function checkPath($path)
    {
        if (!file_exists($path)) {
            throw new DirectoryNotFoundException("Directory: " . $path . " not found, check whether you typed path to directory from root of project");
        }
        return $path;
    }

    /**
     * @param $path
     * @throws DirectoryNotFoundException
     */
    public function setPath($path)
    {
        try {
            $path = str_replace("\\", "/", $path);
            $path = $this->checkPath($path);
        } catch (DirectoryNotFoundException $e) {
            throw $e;
        }

        $this->actualPath = $path;
        $this->basePath = $path;
    }

    /**
     * @return null
     * @throws DirectoryNotFoundException
     */
    public function getAbsolutePath()
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");
        return $this->actualPath;
    }

    /**
     * @return string
     * @throws DirectoryNotFoundException
     */
    public function getRelativePath()
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");
        return ltrim(str_replace(SystemPaths::getScriptPath(), "", $this->actualPath), "/");
    }

    /**
     * @return string
     * @throws DirectoryNotFoundException
     */
    public function getUrlToDirectory(){
        return SystemPaths::getUrlDomain()."/".$this->getRelativePath();
    }

    /**
     * @param array $files
     * @return array
     */
    private function sortFiles($files) {
        return usort($files, "strnatcasecmp");
    }

    /**
     * @param $name
     * @throws DirectoryNotFoundException
     */
    public function moveToDir($name)
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        $name = rtrim($name, "/");

        if ($this->directoryExists($name)) {
            $this->actualPath .= "/" . $name;
        } else {
            throw new DirectoryNotFoundException("Directory: " . $name . " is not exists");
        }
    }

    /**
     * @throws DirectoryNotFoundException
     */
    public function moveUp()
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        if ($this->basePath != $this->actualPath) {
            $parts = explode("/", $this->actualPath);
            unset($parts[ count($parts) - 1 ]);
            $this->actualPath = implode("/", $parts);
        }
    }

    /**
     * @param $name
     * @return bool
     * @throws DirectoryNotFoundException
     */
    public function directoryExists($name)
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");
        return file_exists($this->actualPath . "/" . $name) && is_dir($this->actualPath . "/" . $name);
    }

    /**
     * @param $dirName
     * @param bool $moveToDir
     * @param int $chmod
     * @throws CreateDirectoryException
     * @throws DirectoryNotFoundException
     */
    public function addDirectory($dirName, $moveToDir = false, $chmod = 0777)
    {

        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        $dirName = rtrim($dirName, "/");

        if (!$this->directoryExists($dirName)) {

            umask(0000);
            if (!mkdir($this->actualPath . "/" . $dirName, $chmod)) {
                throw new CreateDirectoryException("Create directory " . $dirName . " into " . $this->actualPath . " wasn't successful, maybe access rights problem.");
            }

            if ($moveToDir) {
                $this->moveToDir($dirName);
            }

        }

    }

    /**
     * @param bool $sort
     * @return array|bool|is_dir
     * @throws DirectoryNotFoundException
     */
    public function getDirectories($sort = true)
    {
        if ($this->actualPath == null) {
            throw new DirectoryNotFoundException("No directory set");
        }
        $dirs = array_filter(glob(".*"), 'is_dir');

        if ($sort) {
            return $this->sortFiles($dirs);
        } else {
            return $dirs;
        }
    }

    /**
     * @param string $regex
     * @param bool $sort
     * @return array|bool|is_dir
     * @throws DirectoryNotFoundException
     */
    public function searchDirectories($regex = ".*", $sort = true){
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");
        $dirs = array_filter(glob($regex), 'is_dir');

        if ($sort) {
            return $this->sortFiles($dirs);
        } else {
            return $dirs;
        }
    }

    /**
     * @param $name
     * @throws DeleteDirectoryException
     * @throws DeleteFileException
     * @throws DirectoryException
     * @throws DirectoryNotFoundException
     */
    public function removeDir($name)
    {

        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        if ($this->directoryExists($name)) {
            try {
                $this->DeleteDir($this->actualPath . " / " . $name);
            } catch (DeleteDirectoryException $e) {
                throw $e;
            }
            if (!rmdir($this->actualPath . " / " . $name)) {
                throw new DeleteDirectoryException("Remove directory: " . $name . " wasn't successful");
            }
        } else {
            throw new DirectoryNotFoundException("Directory : " . $name . " not found");
        }

    }

    /**
     * @param $name
     * @throws DeleteDirectoryException
     * @throws DeleteFileException
     * @throws DirectoryException
     * @throws DirectoryNotFoundException
     */
    public function clearDir($name)
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        if ($this->directoryExists($name)) {
            try {
                $this->DeleteDir($this->actualPath . "/" . $name);
            } catch (DeleteDirectoryException $e) {
                throw $e;
            } catch (DeleteFileException $e) {
                throw $e;
            }
        } else {
            throw new DirectoryNotFoundException("Directory : " . $name . " not found");
        }
    }

    /**
     * @param $path
     * @throws DeleteDirectoryException
     * @throws DeleteFileException
     */
    private function DeleteDir($path)
    {
        $files = array_diff(scandir($path), array(' . ', ' ..'));

        foreach ($files as $file) {
            if (is_dir($path . ' / ' . $file)) {
                $this->DeleteDir($path . ' / ' . $file);
                if (!rmdir($path . ' / ' . $file)) {
                    throw new DeleteDirectoryException("Remove directory: " . $file . " wasn't successful");
                }
            } else {
                if (!unlink($path . '/' . $file)) {
                    throw new DeleteFileException("Remove file: " . $file . " wasn't successful");
                }
            }
        }

    }

    /**
     * @param $name
     * @param $newName
     * @param bool $recursive
     * @throws DirectoryException
     * @throws DirectoryNotFoundException
     */
    public function renameDir($name, $newName, $recursive = false) {

        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        if($this->directoryExists($name)) {
            if (rename($this->actualPath . ' / ' . $name, $this->actualPath . "/" . $newName)) {
                if ($recursive) {
                    $this->renameDirRec($this->actualPath, $name, $newName);
                }
            } else {
                throw new DirectoryException("Rename directory: " . $name . " wasn't successful");
            }
        } else {
            throw new DirectoryNotFoundException("Directory : " . $name . " not found");
        }
    }

    /**
     * @param $path
     * @param $name
     * @param $newName
     * @throws DirectoryException
     * @throws DirectoryNotFoundException
     */
    private function renameDirRec($path, $name, $newName)
    {

        foreach ($this->getDirectories() as $dir) {

            if ($dir == $name) {
                if (!rename($path . ' / ' . $dir, $path . "/" . $newName)) {
                    throw new DirectoryException("Rename directory: " . $dir . " wasn't successful");
                }
            }

            $this->renameDirRec($path . ' / ' . $dir, $name, $newName);
        }

    }

    /**
     * @param $name
     * @param null $extension
     * @return bool
     * @throws DirectoryNotFoundException
     */
    public function fileExists($name, $extension = null)
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        if($extension == null){
            $parts = explode(".", $name);
            $name = $parts[0];
            $extension = strtolower($parts[1]);
        }

        return file_exists($this->actualPath . "/" . $name.".".$extension);
    }


    /**
     * @param $extension
     * @return bool
     */
    public function isImage($extension)
    {
        $extension = strtolower($extension);
        $imagesExtension = ["jpg", "png", "jpeg", "gif", "tiff", "bmp"];
        return in_array($extension, $imagesExtension);
    }

    /**
     * @param string $pattern
     * @param bool $sort
     * @return array
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    protected function getFilesRegex($pattern = ".*", $sort = true){

        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        $foundFiles = array_filter(glob($pattern), function ($k, $v){
            return !is_dir($v);
        }, ARRAY_FILTER_USE_BOTH);

        if($sort){
            $foundFiles = $this->sortFiles($foundFiles);
        }

        $fileResources = [];

        foreach ($foundFiles as $file){
            if(!$this->isImage(pathinfo($this->actualPath.".".$file, PATHINFO_EXTENSION))) {
                $fileResource = new FileResource($this->actualPath, $file);
                array_push($fileResources, $fileResource);
            }
        }

        return $fileResources;

    }

    /**
     * @param $name
     * @param null $extension
     * @return FileResource
     * @throws FileException
     */
    public function getFile($name, $extension = null){

        if($extension == null){
            $parts = explode(".", $name);
            $name = $parts[0];
            $extension = strtolower($parts[1]);
        }

        return new FileResource($this->actualPath, $name, $extension);
    }

    /**
     * @param bool $sort
     * @return array
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function getFiles($sort = true){
        return $this->getFilesRegex(".*", $sort);
    }

    /**
     * @param string $pattern
     * @param bool $sort
     * @return array
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function searchFiles($pattern = ".*", $sort = true){
        return $this->getFilesRegex($pattern, $sort);
    }

    /**
     * @param string $pattern
     * @param bool $sort
     * @return array
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    protected function getImagesRegex($pattern = ".*", $sort = true){

        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        $foundImages = array_filter(glob($pattern), function ($k, $v){
            return !is_dir($v);
        }, ARRAY_FILTER_USE_BOTH);

        if($sort){
            $foundImages = $this->sortFiles($foundImages);
        }

        $imageResources = [];

        foreach ($foundImages as $image){
            if($this->isImage(pathinfo($this->actualPath.".".$image, PATHINFO_EXTENSION))) {
                $imageResource = new ImageResource($this->actualPath, $image);
                array_push($imageResources, $imageResource);
            }
        }

        return $imageResources;

    }

    /**
     * @param $name
     * @param null $extension
     * @return ImageResource
     * @throws FileException
     */
    public function getImage($name, $extension = null){

        if($extension == null){
            $parts = explode(".", $name);
            $name = $parts[0];
            $extension = strtolower($parts[1]);
        }

        return new ImageResource($this->actualPath, $name, $extension);
    }

    /**
     * @param bool $sort
     * @return array
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function getImages($sort = true){
        return $this->getImagesRegex(".*", $sort);
    }

    /**
     * @param string $pattern
     * @param bool $sort
     * @return array
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function searchImages($pattern = ".*", $sort = true){
        return $this->getImagesRegex($pattern, $sort);
    }

    /**
     * @return FileResource|null
     * @throws FileException
     *
    public function getLastFile()
    {
        if (!empty($this->files)) {
            $lastFile = $this->files[ count($this->files) - 1 ];
            $name = pathinfo($this->getActualPath() . "/" . $lastFile, PATHINFO_FILENAME);
            $extension = pathinfo($this->getActualPath() . "/" . $lastFile, PATHINFO_EXTENSION);
            return $this->getFile($this->getActualPath(), $name, $extension);
        } else {
            return null;
        }
    }
    */

    /**
     * @param string $name
     * @param string $extension
     * @param string $content
     * @return bool
     * @throws CreateFileException
     * @throws DirectoryNotFoundException
     */
    public function createFile($name, $extension, $content = "\n")
    {

        if ($name != "") {

            if(file_exists($this->actualPath."/".$name)) {
                $parts = explode(".", $name);
                $name = $parts[0];
                $extension = strtolower($parts[1]);
            }

            if ($extension != "") {
                if (!in_array($extension, UploadedFilesLimits::DISALLOWED)) {

                    if (!$this->fileExists($name, $extension)) {
                        $f = fopen($this->actualPath . "/" . $name, "w+");
                        $f = fwrite($f, $content);
                        fclose($f);
                        return true;
                    }

                } else {
                    throw new CreateFileException("Extension: " . $extension . " of file: " . $name . " is not allowed");
                }
            } else {
                throw new CreateFileException("Extension of file: " . $name . " is not defined");
            }
        } else {
            throw new CreateFileException("No file name is defined");
        }

        return false;
    }

    /**
     * @param string $name
     * @param string $extension
     * @param string $content
     * @param bool $append
     * @throws CreateFileException
     */
    public function writeToFile($name, $extension, $content = "\n", $append = true)
    {

        if ($name != "") {

            if (file_exists($this->actualPath . "/" . $name)) {
                $parts = explode(".", $name);
                $name = $parts[0];
                $extension = strtolower($parts[1]);
            }

            if ($extension != "") {

                if ($append) {
                    $currentData = file_get_contents($this->actualPath . "/" . $name.".".$extension);
                    file_put_contents($this->actualPath . "/" . $name.".".$extension, $currentData . $content);
                } else {
                    file_put_contents($this->actualPath . "/" . $name.".".$extension, $content);
                }

            } else {
                throw new CreateFileException("Extension of file: " . $name . " is not defined");
            }
        } else {
            throw new CreateFileException("No file name is defined");
        }

    }

    /**
     * @param $name
     * @param null $extension
     * @param string $newName
     * @return bool
     * @throws FileException
     */
    public function renameFileTo($name, $extension = null, $newName = "")
    {

        if ($name != "") {
            if($newName != "") {

                if ($extension == null) {
                    $parts = explode(".", $name);
                    $name = $parts[0];
                    $extension = strtolower($parts[1]);
                }

                if (rename($this->actualPath . "/" . $name . "." . $extension, $this->actualPath . "/" . $newName . "." . $extension)) {
                    return true;
                }

                return false;

            } else {
                throw new FileException("No file new name is defined");
            }
        } else {
            throw new FileException("No file name is defined");
        }

    }

    /**
     * @param $path
     * @param $name
     * @param null $extension
     * @param null $renameTo
     * @return bool
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function copyFileFromAnotherDirectory($path, $name, $extension = null, $renameTo = null) {

        if(file_exists($path) && is_dir($path)){

            if ($name != "") {

                if($extension == null){
                    $parts = explode(".", $name);
                    $name = $parts[0];
                    $extension = strtolower($parts[1]);
                }

                if(copy($path."/".$name.".".$extension, $this->actualPath."/".($renameTo != null ? $renameTo : $name).".".$extension)){
                    return true;
                }

                return false;

            } else {
                throw new FileException("File name is not defined");
            }

        } else {
            throw new DirectoryNotFoundException("Directory ".$path." not found");
        }

    }

    /**
     * @param $path
     * @param $name
     * @param null $extension
     * @param null $renameTo
     * @return bool
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function copyFileToAnotherDirectory($path, $name, $extension = null, $renameTo = null) {

        if(file_exists($path) && is_dir($path)){

            if ($name != "") {

                if($extension == null){
                    $parts = explode(".", $name);
                    $name = $parts[0];
                    $extension = strtolower($parts[1]);
                }

                if(copy($this->actualPath."/".$name.".".$extension, $path."/".($renameTo != null ? $renameTo : $name).".".$extension)){
                    return true;
                }

                return false;

            } else {
                throw new FileException("File name is not defined");
            }

        } else {
            throw new DirectoryNotFoundException("Directory ".$path." not found");
        }

    }

    /**
     * @param $pattern
     * @throws DeleteFileException
     */
    public function removeFile($pattern)
    {
        if ($pattern != "") {

            $files = glob($pattern);

            foreach ($files as $file) {
                if (!unlink($this->actualPath . "/" . $file)) {
                    throw new DeleteFileException("Remove file: " . $file . " wasn't successful");
                }
            }

        } else {
            throw new DeleteFileException("No pattern defined to delete file/s");
        }

    }

}