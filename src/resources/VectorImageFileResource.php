<?php declare(strict_types=1);

namespace Optimal\FileManaging\resources;

class VectorImageFileResource extends AbstractImageFileResource
{

    function __construct(string $path, ?string $name = null, ?string $extension = null)
    {
        parent::__construct($path, $name, $extension);
    }

}