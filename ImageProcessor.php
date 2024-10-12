<?php

use Intervention\Image\ImageManagerStatic as Image;

class ImageProcessor
{
    private $imgDir;

    public function __construct($imgDir)
    {
        $this->imgDir = $imgDir;
        if (!file_exists($this->imgDir)) {
            mkdir($this->imgDir, 0755, true);
        }
    }

    public function processImage($imagePath)
    {
        $cover = Image::make($imagePath);
        $shadow = Image::make('sombra.png');

        // Redimensionar a sombra para ter a mesma largura da capa
        $shadowAspectRatio = $shadow->width() / $shadow->height();
        $newShadowHeight = $cover->width() / $shadowAspectRatio;
        $shadow->resize($cover->width(), $newShadowHeight);

        // Criar uma nova imagem com altura suficiente para a capa e a sombra
        $processedImage = Image::canvas($cover->width(), $cover->height() + $shadow->height());

        // Inserir a capa no topo
        $processedImage->insert($cover, 'top');

        // Inserir a sombra abaixo da capa
        $processedImage->insert($shadow, 'bottom');

        $processedImagePath = $this->imgDir . '/processed_' . uniqid() . '.webp';
        $processedImage->save($processedImagePath);

        return $processedImagePath;
    }

    public function cleanOldImages()
    {
        $files = glob($this->imgDir . '/*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 24 * 3600) { // 24 horas em segundos
                    unlink($file);
                }
            }
        }
    }
}
