<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Logo untuk dokumen PDF diambil sebagai data URI. Dompdf tidak bisa membaca
     * URL aset dan symlink public/storage tidak selalu ada, sedangkan gambar base64
     * selalu ikut ter-render.
     */
    public static function logoDataUri(): ?string
    {
        $candidates = array_filter([
            static::get('store_logo') ? Storage::disk('public')->path(static::get('store_logo')) : null,
            public_path('logo.png'),
        ]);

        foreach ($candidates as $path) {
            if (!is_file($path)) {
                continue;
            }

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/png',
            };

            $binary = file_get_contents($path);
            if ($binary === false || $binary === '') {
                continue;
            }

            return 'data:' . $mime . ';base64,' . base64_encode($binary);
        }

        return null;
    }

    public static function set(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getMultiple(array $keys): array
    {
        $settings = self::whereIn('key', $keys)->pluck('value', 'key');
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $settings[$key] ?? null;
        }
        return $result;
    }
}
