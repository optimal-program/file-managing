<?php declare(strict_types=1);

namespace Optimal\FileManaging;

use Optimal\FileManaging\Exception\DirectoryException;
use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\UploadFileException;
use Optimal\FileManaging\Utils\FilesTypes;
use Optimal\FileManaging\Utils\ImageCropSettings;
use Optimal\FileManaging\Utils\ImageResolutionSettings;
use Optimal\FileManaging\Utils\ImageResolutionsSettings;
use Optimal\FileManaging\Utils\UploadedFilesLimits;

class FileUploader {

    private static $instance = null;

    /** @var FileCommander|null */
    private $targetDirCommander;
    /** @var FileCommander|null */
    private $tmpDirCommander;
    /** @var FileCommander|null */
    private $cacheDirCommander;

    /** @var ImagesManager */
    private $imagesManager;
    /** @var string */
    private $imagesResourceType;

    /** @var array */
    private $messages;

    /** @var ImageCropSettings|null  */
    private $imageCropSettings = null;
    /** @var ImageResolutionsSettings|null */
    private $imageResolutionsSettings = null;

    /** @var ImageCropSettings|null  */
    private $imageThumbCropSettings = null;
    /** @var ImageResolutionsSettings|null */
    private $imageThumbResolutionsSettings = null;

    /** @var UploadedFilesLimits */
    private $uploadLimits;
    /** @var bool  */
    private $autoRotateImages = true;
    /** @var bool  */
    private $backup = false;

    /** @var array  */
    private $_FILES;

    /** @var array  */
    private $successMessages;
    /** @var array  */
    private $errorMessages;

    /** @var array  */
    private $uploadedFiles;

    /**
     * @return FileUploader|null
     */
    public static function init(){
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
        $this->cacheDirCommander = null;

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
    public function enableBackup($enable = true){
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

    /**
     * @param string $directory
     * @throws Exception\DirectoryNotFoundException
     */
    public function setImagesVariantsCacheDirectory(string $directory){
        $this->cacheDirCommander = new FileCommander();
        $this->cacheDirCommander->setPath($directory);
    }

    /**
     * @param ImageResolutionsSettings $settings
     */
    public function setImageResolutionsSettings(ImageResolutionsSettings $settings){
        $this->imageResolutionsSettings = $settings;
    }

    /* TODO - enable crop images
    public function setImageCropSettings(ImageCropSettings $settings){
        $this->imageCropSettings = $settings;
    }
    */

    /**
     * @param ImageResolutionsSettings $resizeSettings
     */
    public function setThumbResolutionsSettings(ImageResolutionsSettings $resizeSettings){
        $this->imageThumbResolutionsSettings = $resizeSettings;
    }

    /** TODO - enable crop images
    public function setImageThumbCropSettings(ImageCropSettings $settings){
        $this->imageThumbCropSettings = $settings;
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
     * @param string|null $newFileName
     * @param bool $overwrite
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
    public function uploadFile(string $inputName,int $index,?string $newFileName = null,bool $overwrite = true)
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

            $this->moveFile($this->_FILES[$inputName][$index], $newName);
        }
    }

    /**
     * @param string $inputName
     * @param array $newFileNames
     * @param bool $overwrite
     * @throws DirectoryException
     * @throws DirectoryNotFoundException
     * @throws Exception\CreateDirectoryException
     * @throws Exception\DeleteFileException
     * @throws Exception\FileException
     * @throws Exception\FileNotFoundException
     * @throws Exception\GDException | \ImagickException
     * @throws UploadFileException
     */
    public function uploadFiles(string $inputName, array $newFileNames = [], bool $overwrite = true){

        if(!empty($newFileNames)) {
            if (count($this->_FILES[$inputName]) != count($newFileNames)) {
                throw new \Exception("count of newFileNames is not same as count of files");
            }
        }

        foreach ($this->_FILES[$inputName] as $key => $data){
            $this->uploadFile($inputName, $key, !empty($newFileNames) ? $newFileNames[$key] : uniqid(), $overwrite);
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

        if (preg_match("/php|phtml[0-9]*?/i", $file["only_extension"]) || in_array($file["only_extension"],
                FilesTypes::DISALLOWED) || !in_array($file["only_extension"],
                $this->uploadLimits->getAllowedExtensions())) {
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
     * @return bool
     * @throws DirectoryException
     * @throws DirectoryNotFoundException
     * @throws Exception\CreateDirectoryException
     * @throws Exception\DeleteFileException
     * @throws Exception\FileException
     * @throws Exception\FileNotFoundException
     * @throws Exception\GDException
     * @throws \ImagickException
     */
    private function moveFile(array $file, string $newName):bool
    {
        $this->tmpDirCommander->addDirectory('upload', true);

        $success = @move_uploaded_file($file['tmp_name'], $this->tmpDirCommander->getRelativePath() . "/" . $newName . "." . $file["only_extension"]);

        if ($success) {
            array_push($this->successMessages, $this->parseMessage($this->messages["success"], $file));
        } else {
            array_push($this->errorMessages, $this->parseMessage($this->messages["nonSuccess"], $file));
            return false;
        }

        $tmpFilePath = $this->tmpDirCommander->getRelativePath();

        if ($this->targetDirCommander->isImage($file["only_extension"])) {

            $this->tmpDirCommander->moveUp();
            $this->tmpDirCommander->addDirectory('cache', true);
            $this->tmpDirCommander->addDirectory('optimal-program', true);

            $this->imagesManager->setSourceDirectory($tmpFilePath);
            $this->imagesManager->setOutputDirectory($this->targetDirCommander->getRelativePath());

            $imageManageResource = $this->imagesManager->loadImageManageResource($newName, $file["only_extension"], $this->imagesResourceType);

            if ($this->autoRotateImages) {
                $imageManageResource->autoRotate();
            }

            if($this->imageCropSettings != null){
                // TODO image crop
            }

            $imageManageResource->save();
            $originalImageResource = $imageManageResource->getOutputImageResource();

            if($this->cacheDirCommander) {
                $cacheDirPath = $this->cacheDirCommander->getRelativePath();
                $pathParts = explode("/", $this->targetDirCommander->getRelativePath());
                foreach ($pathParts as $pathPart) {
                    $this->cacheDirCommander->addDirectory($pathPart, true);
                }
            }

            if($this->imageResolutionsSettings != null) {

                if(!$this->cacheDirCommander){
                    throw new DirectoryException("Images variants cache directory is not defined");
                }

                $this->cacheDirCommander->addDirectory('image_variants', true);

                /** @var ImageResolutionSettings $resolutionSettings */
                foreach ($this->imageResolutionsSettings->getResolutionsSettings() as $resolutionSettings) {

                    $this->tmpDirCommander->addDirectory($newName, true);

                    $this->imagesManager->setSourceDirectory($this->targetDirCommander->getRelativePath());
                    $this->imagesManager->setOutputDirectory($this->tmpDirCommander->getRelativePath());

                    $imageManageResourceV = $this->imagesManager->loadImageManageResource($newName, $file["only_extension"], $this->imagesResourceType);
                    $imageManageResourceV->resize($resolutionSettings->getWidth(), $resolutionSettings->getHeight(), $resolutionSettings->getResizeType());

                    $variantName = $newName . (($resolutionSettings->getWidth() > 0) ? '-w' . $resolutionSettings->getWidth() : '') . (($resolutionSettings->getHeight() > 0) ? '-h' . $resolutionSettings->getHeight() : '');
                    $imageManageResourceV->getSourceImageResource()->setNewName($variantName);
                    $imageManageResourceV->save(null, $resolutionSettings->getExtension() == "default" ? null : $resolutionSettings->getExtension());
                }

                $this->cacheDirCommander->setPath($cacheDirPath);

            }

            $thumbImageResource = null;

            if($this->imageThumbResolutionsSettings != null){

                if(!$this->cacheDirCommander){
                    throw new DirectoryException("Images variants cache directory is not defined");
                }

                if($this->imageThumbCropSettings != null) {

                    $this->imagesManager->setSourceDirectory($tmpFilePath);
                    $this->imagesManager->setOutputDirectory($this->targetDirCommander->getRelativePath());

                    $imageManageResourceV = $this->imagesManager->loadImageManageResource($newName, $file["only_extension"], $this->imagesResourceType);
                    // TODO image thumb crop
                    $imageManageResourceV->getSourceImageResource()->setNewName($newName."-thumb");
                    $imageManageResourceV->save();

                    $thumbImageResource = $imageManageResourceV->getOutputImageResource();
                } else {
                    $thumbImageResource = clone($originalImageResource);
                }

                $this->cacheDirCommander->addDirectory('thumb_variants', true);

                foreach ($this->imageThumbResolutionsSettings->getResolutionsSettings() as $resolutionSettings){
                    $this->cacheDirCommander->addDirectory($thumbImageResource->getName(), true);

                    $this->imagesManager->setSourceDirectory($this->targetDirCommander->getRelativePath());
                    $this->imagesManager->setOutputDirectory($this->tmpDirCommander->getRelativePath());

                    $imageManageResourceV = $this->imagesManager->loadImageManageResource($thumbImageResource->getName(), $thumbImageResource->getExtension(), $this->imagesResourceType);
                    $imageManageResourceV->resize($resolutionSettings->getWidth(), $resolutionSettings->getHeight(), $resolutionSettings->getResizeType());

                    $variantName = $newName .'-thumb' . (($resolutionSettings->getWidth() > 0) ? '-w' . $resolutionSettings->getWidth() : '') . (($resolutionSettings->getHeight() > 0) ? '-h' . $resolutionSettings->getHeight() : '');
                    $imageManageResourceV->getSourceImageResource()->setNewName($variantName);
                    $imageManageResourceV->save(null, $resolutionSettings->getExtension() == "default" ? null : $resolutionSettings->getExtension());
                }

            }

            if($this->cacheDirCommander) {
                $this->cacheDirCommander->setPath($cacheDirPath);
            }

            $currDir = $this->targetDirCommander->getRelativePath();
            if($this->backup) {
                $this->targetDirCommander->addDirectory("backup", true);
                $this->targetDirCommander->copyFileFromAnotherDirectory($currDir, $newName, $file["only_extension"]);
            }

            $this->tmpDirCommander->setPath($tmpFilePath);
            $this->tmpDirCommander->removeFile($newName.".".$file["only_extension"]);

            $this->targetDirCommander->moveUp();

            array_push($this->uploadedFiles["images"], [
                "original" => $originalImageResource,
                "thumb" => $thumbImageResource
            ]);

        } else {
            $this->tmpDirCommander->setPath($tmpFilePath);
            $this->tmpDirCommander->copyFileToAnotherDirectory($this->targetDirCommander->getRelativePath(), $newName, $file["only_extension"]);
            $this->tmpDirCommander->removeFile($newName.".".$file["only_extension"]);
            array_push($this->uploadedFiles["files"], $this->targetDirCommander->getFile($newName, $file["only_extension"]));
        }

        return true;
    }

    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
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