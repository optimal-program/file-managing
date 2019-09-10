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

        $suffix = null;
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        switch (substr($size, -1)) {
            case 'K':
            case 'k':
                $suffix = "KB";
                $number = (int) substr($size, 0, -1);
            break;
            case 'M':
            case 'm':
                $suffix = "MB";
                $number = (int) substr($size, 0, -1);
            break;
            case 'G':
            case 'g':
                $suffix = "GB";
                $number = (int) substr($size, 0, -1);
            break;
            default:
                $suffix = strtoupper(substr($size,-2));
                $number = (int) substr($size, 0, -2);

                //B or no suffix
                if(is_numeric(substr($suffix, 0, 1))) {
                    return (int) preg_replace('/[^\d]/', '', $size);
                }

            break;
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if($exponent === null) {
            return 0;
        }

        return $number * (1024 ** $exponent);
    }

    /**
     * @return float
     */
    public static function getPostMaxSize():int {
        return self::$PostMaxSize;
    }

    /**
     * @return float
     */
    public static function getMaxFileSize():int {
        return self::$UploadMaxFileSize;
    }

    /**
     * @return int
     */
    public static function getMaxFilesCount():int {
        return (int) self::$maxFileUploads;
    }

}