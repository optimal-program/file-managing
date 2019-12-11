<?php declare(strict_types=1);

namespace Optimal\FileManaging;

use Optimal\FileManaging\Exception\CreateDirectoryException;
use Optimal\FileManaging\Exception\CreateFileException;
use Optimal\FileManaging\Exception\DeleteDirectoryException;
use Optimal\FileManaging\Exception\DeleteFileException;
use Optimal\FileManaging\Exception\DirectoryException;
use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\FileException;
use Optimal\FileManaging\Exception\FileNotFoundException;
use Optimal\FileManaging\resources\ImageFileResourceBackup;
use Optimal\FileManaging\resources\ImageFileResource;
use Optimal\FileManaging\Utils\FilesTypes;
use Optimal\FileManaging\Utils\SystemPaths;
use Optimal\FileManaging\Utils\UploadedFilesLimits;
use Optimal\FileManaging\resources\FileResource;

class FileCommander
{
    private $actualPath = null;
    private $basePath = null;

    /**
     * @param string $path
     * @return string
     * @throws DirectoryNotFoundException
     */
    public function checkPath(string $path):string
    {
        if (!file_exists($path)) {
            throw new DirectoryNotFoundException("Directory: " . $path . " not found, check whether you typed path to directory from root of project");
        }
        return $path;
    }

    /**
     * @param string $path
     * @throws DirectoryNotFoundException
     */
    public function setPath(string $path):void
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
     * @return string|null
     * @throws DirectoryNotFoundException
     */
    public function getAbsolutePath():?string
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");
        return $this->actualPath;
    }

    /**
     * @return string
     * @throws DirectoryNotFoundException
     */
    public function getRelativePath():string
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");
        return ltrim(str_replace(SystemPaths::getScriptPath(), "", $this->actualPath), "/");
    }

    /**
     * @return string
     * @throws DirectoryNotFoundException
     */
    public function getUrlToDirectory():string
    {
        return SystemPaths::getUrlDomain()."/".$this->getRelativePath();
    }

    /**
     * @param array $files
     * @return array
     */
    private function sortFiles(array $files):array {
        usort($files, "strnatcasecmp");
        return $files;
    }

    /**
     * @param string $name
     * @throws DirectoryNotFoundException
     */
    public function moveToDirectory(string $name)
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
     * @param string $name
     * @return bool
     * @throws DirectoryNotFoundException
     */
    public function directoryExists(string $name):bool
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");
        return file_exists($this->actualPath . "/" . $name) && is_dir($this->actualPath . "/" . $name);
    }

    /**
     * @param string $dirName
     * @param bool $moveToDir
     * @param int $chmod
     * @throws CreateDirectoryException
     * @throws DirectoryNotFoundException
     */
    public function addDirectory(string $dirName,bool $moveToDir = false,int $chmod = 0777)
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        $dirName = rtrim($dirName, "/");

        if (!$this->directoryExists($dirName)) {
            umask(0000);
            if (!mkdir($this->actualPath . "/" . $dirName, $chmod)) {
                throw new CreateDirectoryException("Create directory " . $dirName . " into " . $this->actualPath . " wasn't successful, maybe access rights problem.");
            }
        }

        if ($moveToDir) {
            $this->moveToDirectory($dirName);
        }
    }

    /**
     * @param bool $sort
     * @return array
     * @throws DirectoryNotFoundException
     */
    public function getDirectories(bool $sort = true):array
    {
        if ($this->actualPath == null) {
            throw new DirectoryNotFoundException("No directory set");
        }

        $dirs = array_filter(scandir($this->actualPath), function ($v){
            return is_dir($this->actualPath."/".$v) && !in_array($v, [".",".."]);
        });

        if ($sort) {
            return $this->sortFiles($dirs);
        } else {
            return $dirs;
        }
    }

    /**
     * @param string $regex
     * @param bool $sort
     * @return array
     * @throws DirectoryNotFoundException
     */
    public function searchDirectories(string $regex = ".*",bool $sort = true):array
    {
        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        $dirs = preg_grep("~$regex~", array_filter(scandir($this->actualPath), function ($v){
           return is_dir($this->actualPath."/".$v) && !in_array($v, [".",".."]);
        }));

        if ($sort) {
            return $this->sortFiles($dirs);
        } else {
            return $dirs;
        }
    }

    /**
     * @param string $name
     * @throws DeleteDirectoryException
     * @throws DeleteFileException
     * @throws DirectoryNotFoundException
     */
    public function removeDir(string $name)
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
     * @param string $name
     * @throws DeleteDirectoryException
     * @throws DeleteFileException
     * @throws DirectoryNotFoundException
     */
    public function clearDir(string $name)
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
     * @param string $path
     * @throws DeleteDirectoryException
     * @throws DeleteFileException
     */
    private function DeleteDir(string $path)
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
     * @param string $name
     * @param string $newName
     * @param bool $recursive
     * @throws DirectoryException
     * @throws DirectoryNotFoundException
     */
    public function renameDir(string $name,string $newName,bool $recursive = false) {

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
     * @param string $path
     * @param string $name
     * @param string $newName
     * @throws DirectoryException
     * @throws DirectoryNotFoundException
     */
    private function renameDirRec(?string $path, string $name,string $newName)
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
     * @param string $name
     * @param string|null $extension
     * @return bool
     * @throws DirectoryNotFoundException
     */
    public function fileExists(string $name,?string $extension = null):bool
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
     * @param string $extension
     * @return bool
     */
    public function isImage(string $extension):bool
    {
        $extension = strtolower($extension);
        $imagesExtension = ["jpg", "png", "jpeg", "gif", "tiff", "bmp"];
        return in_array($extension, $imagesExtension);
    }

    /**
     * @param string $pattern
     * @param bool $sort
     * @return FileResource[]
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    protected function getFilesRegex(string $pattern = ".*",bool $sort = true):array
    {

        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        $foundFiles = preg_grep("~$pattern~", array_filter(scandir($this->actualPath), function ($v){
            return !is_dir($v);
        }));

        if($sort){
            $foundFiles = $this->sortFiles($foundFiles);
        }

        $fileResources = [];
        $actualPath = (string) $this->actualPath;

        foreach ($foundFiles as $file){
            if(!$this->isImage(pathinfo($this->actualPath.".".$file, PATHINFO_EXTENSION))) {
                $fileResource = new FileResource($actualPath, $file);
                array_push($fileResources, $fileResource);
            }
        }

        return $fileResources;

    }

    /**
     * @param string $name
     * @param string|null $extension
     * @return FileResource
     * @throws DirectoryNotFoundException
     * @throws FileException
     * @throws FileNotFoundException
     */
    public function getFile(string $name,?string $extension = null){

        if($extension == null){
            $parts = explode(".", $name);
            $name = $parts[0];
            $extension = strtolower($parts[1]);
        }

        if(!$this->fileExists($name, $extension)){
            throw new FileNotFoundException("File: ".$name.".".$extension." not found in ".$this->getRelativePath());
        }

        $actualPath = (string) $this->actualPath;
        return new FileResource($actualPath, $name, $extension);
    }

    /**
     * @param bool $sort
     * @return FileResource[]
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function getFiles(bool $sort = true):array
    {
        return $this->getFilesRegex(".*", $sort);
    }

    /**
     * @param string $pattern
     * @param bool $sort
     * @return FileResource[]
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function searchFiles(string $pattern = ".*",bool $sort = true):array {
        return $this->getFilesRegex($pattern, $sort);
    }

    /**
     * @param string $pattern
     * @param bool $sort
     * @param bool $addBackupImage
     * @param bool $addThumbs
     * @return array
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    protected function getImagesRegex(string $pattern = ".*",bool $sort = true,bool $addBackupImage = true, bool $addThumbs = true):array {

        if($this->actualPath == null) throw new DirectoryNotFoundException("No directory set");

        $foundImages = preg_grep("~$pattern~", array_filter(scandir($this->actualPath), function ($v){
            return !is_dir($v);
        }));

        if($sort){
            $foundImages = $this->sortFiles($foundImages);
        }

        $imageResources = [];

        foreach ($foundImages as $image){
            $name = pathinfo($this->actualPath."./".$image, PATHINFO_FILENAME);
            $ext = pathinfo($this->actualPath."./".$image, PATHINFO_EXTENSION);
            if($this->isImage($ext)) {
                array_push($imageResources, $this->getImage($name, $ext, $addBackupImage, $addThumbs));
            }
        }

        return $imageResources;

    }

    /**
     * @param string $name
     * @param string|null $extension
     * @param bool $addBackupImage
     * @param bool $addThumb
     * @return ImageFileResource
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function getImage(string $name,?string $extension = null,bool $addBackupImage = true, bool $addThumb = true):ImageFileResource
    {

        if($extension == null){
            $parts = explode(".", $name);
            $name = $parts[0];
            $extension = strtolower($parts[1]);
        }

        $actualPath = (string) $this->actualPath;

        $imageResource = new ImageFileResource($actualPath, $name, $extension);

        if($addBackupImage && $this->directoryExists("backup")){
            $this->moveToDirectory("backup");
            $backupImage = $this->getImage($imageResource->getName(), $imageResource->getExtension(), false, false);
            if($backupImage != null) {
                $imageResource->setBackupResource($backupImage);
            }
            $this->moveUp();
        }

        if($this->fileExists($imageResource->getName()."_thumb", $imageResource->getExtension())){
            $imageThumb = $this->getImage($imageResource->getName()."_thumb", $imageResource->getExtension(), false, false);
            $imageResource->setThumb($imageThumb);
        } else {
            if($this->fileExists($imageResource->getName()."_thumb", "png")){
                $imageThumb = $this->getImage($imageResource->getName()."_thumb", "png", false, false);
                $imageResource->setThumb($imageThumb);
            }
        }

        return $imageResource;
    }

    /**
     * @param bool $sort
     * @return ImageFileResource[]
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function getImages(bool $sort = true):array {
        return $this->getImagesRegex(".*", $sort);
    }

    /**
     * @param string $pattern
     * @param bool $sort
     * @return ImageFileResource[]
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function searchImages(string $pattern = ".*",bool $sort = true):array {
        return $this->getImagesRegex($pattern, $sort);
    }

    /**
     * @param string $name
     * @param string $extension
     * @param string $content
     * @return bool
     * @throws CreateFileException
     * @throws DirectoryNotFoundException
     */
    public function createFile(string $name,string $extension,string $content = "\n"):bool
    {

        if ($name != "") {

            if(file_exists($this->actualPath."/".$name)) {
                $parts = explode(".", $name);
                $name = $parts[0];
                $extension = strtolower($parts[1]);
            }

            if ($extension != "") {
                if (!in_array($extension, FilesTypes::DISALLOWED)) {

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
    public function writeToFile(string $name,string $extension,string $content = "\n",bool $append = true)
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
     * @param string $name
     * @param string|null $extension
     * @param string $newName
     * @return bool
     * @throws FileException
     */
    public function renameFileTo(string $name,?string $extension = null,string $newName = ""):bool
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
     * @param $name
     * @param $extension
     * @param $targetName
     * @param $targetExtension
     * @throws DirectoryNotFoundException
     * @throws FileNotFoundException
     */
    public function copyPasteFile($name, $extension, $targetName, $targetExtension){

        if ($this->fileExists($name, $extension)) {
            copy($this->getRelativePath()."/".$name.".".$extension, $this->getRelativePath()."/".$targetName.".".$targetExtension);
        } else {
            throw new FileNotFoundException("File ".$name.".".$extension." not found in ".$this->getRelativePath());
        }

    }

    /**
     * @param string $path
     * @param string|null $name
     * @param string|null $extension
     * @param string|null $renameTo
     * @return bool
     * @throws DirectoryNotFoundException
     * @throws FileException
     */
    public function copyFileFromAnotherDirectory(string $path,?string $name = null,?string $extension = null,?string $renameTo = null):bool
    {
        if (file_exists($path)) {

            if (!is_dir($path)) {
                $name = pathinfo($path, PATHINFO_FILENAME);
                $extension = pathinfo($path, PATHINFO_EXTENSION);
            }

            if ($extension == null) {
                $parts = explode(".", $name);
                $name = $parts[0];
                $extension = strtolower($parts[1]);
            }

            if (copy($path . "/" . $name . "." . $extension, $this->actualPath . "/" . ($renameTo != null ? $renameTo : $name) . "." . $extension)) {
                return true;
            }

            return false;

        } else {
            throw new DirectoryNotFoundException("Path " . $path . " not found");
        }

    }

    /**
     * @param string $path
     * @param string|null $name
     * @param string|null $extension
     * @param string|null $renameTo
     * @return bool
     * @throws DirectoryNotFoundException
     */
    public function copyFileToAnotherDirectory(string $path,?string $name = null,?string $extension = null,?string $renameTo = null):bool
    {
        if (file_exists($path)) {

            if (!is_dir($path)) {
                $name = pathinfo($path, PATHINFO_FILENAME);
                $extension = pathinfo($path, PATHINFO_EXTENSION);
            }


            if ($extension == null) {
                $parts = explode(".", $name);
                $name = $parts[0];
                $extension = strtolower($parts[1]);
            }

            if (copy($this->actualPath . "/" . $name . "." . $extension,
                $path . "/" . ($renameTo != null ? $renameTo : $name) . "." . $extension)) {
                return true;
            }

            return false;

        } else {
            throw new DirectoryNotFoundException("Path " . $path . " not found");
        }

    }

    /**
     * @param string $pattern
     * @throws DeleteFileException
     */
    public function removeFile(string $pattern)
    {
        if ($pattern != "") {

            $files = glob($this->actualPath . "/" .$pattern);

            foreach ($files as $file) {
                if (!unlink($file)) {
                    throw new DeleteFileException("Remove file: " . $file . " wasn't successful");
                }
            }

        } else {
            throw new DeleteFileException("No pattern defined to delete file/s");
        }

    }

    /**
     * @param string $targetPath
     * @param string $destinationPath
     * @param int $permissions
     */
    private function copyDirectoryToRecursive(string $targetPath,string $destinationPath,int $permissions = 775){

        $dir = dir($targetPath);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            if(is_file($targetPath."/".$entry)){
                copy($targetPath."/".$entry, $destinationPath."/".$entry);
            } else {
                mkdir($destinationPath, $permissions);
                $this->copyDirectoryToRecursive($targetPath."/".$entry, $targetPath."/".$entry);
            }
        }

        // Clean up
        $dir->close();

    }

    /**
     * @param string $destPath
     * @param int $permissions
     * @throws DirectoryNotFoundException
     */
    public function copyDirectoryTo(string $destPath, int $permissions = 775){
        $this->checkPath($destPath);
        $this->copyDirectoryToRecursive($this->getAbsolutePath(), $destPath, $permissions);
    }

}