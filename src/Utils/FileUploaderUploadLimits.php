<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

use Optimal\FileManaging\Exception\IniException;

class FileUploaderUploadLimits
{
    private int $iniMaxCount;
    private int $iniMaxFileSize;
    private int $iniMaxAllFilesSize;

    private int $maxCount;
    private int $maxFileSize;
    private ?string $maxFileSizeStr = null;
    private int $maxAllFilesSize;
    private ?string $maxAllFilesSizeStr = null;
    private array $allowedExtensions;

    public function __construct()
    {
        $this->iniMaxCount = $this->maxCount = IniInfo::getMaxFilesCount();
        $this->iniMaxFileSize = $this->maxFileSize = IniInfo::getMaxFileSize();
        $this->iniMaxAllFilesSize = $this->maxAllFilesSize = IniInfo::getPostMaxSize();
        $this->allowedExtensions = FilesTypes::ALL_SUPPORTED_FILES;
    }

    /**
     * @param int|null $maxCount
     * @param string|null $maxFileSizeStr
     * @param string|null $maxAllFilesSizeStr
     * @throws IniException
     */
    protected function checkIni(?int $maxCount = null, ?string $maxFileSizeStr = null, ?string $maxAllFilesSizeStr = null):void
    {

        if (!$maxCount) {
            $count = $this->maxCount;
        }

        if (!$maxFileSizeStr) {
            $maxFileSizeBytes = $this->maxFileSize;
        }
        else {
            $maxFileSizeBytes = IniInfo::toBytes($maxFileSizeStr);
        }

        if (!$maxAllFilesSizeStr) {
            $maxAllFilesSizeBytes = $this->maxAllFilesSize;
        }
        else {
            $maxAllFilesSizeBytes = IniInfo::toBytes($maxAllFilesSizeStr);
        }

        if ($maxCount > $this->iniMaxCount) {
            throw new IniException("Chosen max count is greater than is allowed in php ini (" . $this->maxCount . ")");
        }

        if ($maxFileSizeBytes > $this->iniMaxFileSize) {
            throw new IniException("Chosen max file size is greater than is allowed in php ini (" . IniInfo::getMaxFileSize(false) . ")");
        }

        if ($maxAllFilesSizeBytes > $this->iniMaxAllFilesSize) {
            throw new IniException("Chosen max post size is greater than is allowed in php ini (" . IniInfo::getPostMaxSize(false) . ")");
        }

    }

    /**
     * @param int $count
     * @throws IniException
     */
    public function setMaxCount(int $count):void
    {
        try {
            $this->checkIni($count);
            $this->maxCount = $count;
        } catch (IniException $e) {
            throw $e;
        }
    }

    /**
     * @param string $size
     * @throws IniException
     */
    public function setMaxFileSize(string $size):void
    {
        try {
            $this->checkIni(null, $size);
            $this->maxFileSize = IniInfo::toBytes($size);
            $this->maxFileSizeStr = $size;
        } catch (IniException $e) {
            throw $e;
        }
    }

    /**
     * @param string $size
     * @throws IniException
     */
    public function setMaxPostSize(string $size):void
    {
        try {
            $this->checkIni(null, null, $size);
            $this->maxAllFilesSize = IniInfo::toBytes($size);
            $this->maxAllFilesSizeStr = $size;
        } catch (IniException $e) {
            throw $e;
        }
    }

    /**
     * @param array $extensions
     */
    public function setAllowedExtensions(array $extensions):void
    {
        $this->allowedExtensions = $extensions;
    }

    /**
     * @param array $extensions
     */
    public function addAllowedExtensions(array $extensions):void
    {
        $intersection = array_intersect($this->allowedExtensions, $extensions);
        $this->allowedExtensions = array_merge($this->allowedExtensions, $intersection);
    }

    /**
     * @return int
     */
    public function getMaxFilesCount(): int
    {
        return $this->maxCount;
    }

    /**
     * @return int
     */
    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    /**
     * @return int
     */
    public function getMaxPostSize(): int
    {
        return $this->maxAllFilesSize;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    /**
     * @return array
     */
    public function getDisAllowedExtensions():array
    {
        return FilesTypes::DISALLOWED;
    }

}