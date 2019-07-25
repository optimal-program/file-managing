<?php

namespace Optimal\FileManagingTest;

use Optimal\FileManaging\FileCommander;
use Optimal\FileManaging\Utils\SystemPaths;

class FileCommanderTest extends \PHPUnit_Framework_TestCase
{
    private $fileCommander;
    private $dir;

    protected function setUp()
    {
        $this->fileCommander = new FileCommander();
        $this->fileCommander->setPath(__DIR__ . "/images");
        $this->dir = str_replace("\\", "/", __DIR__);
    }

    public function testGetAbsolutePath()
    {
        $this->assertSame($this->dir . "/images", $this->fileCommander->getAbsolutePath());
    }



}
