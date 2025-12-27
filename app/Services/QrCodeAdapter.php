<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeAdapter
{
    protected $size = 100;
    protected $margin = 0;
    protected $format = 'png';
    protected $encoding = 'UTF-8';

    /**
     * Handle static method calls (e.g., QrCode::format(...)).
     * Creates a new instance and delegates the call.
     */
    public static function __callStatic($method, $args)
    {
        $instance = new static;
        // Delegate to the instance method (which might be handled by __call or be a real method)
        return $instance->$method(...$args);
    }

    /**
     * Handle instance method calls (e.g., ->size(...)).
     * This allows us to map method names if needed, or just chain setters.
     */
    public function __call($method, $args)
    {
        // Map common SimpleQrCode methods to our internal logic if we renamed them
        // But here we rely on the fact that we renamed the public methods to have underscores
        // or just different names to avoid the "static call to non-static" conflict for the initial call.

        switch ($method) {
            case 'format':
                return $this->_format(...$args);
            case 'size':
                return $this->_size(...$args);
            case 'margin':
                return $this->_margin(...$args);
            case 'generate':
                return $this->_generate(...$args);
            case 'encoding':
                $this->encoding = $args[0] ?? 'UTF-8';
                return $this;
            case 'errorCorrection':
                // SimpleQrCode passes 'H', 'L', etc.
                // Endroid uses Enums/Objects. We can ignore or map if strictly needed.
                return $this;
            case 'merge':
                 // merge($image, $percentage, $absolute)
                 // Endroid supports logo. We could implement basic logo support.
                 return $this;
        }

        // If no match, return self to avoid crashing on unknown chain methods
        return $this;
    }

    // INTERNAL METHODS (Renamed to avoid conflict with static interface calls)

    protected function _format($format)
    {
        $this->format = $format;
        return $this;
    }

    protected function _size($size)
    {
        $this->size = $size;
        return $this;
    }

    protected function _margin($margin)
    {
        $this->margin = $margin;
        return $this;
    }

    protected function _generate($text, $filename = null)
    {
        $writer = $this->format === 'svg' ? new SvgWriter() : new PngWriter();

        $builder = Builder::create()
            ->writer($writer)
            ->data((string)$text)
            ->encoding(new Encoding($this->encoding))
            ->size($this->size)
            ->margin($this->margin);

        // Build the result
        $result = $builder->build();

        if ($filename) {
            // Ensure directory exists
            $dir = dirname($filename);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $result->saveToFile($filename);
            return $filename;
        }

        return $result->getString();
    }
}
