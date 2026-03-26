<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Artisan;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeExistingImages extends Command
{
    protected $signature = 'images:optimize {--dry-run : Solo mostrar que se procesaria sin hacer cambios}';
    protected $description = 'Convierte imagenes existentes de productos y artesanos a WebP optimizado';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $imageService = new ImageService();
        $processed = 0;
        $skipped = 0;

        // Productos
        $products = Product::whereNotNull('images')->get();
        foreach ($products as $product) {
            $images = $product->images;
            $newImages = [];
            $changed = false;

            foreach ($images as $imagePath) {
                if (str_ends_with($imagePath, '.webp')) {
                    $newImages[] = $imagePath;
                    $skipped++;
                    continue;
                }

                $fullPath = Storage::disk('public')->path($imagePath);
                if (!file_exists($fullPath)) {
                    $this->warn("Archivo no encontrado: {$imagePath}");
                    $newImages[] = $imagePath;
                    continue;
                }

                if ($dryRun) {
                    $size = round(filesize($fullPath) / 1024);
                    $this->info("[DRY-RUN] Procesaria: {$imagePath} ({$size} KiB)");
                    $newImages[] = $imagePath;
                    $processed++;
                    continue;
                }

                $this->info("Procesando: {$imagePath}");
                $image = $imageService->processAndStore(
                    new \Illuminate\Http\UploadedFile($fullPath, basename($fullPath)),
                    'products',
                    800,
                    80
                );
                $newImages[] = $image;
                $changed = true;
                $processed++;
            }

            if (!$dryRun && $changed) {
                $product->update(['images' => $newImages]);
            }
        }

        // Artesanos
        $artisans = Artisan::whereNotNull('photo')->where('photo', '!=', '')->get();
        foreach ($artisans as $artisan) {
            if (str_ends_with($artisan->photo, '.webp')) {
                $skipped++;
                continue;
            }

            $fullPath = Storage::disk('public')->path($artisan->photo);
            if (!file_exists($fullPath)) {
                $this->warn("Archivo no encontrado: {$artisan->photo}");
                continue;
            }

            if ($dryRun) {
                $size = round(filesize($fullPath) / 1024);
                $this->info("[DRY-RUN] Procesaria: {$artisan->photo} ({$size} KiB)");
                $processed++;
                continue;
            }

            $this->info("Procesando: {$artisan->photo}");
            $newPath = $imageService->processAndStore(
                new \Illuminate\Http\UploadedFile($fullPath, basename($fullPath)),
                'artisans',
                400,
                80
            );
            $artisan->update(['photo' => $newPath]);
            $processed++;
        }

        $this->info("Procesadas: {$processed} | Saltadas (ya WebP): {$skipped}");

        return Command::SUCCESS;
    }
}
