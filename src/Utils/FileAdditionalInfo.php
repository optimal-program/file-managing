<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 16.02.2018
 * Time: 21:08
 */

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
     * @return mixed
     */
    public function getDbId()
    {
        return $this->dbId;
    }

    /**
     * @param mixed $dbId
     */
    public function setDbId($dbId)
    {
        $this->dbId = $dbId;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

}