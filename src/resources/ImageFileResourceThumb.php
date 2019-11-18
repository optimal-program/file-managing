<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

class ImageFileResourceThumb extends AbstractImageFileResource
{

    protected $main;

    /**
     * ImageThumbResource constructor.
     * @param string $path
     * @param string|null $name
     * @param string|null $extension
     * @throws \Optimal\FileManaging\Exception\FileException
     */
    function __construct(string $path, ?string $name = null,?string $extension = null)
    {
        parent::__construct($path, $name, $extension);
        $this->main = false;
    }

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->main;
    }

    /**
     * @param bool $isMain
     */
    public function setIsMain(bool $isMain): void
    {
        $this->main = $isMain;
    }



}