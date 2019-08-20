<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

class IniInfo
{

    private static $UploadMaxFileSize;
    private static $PostMaxSize;
    private static $maxFileUploads;

    public static function init(){

        $maxUploadFileSize = ini_get('upload_max_filesize');
        $maxPostSize = ini_get('post_max_size');

        self::$PostMaxSize = self::toBytes($maxPostSize);
        self::$UploadMaxFileSize = self::toBytes($maxUploadFileSize);
        self::$maxFileUploads = ini_get('max_file_uploads');
    }

    /**
     * @param string $size
     * @return int
     */
    public static function toBytes(string $size):int {

        switch (substr($size, -2, 2)){
            case 'Gb':
                $size = str_replace("Gb","G",$size);
                break;
            case 'Mb':
                $size = str_replace("Mb","M",$size);
                break;
            case 'Kb':
                $size = str_replace("Kb","K",$size);
                break;
            default:
                break;
        }

        switch (substr($size, -1)) {
            case 'K':
            case 'k':
                $maxPostSize = floatval($size);
                $maxPostSize *= 1024;
                break;
            case 'M':
            case 'm':
                $maxPostSize = floatval($size);
                $maxPostSize *= pow(1024, 2);
                break;
            case 'G':
            case 'g':
                $maxPostSize = floatval($size);
                $maxPostSize *= pow(1024, 3);
                break;
            default:
                $maxPostSize = $size;
                break;
        }

        return $maxPostSize;

    }

    /**
     * @return int
     */
    public static function getPostMaxSize():int {
        return self::$PostMaxSize;
    }

    /**
     * @return int
     */
    public static function getMaxFileSize():int {
        return self::$UploadMaxFileSize;
    }

    /**
     * @return int
     */
    public static function getMaxFilesCount():int {
        return self::$maxFileUploads;
    }

}