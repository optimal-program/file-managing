<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 05.03.2018
 * Time: 14:18
 */

namespace Optimal\FileManaging\Exception;

use Throwable;

class DirectoryException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

