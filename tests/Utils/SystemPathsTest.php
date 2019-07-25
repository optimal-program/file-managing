<?php

namespace Optimal\FileManagingTest\Utils;

use Optimal\FileManaging\Utils\SystemPaths;

class SystemPathsTest extends \PHPUnit_Framework_TestCase
{
    private $dir;

    protected function setUp()
    {
        $this->dir = str_replace("\\", "/", __DIR__);
    }


    public function testGetScriptPath()
    {
        /*SystemPaths::getScriptPath();
        print_r($_SERVER);
        die;
        $this->assertSame($this->dir, SystemPaths::getScriptPath());*/
    }
}
