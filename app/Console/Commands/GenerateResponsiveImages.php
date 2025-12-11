<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class GenerateResponsiveImages extends Command
{
    protected $signature = 'images:generate-responsive';
    protected $description = 'Generate webp images and image-meta.json based on orientation';

    public function handle(): int
    {
        //Relevant paths to get images and data
        $imageDirectory = resource_path('images/');
        $metadata = [];

        //Get all the files recursive based on Laravel File helper
        $files = collect(File::allFiles($imageDirectory))
            ->filter(fn($file) => preg_match('/\.(jpe?g|png)$/i', $file->getFilename()))
            ->map(fn($file) => $file->getRealPath());

        foreach ($files as $file) {
            //Define sizes based on orientation
            $identify = new Process(['magick', 'identify', '-format', '%w %h', $file]);
            $identify->run();

            if (!$identify->isSuccessful()) {
                $this->error("Failed to identify image dimensions for {$file}");
                continue;
            }

            [$width, $height] = explode(' ', trim($identify->getOutput()));
            $sizes = [
                (int)($width / 3) => 'small',
                (int)($width / 2) => 'medium',
                (int)$width => 'large'
            ];

            $filename = basename($file);
            $metadata[$filename] = [
                'original_width' => (int)$width,
                'original_height' => (int)$height,
                'sizes' => []
            ];

            //Store webp versions in 3 sizes
            foreach ($sizes as $size => $label) {
                $output = preg_replace('/\.(jpe?g|png)$/i', "-{$label}.webp", $file);
                $process = new Process(["magick", $file, "-resize", $size, $output]);
                $process->run();
                
                if ($process->isSuccessful()) {
                    $metadata[$filename]['sizes'][$label] = $size;
                    $this->info("✓ Generated {$label} variant for {$filename} ({$size}px)");
                } else {
                    $this->error("Failed to convert $file to $output");
                }
            }
        }

        //Store JSON with orientation meta-information per file
        File::put(
            resource_path('images/image-meta.json'),
            json_encode($metadata, JSON_PRETTY_PRINT)
        );

        $this->info('✅ Responsive images and image-meta.json generated!');
        return Command::SUCCESS;
    }
}
