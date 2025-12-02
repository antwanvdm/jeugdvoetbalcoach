<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;

class ResponsiveImage extends Component
{
    public string $srcSet;
    public int $width;
    public int $height;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $src,
        public string $alt,
        public string $class,
        public string $sizes,
        public bool $lazy = true,
    )
    {
        // Load image metadata
        $metaPath = resource_path('images/image-meta.json');
        $metadata = json_decode(File::get($metaPath), true);

        // Extract filename from src and remove Vite hash if present
        $filename = basename($src);
        $parts = pathinfo($filename);
        $nameWithoutExt = $parts['filename'];
        $extension = $parts['extension'];

        // Check if the last part after dash looks like a Vite hash to strip and get the real image from metadata
        $imageMeta = $metadata[$nameWithoutExt . '.' . $extension] ?? null;
        if (!$imageMeta) {
            $exceptionLastDash = strrpos($nameWithoutExt, '--');
            $lastDash = $exceptionLastDash === false ? strrpos($nameWithoutExt, '-') : $exceptionLastDash;
            $name = substr($nameWithoutExt, 0, $lastDash);
            $imageMeta = $metadata[$name . '.' . $extension];
        } else {
            $name = $nameWithoutExt;
        }

        $smallWidth = $imageMeta['sizes']['small'];
        $mediumWidth = $imageMeta['sizes']['medium'];
        $largeWidth = $imageMeta['sizes']['large'];

        // Use Vite::asset for proper hash handling in production
        $this->srcSet = Vite::asset("resources/images/{$name}-small.webp") . " {$smallWidth}w, "
            . Vite::asset("resources/images/{$name}-medium.webp") . " {$mediumWidth}w, "
            . Vite::asset("resources/images/{$name}-large.webp") . " {$largeWidth}w";

        // Use original dimensions for the fallback img
        $this->width = $imageMeta['original_width'];
        $this->height = $imageMeta['original_height'];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.responsive-image');
    }
}
