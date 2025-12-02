<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\File;

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

        // Extract filename from src
        $filename = basename($src);
        $imageMeta = $metadata[$filename];

        // Generate srcset with actual widths from metadata
        $name = str_replace(['.jpg', '.png'], '', $src);

        $smallWidth = $imageMeta['sizes']['small'];
        $mediumWidth = $imageMeta['sizes']['medium'];
        $largeWidth = $imageMeta['sizes']['large'];

        $this->srcSet = asset($name . '-small.webp') . " {$smallWidth}w, "
            . asset($name . '-medium.webp') . " {$mediumWidth}w, "
            . asset($name . '-large.webp') . " {$largeWidth}w";

        // Use large variant dimensions for the fallback img
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
