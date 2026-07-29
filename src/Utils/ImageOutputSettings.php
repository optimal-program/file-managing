<?php declare(strict_types=1);

namespace Optimal\FileManaging\Utils;

use RuntimeException;

/**
 * Settings of the resulting image - maximum dimensions in px the image is resized into,
 * output image format and compression quality.
 */
class ImageOutputSettings
{

    /** Formats the image can be saved into */
    public const ALLOWED_OUTPUT_EXTENSIONS = ["jpg", "jpeg", "jfif", "webp", "png", "gif"];

    /** null means the dimension is not limited */
    private ?int $maxWidth = null;
    private ?int $maxHeight = null;

    /** null means the image keeps the format it was loaded/uploaded in */
    private ?string $outputExtension = null;

    /** null means the default quality of the used image library is applied */
    private ?int $quality = null;

    public function __construct(?int $maxWidth = null, ?int $maxHeight = null, ?string $outputExtension = null, ?int $quality = null)
    {
        $this->setMaxResolution($maxWidth, $maxHeight);
        $this->setOutputExtension($outputExtension);
        $this->setQuality($quality);
    }

    public function getMaxWidth(): ?int
    {
        return $this->maxWidth;
    }

    public function setMaxWidth(?int $maxWidth): static
    {
        if (!is_null($maxWidth) && $maxWidth < 1) {
            throw new RuntimeException("Max image width must be greater than zero");
        }

        $this->maxWidth = $maxWidth;
        return $this;
    }

    public function getMaxHeight(): ?int
    {
        return $this->maxHeight;
    }

    public function setMaxHeight(?int $maxHeight): static
    {
        if (!is_null($maxHeight) && $maxHeight < 1) {
            throw new RuntimeException("Max image height must be greater than zero");
        }

        $this->maxHeight = $maxHeight;
        return $this;
    }

    /**
     * Maximum dimensions in px the image is proportionally resized into, smaller image is not
     * enlarged. Null dimension is not limited at all.
     */
    public function setMaxResolution(?int $maxWidth, ?int $maxHeight = null): static
    {
        $this->setMaxWidth($maxWidth);
        $this->setMaxHeight($maxHeight);
        return $this;
    }

    public function isResolutionLimited(): bool
    {
        return !is_null($this->maxWidth) || !is_null($this->maxHeight);
    }

    public function getOutputExtension(): ?string
    {
        return $this->outputExtension;
    }

    public function setOutputExtension(?string $outputExtension): static
    {
        if (is_null($outputExtension)) {
            $this->outputExtension = null;
            return $this;
        }

        $extension = strtolower(ltrim($outputExtension, "."));

        if (!in_array($extension, self::ALLOWED_OUTPUT_EXTENSIONS, true)) {
            throw new RuntimeException("Image output extension: '" . $outputExtension . "' is not allowed");
        }

        $this->outputExtension = $extension;
        return $this;
    }

    public function getQuality(): ?int
    {
        return $this->quality;
    }

    public function setQuality(?int $quality): static
    {
        if (!is_null($quality) && ($quality < 0 || $quality > 100)) {
            throw new RuntimeException("Image quality must be between 0 and 100");
        }

        $this->quality = $quality;
        return $this;
    }

    /**
     * Extension the image with given extension is saved with.
     */
    public function getTargetExtension(string $currentExtension): string
    {
        return $this->outputExtension ?? strtolower($currentExtension);
    }

}
