<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Procesa y guarda una imagen subida.
     * Redimensiona al ancho maximo y convierte a WebP.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory (ej: 'products', 'artisans')
     * @param int $maxWidth Ancho maximo en px
     * @param int $quality Calidad WebP (1-100)
     * @return string Path relativo al storage
     */
    public function processAndStore($file, string $directory = 'products', int $maxWidth = 800, int $quality = 80): string
    {
        $image = $this->manager->read($file->getPathname());

        // Redimensionar si excede el ancho maximo (mantiene aspect ratio)
        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        // Generar nombre unico con extension .webp
        $filename = uniqid() . '_' . time() . '.webp';
        $path = $directory . '/' . $filename;

        // Codificar a WebP
        $encoded = $image->toWebp($quality);

        // Guardar en storage/app/public/
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    /**
     * Genera un thumbnail de una imagen existente.
     *
     * @param string $originalPath Path relativo en storage
     * @param int $width
     * @param int $height
     * @return string Path del thumbnail
     */
    public function createThumbnail(string $originalPath, int $width = 200, int $height = 200): string
    {
        $fullPath = Storage::disk('public')->path($originalPath);

        if (!file_exists($fullPath)) {
            return $originalPath;
        }

        $image = $this->manager->read($fullPath);
        $image->cover($width, $height);

        $pathInfo = pathinfo($originalPath);
        $thumbPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['filename'] . '.webp';

        $encoded = $image->toWebp(70);
        Storage::disk('public')->put($thumbPath, (string) $encoded);

        return $thumbPath;
    }
}
