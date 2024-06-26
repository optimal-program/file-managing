<?php declare(strict_types=1);

namespace Optimal\FileManaging;

use Optimal\FileManaging\Exception\DirectoryException;
use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\Exception\UploadFileException;
use Optimal\FileManaging\Resources\UploadedFilesResource;
use Optimal\FileManaging\Utils\FilesTypes;
use Optimal\FileManaging\Utils\ImageCropSettings;
use Optimal\FileManaging\Utils\FileUploaderUploadLimits;

class FileUploader
{

    private static $instance = null;

    /** @var array */
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

    /** @var ImageCropSettings|null */
    private $imageCropSettings = null;

    /** @var ImageCropSettings|null */
    private $imageThumbCropSettings = null;

    /** @var FileUploaderUploadLimits */
    private $uploadLimits;

    /** @var bool */
    private $autoRotateImages = true;

    /** @var bool */
    private $backup = false;

    /** @var array */
    private $successMessages;

    /** @var array */
    private $errorMessages;

    /** @var array */
    private $uploadedFiles;

    /**
     * @return FileUploader
     */
    public static function getInstance(): FileUploader
    {
        if (is_null(self::$instance)) {
            self::$instance = new FileUploader();
        }
        return self::$instance;
    }

    /**
     * FileUploader constructor.
     */
    private function __construct()
    {
        $this->uploadLimits = new FileUploaderUploadLimits();

        $this->imagesManager = new ImagesManager();

        $this->_FILES = [];

        foreach ($_FILES as $inputName => $filesItems) {
            if (is_array($filesItems["name"])) {
                foreach ($filesItems as $param => $values) {
                    foreach ($values as $key => $value) {
                        $this->_FILES[$inputName][$key][$param] = $value;
                        if ($param === "name") {
                            $this->_FILES[$inputName][$key]["only_name"] = strtolower(pathinfo($this->_FILES[$inputName][$key][$param], PATHINFO_FILENAME));
                            $this->_FILES[$inputName][$key]["only_extension"] = strtolower(pathinfo($this->_FILES[$inputName][$key][$param], PATHINFO_EXTENSION));
                        }
                    }
                }
            }
            else {
                foreach ($filesItems as $param => $value) {
                    $this->_FILES[$inputName][0][$param] = $value;
                    if ($param === "name") {
                        $this->_FILES[$inputName][0]["only_name"] = strtolower(pathinfo($this->_FILES[$inputName][0][$param], PATHINFO_FILENAME));
                        $this->_FILES[$inputName][0]["only_extension"] = strtolower(pathinfo($this->_FILES[$inputName][0][$param], PATHINFO_EXTENSION));
                    }
                }
            }
        }

        $this->maxImageWidth = 3840;
        $this->maxImageHeight = 2160;

        $this->uploadedFiles = ["images" => [], "files" => []];
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
    public function enableBackup($enable = false): void
    {
        $this->backup = $enable;
    }

    /**
     * @param array $messages
     */
    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    /**
     * @param $msg
     * @param $file
     * @return string
     */
    private function parseMessage($msg, $file): string
    {
        $msg = str_replace("{fileName}", $file["only_name"], $msg);
        $msg = str_replace("{fileExtension}", $file["only_extension"], $msg);
        $msg = str_replace("{fileFull}", $file["only_name"] . "." . $file["only_extension"], $msg);
        $msg = str_replace("{fileSize}", (string) $file["size"], $msg);
        return $msg;
    }

    /**
     * @param FileUploaderUploadLimits $limits
     */
    public function setUploadLimits(FileUploaderUploadLimits $limits): void
    {
        $this->uploadLimits = $limits;
    }

    /**
     * @param string $directory
     * @throws Exception\DirectoryNotFoundException
     */
    public function setTemporaryDirectory(string $directory): void
    {
        $this->tmpDirCommander = new FileCommander();
        $this->tmpDirCommander->setPath($directory);
    }

    /**
     * @param string $directory
     * @throws Exception\DirectoryNotFoundException
     */
    public function setTargetDirectory(string $directory): void
    {
        $this->targetDirCommander = new FileCommander();
        $this->targetDirCommander->setPath($directory);
    }

    /* TODO - enable crop images
    public function setimagecropsettings(imagecropsettings $settings){
        $this->imagecropsettings = $settings;
    }
    */

    /** TODO - enable crop images
     * public function setimagethumbcropsettings(imagecropsettings $settings){
     * $this->imagethumbcropsettings = $settings;
     * }
     */

    /**
     * @param bool $rotate
     */
    public function autoRotateImages(bool $rotate = true): void
    {
        $this->autoRotateImages = $rotate;
    }

    /**
     * @param string $resource
     */
    public function setImageManageResourceType(string $resource = ImagesManager::RESOURCE_TYPE_GD): void
    {
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
    public function isPostFile(string $inputName): bool
    {

        if (empty($this->_FILES)) {
            return false;
        }

        foreach ($this->_FILES as $key => $val) {

            if ($key != $inputName) {
                continue;
            }

            if ($val[0]["error"] === 4) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $inputName
     * @return int
     */
    public function countInputFiles(string $inputName): int
    {
        if (!isset($this->_FILES[$inputName])) {
            return 0;
        }
        return count($this->_FILES[$inputName]);
    }

    /**
     * @param string $inputName
     * @param int $index
     * @return array
     */
    public function getFileToUploadData(string $inputName, int $index): array
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
    public function uploadFile(string $inputName, int $index, ?string $newFileName = null, bool $overwrite = true, callable $beforeUploadCallback = null, callable $afterUploadCallback = null): void
    {
        if (!$this->targetDirCommander || !$this->tmpDirCommander) {
            throw new DirectoryException("Temporary or target directory is not defined");
        }

        if ($this->checkFile($this->_FILES[$inputName][$index])) {

            $file = $this->_FILES[$inputName][$index];
            $newName = uniqid();

            if ($newFileName !== null) {
                $newName = $newFileName;
            }

            if (!$overwrite) {
                $i = 1;
                if ($this->targetDirCommander->fileExists($newName, $file["only_extension"])) {
                    while ($this->targetDirCommander->fileExists($newName . "_" . $i, $file["only_extension"])) {
                        $i++;
                    }
                }
                $newName .= "_" . $i;
            }

            $this->moveFile($this->_FILES[$inputName][$index], $newName, $beforeUploadCallback, $afterUploadCallback);
        }
    }

    /**
     * @param array $file
     * @return bool
     */
    private function checkFile(array $file): bool
    {

        switch ($file['error']) {
            case 0:
                break;
            case 1:
            case 2:
                $this->errorMessages[] = $this->parseMessage($this->messages["tooBig"], $file);
                return false;
                break;
            case 3:
                $this->errorMessages[] = $this->parseMessage($this->messages["notFull"], $file);
                return false;
                break;
            default:
                $this->errorMessages[] = $this->parseMessage($this->messages["otherProblem"], $file);
                return false;
                break;
        }

        if ($file['size'] === 0) {
            $this->errorMessages[] = $this->parseMessage($this->messages["isEmpty"], $file);
            return false;
        }
        if ($file['size'] > $this->uploadLimits->getMaxFileSize()) {
            $this->errorMessages[] = $this->parseMessage($this->messages["isTooLarge"], $file);
            return false;
        }

        if (preg_match("/php|phtml[\d]*?/i", $file["only_extension"]) || in_array($file["only_extension"], FilesTypes::DISALLOWED) || !in_array($file["only_extension"], $this->uploadLimits->getAllowedExtensions())) {
            $this->errorMessages[] = $this->parseMessage($this->messages["notAllowed"], $file);
            return false;
        }

        if (preg_match("/\/|\\|&|\||\?|\*/i", $file["only_name"])) {
            $this->errorMessages[] = $this->parseMessage($this->messages["wrongName"], $file);
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
     * @throws \ImagickException
     */
    private function moveFile(array $file, string $newName, callable $beforeUploadCallback = null, callable $afterUploadCallback = null): bool
    {

        if ($beforeUploadCallback) {
            $result = $beforeUploadCallback($file, $newName);
            if (is_array($result)) {
                if (isset($result['newName'])) {
                    $newName = $result['newName'];
                }
            }
            if (is_bool($result) && !$result) {
                return false;
            }
        }

        $success = move_uploaded_file($file['tmp_name'], $this->tmpDirCommander->getAbsolutePath() . "/" . $newName . "." . $file["only_extension"]);

        if ($success) {
            $this->successMessages[] = $this->parseMessage($this->messages["success"], $file);
        }
        else {
            $this->successMessages[] = $this->parseMessage($this->messages["nonSuccess"], $file);
            return false;
        }

        if (FileCommander::isImage($file["only_extension"])) {

            if (FileCommander::isBitmapImage($file["only_extension"])) {

                $this->imagesManager->setSourceDirectory($this->tmpDirCommander->getRelativePath());

                $imageManageResource = $this->imagesManager->loadImageManageResource($newName, $file["only_extension"], $this->imagesResourceType);

                if ($this->autoRotateImages) {
                    $imageManageResource->autoRotate();
                }

                $imageManageResource->maxResize($this->maxImageWidth, $this->maxImageHeight);

                // TODO image crop

                $imageManageResource->save($this->targetDirCommander->getRelativePath());
                $originalImageResource = $imageManageResource->getOutputImageResource();
                $originalImageResourceExt = $originalImageResource->getExtension();

                $thumbImageResource = null;
                if (!is_null($this->imageThumbCropSettings)) {

                    $this->imagesManager->setSourceDirectory($this->targetDirCommander->getRelativePath());

                    $imageManageResourceV = $this->imagesManager->loadImageManageResource($originalImageResource->getName(), $originalImageResourceExt, $this->imagesResourceType);

                    // TODO image thumb crop

                    $resource = $imageManageResourceV->getSourceImageResource();
                    $imageManageResourceV->save($this->targetDirCommander->getRelativePath(), $newName . "-thumb");

                    $thumbImageResource = $imageManageResourceV->getOutputImageResource();
                }

                $currDir = $this->targetDirCommander->getRelativePath();

                if ($this->backup) {
                    $this->targetDirCommander->addDirectory("backup", true);
                    $this->targetDirCommander->copyFileFromAnotherDirectory($currDir, $newName, $file["only_extension"]);
                }

                $this->tmpDirCommander->removeFile($newName . "." . $file["only_extension"]);

                $this->targetDirCommander->moveUp();

                $this->uploadedFiles["images"][] = [
                    'original' => $originalImageResource,
                    'thumb' => $thumbImageResource
                ];

                if (isset($afterUploadCallback)) {
                    $afterUploadCallback($originalImageResource, $thumbImageResource);
                }

            }
            else {

                $this->tmpDirCommander->copyFileToAnotherDirectory($newName, $file["only_extension"], $this->targetDirCommander->getRelativePath());
                $this->tmpDirCommander->removeFile($newName . "." . $file["only_extension"]);

                $vectorImageResource = $this->targetDirCommander->getImage($newName, $file["only_extension"]);

                $this->uploadedFiles["images"] = ['original' => $vectorImageResource];

                if (isset($afterUploadCallback)) {
                    $afterUploadCallback($vectorImageResource);
                }

            }

        }
        else {
            $this->tmpDirCommander->copyFileToAnotherDirectory($newName, $file["only_extension"], $this->targetDirCommander->getRelativePath());
            $this->tmpDirCommander->removeFile($newName . "." . $file["only_extension"]);

            $fileResource = $this->targetDirCommander->getFile($newName, $file["only_extension"]);

            $this->uploadedFiles["files"][] = $fileResource;

            if (isset($afterUploadCallback)) {
                $afterUploadCallback($fileResource);
            }
        }

        return true;
    }

    public function getUploadedFiles(): UploadedFilesResource
    {
        return new UploadedFilesResource($this->uploadedFiles["files"], $this->uploadedFiles["images"]);
    }

    /**
     * @return array
     */
    public function getSuccessMessages(): array
    {
        return $this->successMessages;
    }

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    public function clear(): void
    {
        $this->uploadedFiles = ["images" => [], "files" => []];
        $this->successMessages = [];
        $this->errorMessages = [];
    }

}