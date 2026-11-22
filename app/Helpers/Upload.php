<?php
/**
 * Akilli Kariyer - Dosya yukleme yardimcisi
 *
 * MIME kontrolu, boyut limiti ve guvenli dosya adi uretimi yapar.
 */

namespace App\Helpers;

final class Upload
{
    /**
     * Gorel dosya yukler.
     *
     * @param array $file  $_FILES elemanı
     * @param string $destDir
     * @return array{success:bool, path?:string, name?:string, error?:string}
     */
    public static function image(array $file, string $destDir = UPLOAD_DIR): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'error' => 'Geçersiz dosya verisi.'];
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'Dosya seçilmedi.'];
        }
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ['success' => false, 'error' => 'Dosya boyutu sunucu limitini aşıyor (maks. 2 MB).'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Dosya yüklenirken bir hata oluştu.'];
        }

        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return ['success' => false, 'error' => 'Dosya boyutu 2 MB\'den büyük olamaz.'];
        }

        // Guvenli dosya adi
        $originalName = basename((string) $file['name']);
        $extension    = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, ALLOWED_IMAGE_EXT, true)) {
            return ['success' => false, 'error' => 'Sadece JPG, PNG, WEBP veya GIF dosyaları yüklenebilir.'];
        }

        // MIME dogrulamasi (dosya imzasi)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
            return ['success' => false, 'error' => 'Dosya içeriği geçerli bir görsel değil.'];
        }

        // Gercek resim dogrulamasi
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'error' => 'Dosya okunamadı veya bozuk.'];
        }

        if (!is_dir($destDir) && !mkdir($destDir, 0775, true)) {
            return ['success' => false, 'error' => 'Yükleme klasörü oluşturulamadı.'];
        }

        $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
        $target   = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return ['success' => false, 'error' => 'Dosya kaydedilemedi.'];
        }

        @chmod($target, 0644);

        return [
            'success' => true,
            'path'    => 'uploads/' . $safeName,
            'name'    => $safeName,
        ];
    }

    /**
     * Yuklenen dosyayi siler.
     */
    public static function delete(?string $relativePath): void
    {
        if (!$relativePath || !str_starts_with($relativePath, 'uploads/')) {
            return;
        }
        $full = UPLOAD_DIR . '/' . basename($relativePath);
        if (is_file($full)) {
            @unlink($full);
        }
    }

    /**
     * Veritabaninda saklanan yol ile fiziksel dosyayi bulur.
     */
    public static function url(?string $relativePath): ?string
    {
        if (!$relativePath) {
            return null;
        }
        if (str_starts_with($relativePath, 'http')) {
            return $relativePath;
        }
        return url('storage/' . ltrim($relativePath, '/'));
    }
}
