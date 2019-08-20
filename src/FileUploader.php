<?php

namespace Optimal\FileManaging;

use App\Core\Files\Exceptions\DirectoryNotFoundException;
use App\Core\Files\Exceptions\UploadFileException;
use App\Core\Files\Utils\ImagesResizeSettings;
use App\Core\Files\Utils\UploadedFilesLimits;

//todo: refactor add crop feature into file uploader

class FileUploader
{
    private static $instance = null;

    private $commander;
    private $uploadLimits;
    private $resizeSettings;
    //private $cropSettings;

    private $tempDir;
    private $thumbs = [];

    private $messages;
    private $errors = [];
    private $success = [];

    private $postFiles = [];
    private $uploadedFiles = [];

    private $imagesSettings = [];

    public static function init()
    {
        if (self::$instance == null) {
            self::$instance = new FileUploader();
        }

        return self::$instance;
    }

    public function setMessages($msgs = [])
    {
        $this->messages = $msgs;
    }

    private function __construct()
    {
        $this->uploadLimits = new UploadedFilesLimits();
        $this->commander = new FileCommander();
        $this->resizeSettings = new ImagesResizeSettings();

        $this->messages = [
            "tooBig" => "File: \"{fileFull}\" is too big",
            "notFull" => "File: \"{fileFull}\" was uploaded partially",
            "otherProblem" => "Some problem with uploaded file: \"{fileFull}\"",
            "notAllowed" => "Extension: {fileExtension} of file: \"{fileName}\" is not allowed",
            "isEmpty" => "File: \"{fileFull}\" is empty",
            "isTooLarge" => "The size of file: \"{fileFull}\" is higher then allowed",
            "wrongName" => "Name of file: \"{fileFull}\" contains suspicious characters",
            "success" => "File: \"{fileFull}\" was successfully uploaded",
            "nonSuccess" => "File: \"{fileFull}\" wasn't successfully uploaded"
        ];

        $files = $_FILES;

        foreach ($files as $inputName => $filesItems) {

            if (is_array($filesItems["name"])) {
                foreach ($filesItems as $param => $values) {
                    foreach ($values as $key => $value) {
                        $this->postFiles[$inputName][$key][$param] = $value;
                        if ($param == "name") {
                            $this->postFiles[$inputName][$key]["only_name"] = strtolower(pathinfo($this->postFiles[$inputName][$key][$param], PATHINFO_FILENAME));
                            $this->postFiles[$inputName][$key]["extension"] = strtolower(pathinfo($this->postFiles[$inputName][$key][$param], PATHINFO_EXTENSION));
                        }
                    }
                }
            } else {
                foreach ($filesItems as $param => $value) {
                    $this->postFiles[$inputName][0][$param] = $value;
                    if ($param == "name") {
                        $this->postFiles[$inputName][0]["only_name"] = strtolower(pathinfo($this->postFiles[$inputName][0][$param], PATHINFO_FILENAME));
                        $this->postFiles[$inputName][0]["extension"] = strtolower(pathinfo($this->postFiles[$inputName][0][$param], PATHINFO_EXTENSION));
                    }
                }
            }
        }

        $this->uploadedFiles = ["images" => [], "files" => []];
        //$this->cropSettings = new ImagesCropSettings();
    }

    private function parseMessage($msg, $file)
    {

        $msg = str_replace("{fileName}", $file["only_name"], $msg);
        $msg = str_replace("{fileExtension}", $file["extension"], $msg);
        $msg = str_replace("{fileFull}", $file["only_name"] . "." . $file["extension"], $msg);
        $msg = str_replace("{fileSize}", $file["size"], $msg);
        return $msg;
    }

    /**
     * @param $path
     * @throws DirectoryNotFoundException
     */
    public function setTempDirectory($path)
    {
        $cmd = new FileCommander();
        try {
            $cmd->setPath($path);
        } catch (DirectoryNotFoundException $e) {
            throw $e;
        }
        $this->tempDir = $cmd->getActualPath() . "/";
    }

    /**
     * @param $folder
     * @throws DirectoryNotFoundException
     */
    public function setUploadDestination($folder)
    {
        try {
            $this->commander->setPath($folder);
        } catch (DirectoryNotFoundException $e) {
            throw $e;
        }
    }

    /**
     * @param UploadedFilesLimits $limits
     */
    public function setUploadLimits(UploadedFilesLimits $limits)
    {
        $this->uploadLimits = $limits;
    }

    /**
     * @param $destination
     * @param ImagesResizeSettings $resize
     * @throws DirectoryNotFoundException
     */
    public function addImageThumb($destination, ImagesResizeSettings $resize/*, ImagesCropSettings $crop = null*/)
    {
        $cmd = new FileCommander();
        try {
            $cmd->setPath($destination);
        } catch (DirectoryNotFoundException $e) {
            throw $e;
        }
        array_push($this->thumbs, ["destination" => $cmd->getActualPath(), "resize" => $resize/*, "crop" => $crop*/]);
    }

    /**
     * @param ImagesResizeSettings $resize
     */
    public function setImagesResizeLimits(ImagesResizeSettings $resize)
    {
        $this->resizeSettings = $resize;
    }

    /*
    public function setImagesCropLimits(ImagesCropSettings $crop){
        $this->cropSettings = $crop;
    }
    */

    public function getPostFiles($name)
    {
        if ($name != "") {
            return $this->postFiles[$name];
        }
        return null;
    }

    public function autoRotateImage($rotate = true)
    {
        $this->imagesSettings["autorotate"] = $rotate;
    }

    public function uploadNewFile($file, $newFileName = "", $overwrite = true)
    {

        if ($this->tempDir != "") {
            if ($this->commander->getActualPath() != null) {

                $fileToUpload = [];

                if ($newFileName != '') {
                    $fileToUpload['new_name'] = $newFileName;
                }

                $fileToUpload['overwrite'] = $overwrite;

                $this->upload($file, $fileToUpload);

            } else {
                throw new UploadFileException("No destination directory set!");
            }
        } else {
            throw  new UploadFileException("No temporary directory defined!");
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isPostFile($name = "")
    {

        if (empty($this->postFiles)) {
            return false;
        }

        foreach ($this->postFiles as $key => $val) {

            if ($name != "") {
                if ($key != $name) {
                    continue;
                }
            }

            if ($val[0]["error"] == 4) {
                return false;
            }
        }

        return true;
    }

    private function upload($file, $fileToUpload)
    {

        switch ($file['error']) {
            case 0:
                break;
            case 1:
            case 2:
                array_push($this->errors, $this->parseMessage($this->messages["tooBig"], $file));
                return;
                break;
            case 3:
                array_push($this->errors, $this->parseMessage($this->messages["notFull"], $file));
                return;
                break;
            default:
                array_push($this->errors, $this->parseMessage($this->messages["otherProblem"], $file));
                return;
                break;
        }

        if ($this->checkSize($file)) {

            $fileToUpload = $this->checkName($file, $fileToUpload);

            if ($fileToUpload != null) {
                $this->moveFile($file, $fileToUpload);
            }

        }

    }

    private function checkSize($file)
    {
        if ($file['size'] == 0) {
            array_push($this->errors, $this->parseMessage($this->messages["isEmpty"], $file));
            return false;
        } elseif ($file['size'] > $this->uploadLimits->getMaxFileSize()) {
            array_push($this->errors, $this->parseMessage($this->messages["isTooLarge"], $file));
            return false;
        } else {
            return true;
        }
    }

    private function checkName($file, $fileToUpload)
    {

        if (!preg_match("/php|phtml[0-9]*?/i", $file["extension"])) {

            if (!in_array($file["extension"], UploadedFilesLimits::DISALLOWED)) {
                if (in_array($file["extension"], $this->uploadLimits->getAllowedExtensions())) {

                    if (!preg_match("/\/|\\|&|\||\?|\*/i", $file["only_name"])) {

                        if ($fileToUpload["new_name"] != '') {
                            $fileToUpload["extension"] = $file["extension"];
                        } else {
                            $newName = uniqid();
                            $fileToUpload["new_name"] = $newName;
                            $fileToUpload["extension"] = $file["extension"];
                        }

                        if ($fileToUpload['overwrite'] == false) {
                            if (file_exists($this->commander->getActualPath() . "/" . $fileToUpload["new_name"])) {

                                $newName = $fileToUpload["new_name"];
                                $i = 1;

                                while (file_exists($this->commander->getActualPath() . "/" . $newName . "_" . $i . "." . $file["extension"])) {
                                    $i++;
                                }

                                $fileToUpload["new_name"] = $newName . "_" . $i . "";

                            }
                        }

                        $fileToUpload["full"] = $fileToUpload["new_name"] . "." . $fileToUpload["extension"];
                        return $fileToUpload;

                    } else {
                        array_push($this->errors, $this->parseMessage($this->messages["wrongName"], $file));
                        array_push($this->errors, "Soubor obsahuje podezřelé znaky!!!");
                    }

                } else {
                    array_push($this->errors, $this->parseMessage($this->messages["notAllowed"], $file));
                }
            } else {
                array_push($this->errors, $this->parseMessage($this->messages["notAllowed"], $file));
            }
        } else {
            array_push($this->errors, $this->parseMessage($this->messages["notAllowed"], $file));
        }

        return null;

    }

    private function moveFile($file, $fileToUpload)
    {

        $success = move_uploaded_file($file['tmp_name'], $this->tempDir . $fileToUpload["new_name"] . "." . $fileToUpload["extension"]);

        if ($success) {
            array_push($this->success, $this->parseMessage($this->messages["success"], $file));
        } else {
            array_push($this->errors, $this->parseMessage($this->messages["nonSuccess"], $file));
            return;
        }

        $newFile = $this->commander->getActualPath() . "/" . $fileToUpload["full"];

        if ($this->commander->isImage($fileToUpload["extension"])) {

            if (count($this->uploadedFiles["images"]) > 0) {
                $index = count($this->uploadedFiles["images"]);
            } else {
                $index = 0;
            }

            $imgManager = new ImagesManager();
            $commander = new FileCommander();

            $i = 1;
            foreach ($this->thumbs as $thumb) {

                if (empty($this->uploadedFiles["images"][$index]["thumbs"])) {
                    $this->uploadedFiles["images"][$index]["thumbs"] = [];
                }

                $imgManager->setDestination($this->tempDir);
                $imgManager->setOutputDestination($thumb['destination']);

                $image = $imgManager->loadGDImage($fileToUpload["new_name"], $fileToUpload["extension"]);

                if ($this->imagesSettings["autorotate"]) {
                    $image->autoRotate();
                }

                $image->resize($thumb["resize"]->getResizeWidth(), $thumb["resize"]->getResizeHeight(), $thumb["resize"]->getResizeType());

                $newName = $fileToUpload["new_name"]."_thumb_".$i;
                $image->setName($newName);
                $image->save();

                $commander->setPath($thumb['destination']);
                $file = $commander->searchFile($image->getName(), $image->getExtension());
                array_push($this->uploadedFiles["images"][$index]["thumbs"], $file);

                $i++;
            }

            $imgManager->setDestination($this->tempDir);
            $imgManager->setOutputDestination($this->commander->getActualPath());

            $image = $imgManager->loadGDImage($fileToUpload["new_name"], $fileToUpload["extension"]);
            $image->autoRotate();
            $image->resize($this->resizeSettings->getResizeWidth(), $this->resizeSettings->getResizeHeight(), $this->resizeSettings->getResizeType());
            $image->save();

            $commander->setPath($this->commander->getActualPath());
            if (!$commander->dirExists("backup")) {
                $commander->addDir("backup", true);
            } else {
                $commander->moveToDir("backup");
            }

            $commander->copyFileFromAnotherDirectory($this->tempDir, $fileToUpload["new_name"], $fileToUpload["extension"]);

            $image->removeOriginal();
            $this->commander->refresh();
            $file = $this->commander->searchFile($image->getName(), $image->getExtension());
            $this->uploadedFiles["images"][$index]["original"] = $file;

        } else {

            $preLoadFile = $this->tempDir . $fileToUpload["full"];
            copy($preLoadFile, $newFile);
            unlink($preLoadFile);

            $this->commander->refresh();
            $file = $this->commander->searchFile($fileToUpload["new_name"], $fileToUpload["extension"]);

            array_push($this->uploadedFiles["files"], $file);

        }

    }

    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    public function printStats()
    {

        $message = "";

        foreach ($this->errors as $value) {
            $message .= $value . "<br />";
        }

        foreach ($this->success as $value) {
            $message .= $value . "<br />";
        }

        return $message;

    }

    public function countErrors()
    {
        return count($this->errors);
    }

    public function getResult()
    {
        $result = array(0 => $this->success, 1 => $this->errors);
        return $result;
    }


    public function clear()
    {

        $this->uploadLimits = new UploadedFilesLimits();
        $this->resizeSettings = new ImagesResizeSettings();
        //$this->cropSettings = new ImagesCropSettings();

        $this->thumbs = [];
        $this->errors = [];
        $this->success = [];

        $this->uploadedFiles = [];

    }

}

?>

