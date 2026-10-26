<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'group',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('setting_key', $key)->first();
        if (!$setting) {
            return $default;
        }

        if ($setting->is_encrypted && !empty($setting->setting_value)) {
            try {
                return Crypt::decryptString($setting->setting_value);
            } catch (\Throwable $e) {
                return $default;
            }
        }

        return $setting->setting_value ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general', bool $encrypted = false): void
    {
        $val = $encrypted && !empty($value) ? Crypt::encryptString((string) $value) : (string) $value;

        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $val, 'group' => $group, 'is_encrypted' => $encrypted]
        );
    }
}
