<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

class IniInfo
{

    /** @var string|null */
    private static $UploadMaxFileSize;

    /** @var string|null */
    private static $PostMaxSize;

    /** @var string|null */
    private static $maxFileUploads;

    private static function load(): void
    {
        if (is_null(self::$PostMaxSize)) {
            self::$PostMaxSize = ini_get('post_max_size');
            self::$UploadMaxFileSize = ini_get('upload_max_filesize');
            self::$maxFileUploads = ini_get('max_file_uploads');
        }
    }

    /**
     * @param string $size
     * @return int
     */
    public static function toBytes(string $size): int
    {

        $suffix = null;
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        switch (substr($size, -1)) {
            case 'K':
            case 'k':
                $suffix = "KB";
                $number = (int)substr($size, 0, -1);
                break;
            case 'M':
            case 'm':
                $suffix = "MB";
                $number = (int)substr($size, 0, -1);
                break;
            case 'G':
            case 'g':
                $suffix = "GB";
                $number = (int)substr($size, 0, -1);
                break;
            default:
                $suffix = strtoupper(substr($size, -2));
                $number = (int)substr($size, 0, -2);

                //B or no suffix
                if (is_numeric($suffix[0])) {
                    return (int)preg_replace('/[^\d]/', '', $size);
                }

                break;
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if ($exponent === null) {
            return 0;
        }

        return $number * (1024 ** $exponent);
    }

    /**
     * @param bool $toBytes
     * @return int|string
     */
    public static function getPostMaxSize(bool $toBytes = true)
    {
        self::load();
        return $toBytes ? self::toBytes(self::$PostMaxSize) : self::$PostMaxSize;
    }

    /**
     * @param bool $toBytes
     * @return int|string
     */
    public static function getMaxFileSize(bool $toBytes = true)
    {
        self::load();
        return $toBytes ? self::toBytes(self::$UploadMaxFileSize) : self::$UploadMaxFileSize;
    }

    /**
     * @return int
     */
    public static function getMaxFilesCount(): int
    {
        self::load();
        return (int)self::$maxFileUploads;
    }

}