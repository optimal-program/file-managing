<?php

namespace Optimal\FileManaging\Exception;

use Throwable;

class CreateFileException extends FileException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

