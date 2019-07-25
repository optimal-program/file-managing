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
use Optimal\FileManaging\Utils\SystemPaths;
use Optimal\FileManaging\Utils\UploadedFilesLimits;
use Optimal\FileManaging\FileObject\File;
use Optimal\FileManaging\FileObject\Image;

class FileCommander
{
    private $path;
    private $actualPath = "";

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

        $this->path = $path;
        $this->actualPath = $path;
    }

    /**
     * @return string|null
     */
    public function getAbsolutePath()
    {
        if ($this->actualPath != "") {
            return $this->actualPath;
        } else {
            return null;
        }
    }

    public function getRelativePath()
    {
        if ($this->actualPath != "") {
            print_r($this->actualPath);
            print_r(SystemPaths::getScriptPath());

            return ltrim(str_replace(SystemPaths::getScriptPath(), "", $this->actualPath), "/");
        } else {
            return null;
        }
    }

    private function loadFiles()
    {
        $this->dirs = [];
        $this->files = [];

        $dirContent = scandir($this->actualPath);

        foreach ($dirContent as $key => $value) {

            if ($value != "." && $value != "..") {

                if (is_dir($this->actualPath . "/" . $value)) {
                    array_push($this->dirs, $value);
                } else {
                    array_push($this->files, $value);
                }
            }
        }

        $this->sortFile();
    }

    private function sortFile($files = true, $dirs = true)
    {
        if ($files) {
            usort($this->files, "strnatcasecmp");
        }
        if ($dirs) {
            usort($this->dirs, "strnatcasecmp");
        }
    }

    /**
     * @param $name
     * @throws DirectoryNotFoundException
     */
    public function moveToDir($name)
    {
        $name = rtrim($name, "/");

        if ($this->dirExists($name)) {
            $this->actualPath .= "/" . $name;
            $this->loadFiles();
        } else {
            throw new DirectoryNotFoundException("Directory: " . $name . " is not exists");
        }
    }

    public function moveUp()
    {
        if ($this->path != $this->actualPath) {
            $parts = explode("/", $this->actualPath);
            unset($parts[ count($parts) - 1 ]);
            $this->actualPath = implode("/", $parts);
            $this->loadFiles();
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function dirExists($name)
    {
        foreach ($this->dirs as $value) {
            if ($value == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $name
     * @param $extension
     * @return bool
     */
    public function fileExists($name, $extension)
    {
        foreach ($this->files as $value) {

            $fileName = pathinfo($this->getActualPath() . "/" . $value, PATHINFO_FILENAME);
            $fileExtension = pathinfo($this->getActualPath() . "/" . $value, PATHINFO_EXTENSION);

            if ($fileName == $name && $fileExtension == $extension) {
                return true;
            }
        }

        return false;
    }


    /**
     * @return array
     */
    public function getDirs()
    {
        return $this->dirs;
    }

    /**
     * @return int
     */
    public function countDirs()
    {
        return count($this->dirs);
    }

    /**
     * @return int
     */
    public function countFiles()
    {
        return count($this->files);
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
     * @param $path
     * @param $name
     * @param $extension
     * @return File|Image
     */
    protected function getFile($path, $name, $extension)
    {
        if ($this->isImage($extension)) {
            $file = new Image();
            $file->setFilePath($path . "/" . $name . "." . $extension);
        } else {
            $file = new File();
            $file->setFilePath($path . "/" . $name . "." . $extension);
        }

        return $file;
    }

    /**
     * @return null|File|Image
     */
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

    /**
     * @param string $pattern
     * @param array $extensions
     * @return array
     */
    public function getFiles($pattern = ".*", $extensions = [])
    {
        foreach ($extensions as $key => $extension) {
            $extensions[ $key ] = strtolower($extension);
        }

        $searched = [];

        if (empty($this->files)) {
            $this->loadFiles();
        }

        foreach ($this->files as $file) {

            // TODO může být jenom file exist?
            $name = pathinfo($this->getActualPath() . "/" . $file, PATHINFO_FILENAME);
            $extension = strtolower(pathinfo($this->getActualPath() . "/" . $file, PATHINFO_EXTENSION));

            if (!empty($extensions)) {
                if (!in_array($extension, $extensions)) {
                    continue;
                }
            }

            if ($pattern != '') {
                if (!preg_match("~" . $pattern . "~", $file)) {
                    continue;
                }
            }

            array_push($searched, $this->getFile($this->getActualPath(), $name, $extension));

        }
        return $searched;
    }

    /**
     * @param $name
     * @param string $extension
     * @return File|Image|null
     */
    public function searchFile($name, $extension = "")
    {
        $found = $this->getFiles($name, [$extension]);

        if (!empty($found)) {
            return $found[ 0 ];
        }

        return null;

    }

    /**
     * @param $dirName
     * @param bool $moveToDir
     * @param int $chmod
     * @return bool
     * @throws CreateDirectoryException
     */
    public function addDir($dirName, $moveToDir = false, $chmod = 0777)
    {

        $dirName = rtrim($dirName, "/");

        if (!$this->dirExists($dirName)) {

            umask(0000);
            if (!mkdir($this->actualPath . "/" . $dirName, $chmod)) {
                throw new CreateDirectoryException("Create directory " . $dirName . " into " . $this->actualPath . " wasn't successful, maybe access rights problem.");
            }

            array_push($this->dirs, $dirName);
            $this->sort(false, true);

            if ($moveToDir) {
                $this->moveToDir($dirName);
            }

        }

        return true;
    }

    /**
     * @param $name
     * @param $extension
     * @param string $data
     * @param bool $getFile
     * @return bool|File
     * @throws CreateFileException
     */
    public function createFile($name, $extension, $data = "\n", $getFile = false)
    {

        if ($name != "") {
            if ($extension != "") {
                if (!in_array($extension, UploadedFilesLimits::DISALLOWED)) {

                    if (!$this->fileExists($name, $extension)) {

                        $f = fopen($this->actualPath . "/" . $name, "w+");
                        $f = fwrite($f, $data);
                        fclose($f);

                        array_push($this->files, $name);

                        if ($getFile) {
                            $file = new File();
                            $file->setFilePath($this->getActualPath() . "/" . $name . "." . $extension);
                            return $file;
                        }

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
     * @param File $file
     * @param $data
     * @param bool $append
     * @throws FileNotFoundException
     */
    public function writeToFile(File $file, $data, $append = true)
    {

        if ($this->fileExists($file->getRealName(), $file->getRealExtension())) {

            if ($append) {
                $currentData = file_get_contents($this->actualPath . "/" . $file->getRealNameEx());
                file_put_contents($this->actualPath . "/" . $file->getRealNameEx(), $currentData . $data);
            } else {
                file_put_contents($this->actualPath . "/" . $file->getRealNameEx(), $data);
            }

        } else {
            throw new FileNotFoundException("File in current working directory not found!");
        }
    }

    /**
     * @param $fileLocation
     * @param $name
     * @param $extension
     * @param null $renameTo
     * @param bool $getFileCopy
     * @return bool|File
     * @throws DirectoryNotFoundException
     * @throws FileNotFoundException
     */
    public function copyFileFromAnotherDirectory(
        $fileLocation,
        $name,
        $extension,
        $renameTo = null,
        $getFileCopy = false
    ) {

        $cmd = new FileCommander();

        try {
            $cmd->setPath($fileLocation);
        } catch (DirectoryNotFoundException $e) {
            throw new  DirectoryNotFoundException($e);
        }

        if ($name != "") {
            if ($extension != "") {

                $fileToCopy = $cmd->searchFile($name, $extension);

                if ($renameTo != null) {
                    $fileName = $renameTo;
                } else {
                    $fileName = $fileToCopy->getRealName();
                }

                if (copy($fileToCopy->getRealPath(),
                    $this->actualPath . "/" . $fileName . "." . $fileToCopy->getRealExtension())) {
                    if ($getFileCopy) {
                        $fileCopy = new File();
                        $fileCopy->setFilePath($this->actualPath . "/" . $fileName . "." . $fileToCopy->getRealExtension());
                        return $fileCopy;
                    }
                }

            } else {
                throw new FileNotFoundException("Extension of file: " . $name . " is not defined");
            }
        } else {
            throw new FileNotFoundException("File name is not defined");
        }

        return null;

    }

    /**
     * @param File $file
     * @param $newName
     * @throws FileException
     * @throws FileNotFoundException
     */
    public function renameFileTo(File &$file, $newName)
    {

        if (file_exists($file->getRealPath())) {
            if ($newName != "") {
                if ($this->fileExists($file->getRealName(), $file->getRealExtension())) {
                    if (rename($file->getRealPath(),
                        $file->getRealDestination() . "/" . $newName . "." . $file->getRealExtension())) {
                        $file->setRealName($newName);
                    }
                }
            } else {
                throw new FileException("No name defined for renaming the file");
            }
        } else {
            throw new FileNotFoundException("Defined file is not exists!");
        }

    }

    /**
     * @param $pattern
     * @param array $extensions
     * @throws DeleteFileException
     */
    public function removeFile($pattern, $extensions = [])
    {
        if ($pattern != "") {

            $files = $this->getFiles("^" . $pattern . "\..*$", $extensions);

            foreach ($files as $file) {
                if (!unlink($this->actualPath . "/" . $file->getRealNameEx())) {
                    throw new DeleteFileException("Remove file: " . $file . " wasn't successful");
                }
            }

        } else {
            throw new DeleteFileException("No pattern defined to delete file[ s ] . ");
        }

    }

    /**
     * @param $name
     * @throws DeleteDirectoryException
     */
    public function removeDir($name)
    {

        if ($this->dirExists($name)) {
            try {
                $this->DeleteDir($this->actualPath . " / " . $name);
            } catch (DeleteDirectoryException $e) {
                throw $e;
            }
            if (!rmdir($this->actualPath . " / " . $name)) {
                throw new DeleteDirectoryException("Remove directory: " . $name . " wasn't successful");
            }
        }

    }

    /**
     * @param $name
     * @throws DeleteDirectoryException
     * @throws DeleteFileException
     */
    public function clearDir($name)
    {

        if ($this->dirExists($name)) {
            try {
                $this->DeleteDir($this->actualPath . "/" . $name);
            } catch (DeleteDirectoryException $e) {
                throw $e;
            } catch (DeleteFileException $e) {
                throw $e;
            }
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

    public function renameDirRecursive($name, $newName)
    {
        $this->renameDirRec($this->getActualPath(), $name, $newName);
    }

    private function renameDirRec($path, $name, $newName)
    {

        $files = array_diff(scandir($path), array(' . ', ' ..'));

        foreach ($files as $file) {
            if (is_dir($path . ' / ' . $file)) {
                $this->renameDirRec($path . ' / ' . $file, $name, $newName);
                if ($file == $name) {
                    if (!rename($path . ' / ' . $file, $path . "/" . $newName)) {
                        throw new DirectoryException("Remove directory: " . $file . " wasn't successful");
                    }
                }
            }
        }

    }

}

?>