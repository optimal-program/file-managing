<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

class SystemPaths
{
    /**
     * @return string
     */
    public static function getScriptPath():string
    {
        return substr(self::getAbsoluteScriptName(), 0, strrpos(self::getAbsoluteScriptName(), "/"));
    }

    /**
     * @return string
     */
    public static function getAbsoluteScriptName():string
    {
        return str_replace("\\", "/", $_SERVER[ "SCRIPT_FILENAME" ]);
    }

    /**
     * @return string
     */
    public static function getRelativeScriptName():string
    {
        return str_replace("\\", "/", $_SERVER[ "SCRIPT_NAME" ]);
    }

    /**
     * @return string
     */
    public static function getBaseUrl():string
    {
        return SystemPaths::getUrlDomain() . substr(self::getRelativeScriptName(), 0,
                strrpos(self::getRelativeScriptName(), "/"));
    }

    /**
     * @return string
     */
    public static function getUrlDomain():string
    {
        $url = self::fullUrl($_SERVER);
        $url = str_replace("//", "*", $url);
        $url = explode("/", $url);

        $needed = str_replace("*", "//", $url[ 0 ]);

        return $needed;
    }

    /**
     * @param array $s
     * @param bool $use_forwarded_host
     * @return string
     */
    public static function fullUrl(array $s, bool $use_forwarded_host = false):string
    {
        $ssl = (!empty($s[ 'HTTPS' ]) && $s[ 'HTTPS' ] == 'on') ? true : false;
        $sp = strtolower($s[ 'SERVER_PROTOCOL' ]);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $s[ 'SERVER_PORT' ];
        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
        $host = ($use_forwarded_host && isset($s[ 'HTTP_X_FORWARDED_HOST' ])) ? $s[ 'HTTP_X_FORWARDED_HOST' ] : (isset($s[ 'HTTP_HOST' ]) ? $s[ 'HTTP_HOST' ] : null);
        $host = isset($host) ? $host : $s[ 'SERVER_NAME' ] . $port;

        return $protocol . '://' . $host . $s[ 'REQUEST_URI' ];
    }
}