<?php

namespace Optimal\FileManaging\resources;

class UploadedFilesResource
{

    private $uploadedFiles = [];
    private $uploadedImages = [];

    function __construct($uploadedFiles, $uploadedImages)
    {
        $this->uploadedFiles = $uploadedFiles;
        $this->uploadedImages = $uploadedImages;
    }

    /**
     * @return array
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * @return array
     */
    public function getUploadedImages(): array
    {
        return $this->uploadedImages;
    }

}