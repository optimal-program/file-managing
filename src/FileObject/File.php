<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 08.10.2017
 * Time: 14:09
 */

namespace Optimal\FileManaging\FileObject;

class File
{

    protected $fileData;
    protected $additionalInfo;
    protected $isPhpImg;

    function __construct()
    {
        $this->fileData = [];
        $this->additionalInfo = null;
        $this->isPhpImg = false;
    }

    protected function setFileInfo()
    {
        if (isset($this->fileData[ "name" ]) && isset($this->fileData[ "extension" ])) {
            $this->fileData[ "full" ] = $this->fileData[ "name" ] . "." . $this->fileData[ "extension" ];
            $this->fileData[ "fileSize" ] = file_exists($this->fileData[ "destination" ] . "/" . $this->fileData[ "full" ]) ? filesize($this->fileData[ "destination" ] . "/" . $this->fileData[ "full" ]) : 0;
        }
    }

    /**
     * @param $path
     * @return $this
     */
    public function setFilePath($path)
    {

        $this->fileData[ "name" ] = pathinfo($path, PATHINFO_FILENAME);
        $this->fileData[ "extension" ] = pathinfo($path, PATHINFO_EXTENSION);
        $this->fileData[ "destination" ] = str_replace("\\", "/", pathinfo($path, PATHINFO_DIRNAME));
        $this->fileData[ "relpath" ] = ltrim(str_replace(SystemPaths::getScriptPath(), "",
            $this->fileData[ "destination" ]), "/");
        $this->fileData[ "url" ] = SystemPaths::getBaseUrl() . "/" . $this->fileData[ "relpath" ];
        $this->setFileInfo();
        return $this;
    }

    /**
     * @param $name
     * @return $this
     * @throws \Exception
     */
    public function setRealName($name)
    {

        if ($this->fileData[ "destination" ] == "") {
            throw new \Exception("Destination must be defined first!");
        }

        $this->fileData[ "name" ] = $name;
        $this->setFileInfo();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRealName()
    {
        return $this->fileData[ "name" ];
    }

    /**
     * @param $ext
     * @return $this
     * @throws \Exception
     */
    public function setRealExtension($ext)
    {

        if ($this->fileData[ "destination" ] == "" || $this->fileData[ "name" ] == "") {
            throw new \Exception("Destination and file name must be defined first!");
        }

        $this->fileData[ "extension" ] = $ext;
        $this->setFileInfo();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRealExtension()
    {
        return $this->fileData[ "extension" ];
    }

    /**
     * @return mixed
     */
    public function getRealNameEx()
    {
        return $this->fileData[ "full" ];
    }

    /**
     * @return mixed
     */
    public function getRealPath()
    {
        return $this->fileData[ "destination" ] . "/" . $this->fileData[ "full" ];
    }

    /**
     * @return mixed
     */
    public function getRealRelativeDestination()
    {
        return $this->fileData[ "relpath" ];
    }

    /**
     * @return mixed
     */
    public function getRealRelativePath()
    {
        return $this->fileData[ "relpath" ] . "/" . $this->fileData[ "full" ];
    }

    /**
     * @return mixed
     */
    public function getRealDestination()
    {
        return $this->fileData[ "destination" ];
    }

    /**
     * @param $destination
     * @return $this
     */
    public function setRealDestination($destination)
    {
        $this->fileData[ "destination" ] = $destination;
        $this->fileData[ "relpath" ] = str_replace(SystemPaths::getScriptPath(), "", $destination);
        $this->fileData[ "url" ] = SystemPaths::getBaseUrl() . "/" . $this->fileData[ "relpath" ];
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRealFileSize()
    {
        return $this->fileData[ "fileSize" ];
    }

    /**
     * @return mixed
     */
    public function getUrlToFile()
    {
        if ($this->isPhpImg) {
            return $this->fileData[ "url" ];
        } else {
            return $this->fileData[ "url" ] . "/" . $this->fileData[ "full" ];
        }
    }

    /**
     * @param $src
     */
    public function setUrlToFile($src)
    {

        $paths = new systemPaths();

        $relPath = str_replace($paths->getProjectPath(false, true), "", $src);
        $projectAbs = $paths->getProjectPath(true);

        $this->fileData[ "name" ] = pathinfo($projectAbs . "/" . $relPath, PATHINFO_FILENAME);
        $this->fileData[ "extension" ] = pathinfo($projectAbs . "/" . $relPath, PATHINFO_EXTENSION);
        $this->fileData[ "destination" ] = pathinfo($projectAbs . "/" . $relPath, PATHINFO_DIRNAME);
        $this->fileData[ "url" ] = str_replace("/" . $this->fileData[ "name" ] . "." . $this->fileData[ "extension" ] . "",
            "", $src);
        $this->fileData[ "relpath" ] = str_replace($paths->getProjectPath(true), "", $this->fileData[ "destination" ]);

        $this->setFileInfo();
    }

    public function setUrlToPhpImage($url)
    {
        $this->fileData[ "url" ] = $url;
        $this->isPhpImg = true;
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
        if ($this->additionalInfo != null) {
            return $this->additionalInfo->getDbId();
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        if ($this->additionalInfo != null) {
            return $this->additionalInfo->getName() != "" ? $this->additionalInfo->getName() : $this->getRealName();
        }
        return $this->getRealName();
    }

    /**
     * @return mixed|string
     */
    public function getNameEx()
    {
        if ($this->additionalInfo != null) {
            return $this->additionalInfo->getName() != "" ? $this->additionalInfo->getName() . "." . $this->getRealExtension() : $this->getRealNameEx();
        }
        return $this->getRealNameEx();
    }

    /**
     * @return string
     */
    public function getFileDescription()
    {
        if ($this->additionalInfo != null) {
            return $this->additionalInfo->getDescription();
        }
        return "";
    }

    /**
     * @return string
     */
    public function getFileTitle()
    {
        if ($this->additionalInfo != null) {
            return $this->additionalInfo->getDescription();
        }
        return "";
    }

    /**
     * @param $data
     * @return mixed
     */
    public function parseAdditionalInformation($data)
    {

        $data = str_replace("{realName}", $this->getRealName(), $data);
        $data = str_replace("{realExtension}", $this->getRealExtension(), $data);
        $data = str_replace("{realNameEx}", $this->getRealNameEx(), $data);
        $data = str_replace("{realFileSize}", $this->getRealFileSize(), $data);
        $data = str_replace("{name}", $this->getName(), $data);
        $data = str_replace("{nameEx}", $this->getNameEx(), $data);
        $data = str_replace("{description}", $this->getFileDescription(), $data);

        return $data;
    }

}
