<?php declare(strict_types=1);

namespace Optimal\FileManaging\Resources;

use Optimal\FileManaging\Exception\DirectoryNotFoundException;
use Optimal\FileManaging\FileCommander;
use Optimal\FileManaging\Utils\SystemPaths;

abstract class AbstractFileResource
{
    protected ?string $name;

    protected ?string $extension;

    protected int $size;

    protected string $path;

    /**
     * AbstractFileResource constructor.
     * @param string $path
     * @param string|null $name
     * @param string|null $extension
     * @throws DirectoryNotFoundException
     */
    public function __construct(string $path, ?string $name = null, ?string $extension = null)
    {
        $validPath = FileCommander::checkPath($path);

        if (!is_dir($validPath)) {
            $name = (string) pathinfo($validPath, PATHINFO_FILENAME);
            $extension = (string) pathinfo($validPath, PATHINFO_EXTENSION);
            $validPath = (string) pathinfo($validPath, PATHINFO_DIRNAME);
        }
        elseif ($extension === null) {
            $filePath = $validPath . "/" . $name;
            $name = (string) pathinfo($filePath, PATHINFO_FILENAME);
            $extension = (string) pathinfo($filePath, PATHINFO_EXTENSION);
        }

        FileCommander::checkPath($validPath . "/" . $name . "." . $extension);

        $this->name = $name;
        $this->extension = $extension;
        $this->path = $validPath;
        $this->setFileInfo();
    }

    protected function setFileInfo():void
    {
        $this->size = filesize($this->path . "/" . $this->name . "." . $this->extension);
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getNameExtension(): string
    {
        return $this->name . "." . $this->extension;
    }

    /**
     * @return string
     */
    public function getFileDirectoryPath(): string
    {
        return $this->path;
    }

    /**
     * @param $dir
     */
    public function setFileDirectoryPath($dir): void
    {
        $this->path = $dir;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->path . "/" . $this->getNameExtension();
    }

    /**
     * @return string
     */
    public function getFileRelativePath(): string
    {
        return ltrim(str_replace(SystemPaths::getScriptPath(), "", $this->path), "/") . "/" . $this->name . "." . $this->extension;
    }

    /**
     * @return string
     */
    public function getUrlToFile(): string
    {
        return SystemPaths::getBaseUrl() . "/" . $this->path . "/" . $this->name . "." . $this->extension;
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->size;
    }

    /**
     * @param string $string
     * @return string
     */
    public function parseString(string $string): string
    {
        return strtr($string, [
            '{realName}'      => $this->getName(),
            '{realExtension}' => $this->getExtension(),
            '{realNameEx}'    => $this->getNameExtension(),
            '{realFileSize}'  => $this->getFileSize(),
        ]);
    }
}