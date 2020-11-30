<?php declare(strict_types=1);

namespace Optimal\FileManaging;

use Optimal\FileManaging\Exception\DirectoryException;
use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\UploadFileException;
use Optimal\FileManaging\resources\ImageFileResourceThumb;
use Optimal\FileManaging\resources\UploadedFilesResource;
use Optimal\FileManaging\Utils\FilesTypes;
use Optimal\FileManaging\Utils\ImageCropSettings;
use Optimal\FileManaging\Utils\ImageResolutionSettings;
use Optimal\FileManaging\Utils\ImageResolutionsSettings;
use Optimal\FileManaging\Utils\UploadedFilesLimits;

class FileUploader {

    private static $instance = null;

    /** @var array  */
    private $_FILES;

    /** @var array */
    private $messages;

    /** @var FileCommander|null */
    private $targetDirCommander;
    /** @var FileCommander|null */
    private $tmpDirCommander;

    /** @var ImagesManager */
    private $imagesManager;
    /** @var string */
    private $imagesResourceType;

    /** @var int */
    private $maxImageWidth;

    /** @var int */
    private $maxImageHeight;

    /** @var ImageCropSettings|null  */
    private $imageCropSettings = null;

    /** @var ImageCropSettings|null  */
    private $imageThumbCropSettings = null;

    /** @var UploadedFilesLimits */
    private $uploadLimits;
    /** @var bool  */
    private $autoRotateImages = true;
    /** @var bool  */
    private $backup = false;

    /** @var array  */
    private $successMessages;
    /** @var array  */
    private $errorMessages;

    /** @var array  */
    private $uploadedFiles;

    /**
     * @return FileUploader|null
     */
    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new FileUploader();
        }
        return self::$instance;
    }

    /**
     * FileUploader constructor.
     */
    private function __construct(){

        $this->uploadLimits = new UploadedFilesLimits();

        $this->targetDirCommander = null;
        $this->tmpDirCommander = null;

        $this->imagesManager = new ImagesManager();

        $this->_FILES = [];

        foreach ($_FILES as $inputName => $filesItems) {
            if (is_array($filesItems["name"])) {
                foreach ($filesItems as $param => $values) {
                    foreach ($values as $key => $value) {
                        $this->_FILES[$inputName][$key][$param] = $value;
                        if ($param == "name") {
                            $this->_FILES[$inputName][$key]["only_name"] = strtolower(pathinfo($this->_FILES[$inputName][$key][$param], PATHINFO_FILENAME));
                            $this->_FILES[$inputName][$key]["only_extension"] = strtolower(pathinfo($this->_FILES[$inputName][$key][$param], PATHINFO_EXTENSION));
                        }
                    }
                }
            } else {
                foreach ($filesItems as $param => $value) {
                    $this->_FILES[$inputName][0][$param] = $value;
                    if ($param == "name") {
                        $this->_FILES[$inputName][0]["only_name"] = strtolower(pathinfo($this->_FILES[$inputName][0][$param], PATHINFO_FILENAME));
                        $this->_FILES[$inputName][0]["only_extension"] = strtolower(pathinfo($this->_FILES[$inputName][0][$param], PATHINFO_EXTENSION));
                    }
                }
            }
        }

        $this->maxImageWidth = 3840;
        $this->maxImageHeight = 2160;

        $this->uploadedFiles = ["images"=>[],"files"=>[]];
        $this->successMessages = [];
        $this->errorMessages = [];

        $this->messages = [
            "tooBig" => "Soubor: '{fileFull}' je příliš velký",
            "notFull" => "Soubor: '{fileFull}' byl nahrán pouze částečně",
            "otherProblem" => "S nahrávaným souborem: '{fileFull}' nastal nějaký problém",
            "notAllowed" => "Přípona: {fileExtension} souboru: '{fileName}' není povolena",
            "isEmpty" => "Soubor: '{fileFull}' je prázdný",
            "isTooLarge" => "Velikost souborů: '{fileFull}' je větší, než je povoleno",
            "wrongName" => "Název souboru: '{fileFull}' obsahuje podezřelé znaky",
            "success" => "Soubor: '{fileFull}' byl úspěšně nahrán",
            "nonSuccess" => "Soubor: '{fileFull}' nebyl úspěšně nahrán"
        ];

        $this->imagesResourceType = ImagesManager::RESOURCE_TYPE_GD;
    }

    /**
     * @param bool $enable
     */
    public function enableBackup($enable = false){
        $this->backup = $enable;
    }

    /**
     * @param array $messages
     */
    public function setMessages(array $messages) {
        $this->messages = $messages;
    }

    /**
     * @param $msg
     * @param $file
     * @return mixed
     */
    private function parseMessage($msg, $file)
    {
        $msg = str_replace("{fileName}", $file["only_name"], $msg);
        $msg = str_replace("{fileExtension}", $file["only_extension"], $msg);
        $msg = str_replace("{fileFull}", $file["only_name"] . "." . $file["only_extension"], $msg);
        $msg = str_replace("{fileSize}", $file["size"], $msg);
        return $msg;
    }

    /**
     * @param UploadedFilesLimits $limits
     */
    public function setUploadLimits(UploadedFilesLimits $limits){
        $this->uploadLimits = $limits;
    }

    /**
     * @param string $directory
     * @throws Exception\DirectoryNotFoundException
     */
    public function setTemporaryDirectory(string $directory){
        $this->tmpDirCommander = new FileCommander();
        $this->tmpDirCommander->setPath($directory);
    }

    /**
     * @param string $directory
     * @throws Exception\DirectoryNotFoundException
     */
    public function setTargetDirectory(string $directory){
        $this->targetDirCommander = new FileCommander();
        $this->targetDirCommander->setPath($directory);
    }

    /* todo - enable crop images
    public function setimagecropsettings(imagecropsettings $settings){
        $this->imagecropsettings = $settings;
    }
    */

    /** todo - enable crop images
    public function setimagethumbcropsettings(imagecropsettings $settings){
        $this->imagethumbcropsettings = $settings;
    }
    */

    /**
     * @param bool $rotate
     */
    public function autoRotateImages(bool $rotate = true)
    {
        $this->autoRotateImages = $rotate;
    }

    /**
     * @param string $resource
     */
    public function setImageManageResourceType(string $resource = ImagesManager::RESOURCE_TYPE_GD){
        $this->imagesResourceType = $resource;
    }

    /**
     * @return int
     */
    public function getMaxImageWidth(): int
    {
        return $this->maxImageWidth;
    }

    /**
     * @param int $maxImageWidth
     */
    public function setMaxImageWidth(int $maxImageWidth = 3840): void
    {
        $this->maxImageWidth = $maxImageWidth;
    }

    /**
     * @return int
     */
    public function getMaxImageHeight(): int
    {
        return $this->maxImageHeight;
    }

    /**
     * @param int $maxImageHeight
     */
    public function setMaxImageHeight(int $maxImageHeight = 2160): void
    {
        $this->maxImageHeight = $maxImageHeight;
    }

    /**
     * @param string $inputName
     * @return bool
     */
    public function isPostFile(string $inputName)
    {

        if (empty($this->_FILES)) {
            return false;
        }

        foreach ($this->_FILES as $key => $val) {

            if ($key != $inputName) {
                continue;
            }

            if ($val[0]["error"] == 4) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $inputName
     * @return int
     */
    public function countInputFiles(string $inputName){
        if(!isset($this->_FILES[$inputName])){
            return 0;
        }
        return count($this->_FILES[$inputName]);
    }

    /**
     * @param string $inputName
     * @param int $index
     * @return array
     */
    public function getFileToUploadData(string $inputName, int $index):array
    {
        return $this->_FILES[$inputName][$index];
    }

    /**
     * @param string $inputName
     * @param int $index
     * @param string|null $newFileName
     * @param bool $overwrite
     * @param callable|null $beforeUploadCallback
     * @param callable|null $afterUploadCallback
     * @throws DirectoryException
     * @throws DirectoryNotFoundException
     * @throws Exception\CreateDirectoryException
     * @throws Exception\DeleteFileException
     * @throws Exception\FileException
     * @throws Exception\FileNotFoundException
     * @throws Exception\GDException
     * @throws \ImagickException
     */
    public function uploadFile(string $inputName, int $index, ?string $newFileName = null, bool $overwrite = true, callable $beforeUploadCallback = null, callable $afterUploadCallback = null)
    {
        if(!$this->targetDirCommander || !$this->tmpDirCommander){
            throw new DirectoryException("Temporary or target directory is not defined");
        }

        if($this->checkFile($this->_FILES[$inputName][$index])) {

            $file = $this->_FILES[$inputName][$index];
            $newName = uniqid();

            if ($newFileName != null) {
                $newName = $newFileName;
            }

            if(!$overwrite){
                $i = 1;
                if($this->targetDirCommander->fileExists($newName, $file["only_extension"])){
                    while($this->targetDirCommander->fileExists($newName."_".$i, $file["only_extension"])){
                        $i++;
                    }
                }
                $newName = $newName."_".$i;
            }

            $this->moveFile($this->_FILES[$inputName][$index], $newName, $beforeUploadCallback, $afterUploadCallback);
        }
    }

    /**
     * @param string $inputName
     * @param array $newFileNames
     * @param bool $overwrite
     * @param callable|null $beforeFileUploadCallback
     * @param callable|null $afterFileUploadCallback
     * @throws DirectoryException
     * @throws DirectoryNotFoundException
     * @throws Exception\CreateDirectoryException
     * @throws Exception\DeleteFileException
     * @throws Exception\FileException
     * @throws Exception\FileNotFoundException
     * @throws Exception\GDException
     * @throws UploadFileException
     * @throws \ImagickException
     */
    public function uploadFiles(string $inputName, array $newFileNames = [], bool $overwrite = true, callable $beforeFileUploadCallback = null, callable $afterFileUploadCallback = null){

        if(!empty($newFileNames)) {
            if (count($this->_FILES[$inputName]) != count($newFileNames)) {
                throw new \Exception("count of newFileNames is not same as count of files");
            }
        }

        foreach ($this->_FILES[$inputName] as $key => $data){
            $this->uploadFile($inputName, $key, !empty($newFileNames) ? $newFileNames[$key] : uniqid(), $overwrite, $beforeFileUploadCallback, $afterFileUploadCallback);
        }

    }

    /**
     * @param array $file
     * @return bool
     */
    private function checkFile(array $file):bool
    {

        switch ($file['error']) {
            case 0:
                break;
            case 1:
            case 2:
                array_push($this->errorMessages, $this->parseMessage($this->messages["tooBig"], $file));
                return false;
                break;
            case 3:
                array_push($this->errorMessages, $this->parseMessage($this->messages["notFull"], $file));
                return false;
                break;
            default:
                array_push($this->errorMessages, $this->parseMessage($this->messages["otherProblem"], $file));
                return false;
                break;
        }

        if ($file['size'] == 0) {
            array_push($this->errorMessages, $this->parseMessage($this->messages["isEmpty"], $file));
            return false;
        } elseif ($file['size'] > $this->uploadLimits->getMaxFileSize()) {
            array_push($this->errorMessages, $this->parseMessage($this->messages["isTooLarge"], $file));
            return false;
        }

        if (preg_match("/php|phtml[0-9]*?/i", $file["only_extension"]) || in_array($file["only_extension"], FilesTypes::DISALLOWED) || !in_array($file["only_extension"], $this->uploadLimits->getAllowedExtensions())) {
            array_push($this->errorMessages, $this->parseMessage($this->messages["notAllowed"], $file));
            return false;
        }

        if (preg_match("/\/|\\|&|\||\?|\*/i", $file["only_name"])) {
            array_push($this->errorMessages, $this->parseMessage($this->messages["wrongName"], $file));
            return false;
        }

        return true;
    }

    /**
     * @param array $file
     * @param string $newName
     * @param callable|null $beforeUploadCallback
     * @param callable|null $afterUploadCallback
     * @return bool
     * @throws DirectoryNotFoundException
     * @throws Exception\CreateDirectoryException
     * @throws Exception\DeleteFileException
     * @throws Exception\FileException
     * @throws Exception\FileNotFoundException
     * @throws Exception\GDException
     * @throws \ImagickException
     */
    private function moveFile(array $file, string $newName, callable $beforeUploadCallback = null, callable $afterUploadCallback = null):bool
    {

        if($beforeUploadCallback){
            $result = $beforeUploadCallback($file, $newName);
            if(is_array($result)){
                if(isset($result['newName'])){
                    $newName = $result['newName'];
                }
            }
        }

        $success = @move_uploaded_file($file['tmp_name'], $this->tmpDirCommander->getRelativePath() . "/" . $newName . "." . $file["only_extension"]);

        if ($success) {
            array_push($this->successMessages, $this->parseMessage($this->messages["success"], $file));
        } else {
            array_push($this->errorMessages, $this->parseMessage($this->messages["nonSuccess"], $file));
            return false;
        }

        if ($this->targetDirCommander->isImage($file["only_extension"])) {

            $this->imagesManager->setSourceDirectory($this->tmpDirCommander->getRelativePath());
            $this->imagesManager->setOutputDirectory($this->targetDirCommander->getRelativePath());

            $imageManageResource = $this->imagesManager->loadImageManageResource($newName, $file["only_extension"], $this->imagesResourceType);

            if ($this->autoRotateImages) {
                $imageManageResource->autoRotate();
            }

            $imageManageResource->resize($this->maxImageWidth, $this->maxImageHeight);

            if($this->imageCropSettings != null){
                // TODO image crop
            }

            $imageManageResource->save();
            $originalImageResource = $imageManageResource->getOutputImageResource();
            $originalImageResourceExt = $originalImageResource->getNewExtension() != null ? $originalImageResource->getNewExtension() : $originalImageResource->getExtension();

            $thumbImageResource = null;
            if($this->imageThumbCropSettings != null) {

                $this->imagesManager->setSourceDirectory($this->targetDirCommander->getRelativePath());
                $this->imagesManager->setOutputDirectory($this->targetDirCommander->getRelativePath());

                $imageManageResourceV = $this->imagesManager->loadImageManageResource($originalImageResource->getName(), $originalImageResourceExt, $this->imagesResourceType);

                // TODO image thumb crop

                $imageManageResourceV->getSourceImageResource()->setNewName($newName."-thumb");
                $imageManageResourceV->save();

                $thumbImageResource = $imageManageResourceV->getOutputImageResource();
            }

            $currDir = $this->targetDirCommander->getRelativePath();
            if($this->backup) {
                $this->targetDirCommander->addDirectory("backup", true);
                $this->targetDirCommander->copyFileFromAnotherDirectory($currDir, $newName, $file["only_extension"]);
            }

            $this->tmpDirCommander->removeFile($newName.".".$file["only_extension"]);

            $this->targetDirCommander->moveUp();

            array_push($this->uploadedFiles["images"], ['original' => $originalImageResource, 'thumb' => $thumbImageResource]);

            if(isset($afterUploadCallback)){
                $afterUploadCallback($originalImageResource, $thumbImageResource);
            }

        } else {
            $this->tmpDirCommander->copyFileToAnotherDirectory($newName, $file["only_extension"], $this->targetDirCommander->getRelativePath());
            $this->tmpDirCommander->removeFile($newName.".".$file["only_extension"]);

            $fileResource = $this->targetDirCommander->getFile($newName, $file["only_extension"]);

            array_push($this->uploadedFiles["files"], $fileResource);

            if(isset($afterUploadCallback)){
                $afterUploadCallback($fileResource);
            }
        }

        return true;
    }

    /**
     * @return UploadedFilesResource
     */
    public function getUploadedFiles()
    {
        return new UploadedFilesResource($this->uploadedFiles["files"], $this->uploadedFiles["images"]);
    }

    public function getSuccessMessages()
    {
        return $this->successMessages;
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function clear()
    {
        $this->uploadedFiles = ["images"=>[],"files"=>[]];
        $this->successMessages = [];
        $this->errorMessages = [];
    }

}