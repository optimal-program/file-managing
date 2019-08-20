<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

class FileAdditionalInfo
{

    private $dbId;
    private $name;
    private $title;
    private $description;

    function __construct()
    {
        $this->dbId = null;
        $this->name = "";
        $this->title = "";
        $this->description = "";
    }

    /**
     * @return int|null
     */
    public function getDbId():?int
    {
        return $this->dbId;
    }

    /**
     * @param int $dbId
     * @return FileAdditionalInfo
     */
    public function setDbId(int $dbId):FileAdditionalInfo
    {
        $this->dbId = $dbId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName():string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return FileAdditionalInfo
     */
    public function setName(string $name):FileAdditionalInfo
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle():string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return FileAdditionalInfo
     */
    public function setTitle(string $title):FileAdditionalInfo
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription():string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return FileAdditionalInfo
     */
    public function setDescription(string $description):FileAdditionalInfo
    {
        $this->description = $description;
        return $this;
    }

}