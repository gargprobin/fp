<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    /**
     * Helper to get a setting value with a default fallback.
     */
    public static function getVal(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if ($setting) {
            if ($setting->type === 'boolean') {
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            }
            return $setting->value;
        }
        return $default;
    }

    /**
     * Helper to set/update a setting value.
     */
    public static function setVal(string $key, $value, string $type = 'string'): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type]
        );
    }
}
