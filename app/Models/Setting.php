<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'display_name',
        'description',
        'is_public',
        'options',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'options' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return $setting->getValue();
        });
    }
    
    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return void
     */
    public static function set(string $key, $value, string $type = 'string'): void
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->type = $type;
        
        if (!$setting->exists) {
            $setting->group = $setting->group ?? 'general';
            $setting->display_name = $setting->display_name ?? ucwords(str_replace('_', ' ', $key));
            $setting->is_public = $setting->is_public ?? false;
        }
        
        $setting->save();
        
        // Clear the cache
        Cache::forget("setting.{$key}");
    }
    
    /**
     * Get the casted value of the setting.
     *
     * @return mixed
     */
    public function getValue()
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'array' => json_decode($this->value, true) ?? [],
            'json' => json_decode($this->value, true) ?? [],
            default => $this->value,
        };
    }
    
    /**
     * Get all settings as an associative array.
     *
     * @param bool $publicOnly
     * @return array
     */
    public static function allSettings(bool $publicOnly = false): array
    {
        $query = static::query();
        
        if ($publicOnly) {
            $query->where('is_public', true);
        }
        
        return $query->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group')
            ->mapWithKeys(function ($items, $group) {
                return [
                    $group => $items->mapWithKeys(function ($item) {
                        return [$item->key => $item->getValue()];
                    })->toArray()
                ];
            })->toArray();
    }
    
    /**
     * Get settings by group.
     *
     * @param string $group
     * @param bool $publicOnly
     * @return array
     */
    public static function getByGroup(string $group, bool $publicOnly = false): array
    {
        $query = static::where('group', $group);
        
        if ($publicOnly) {
            $query->where('is_public', true);
        }
        
        return $query->orderBy('sort_order')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->key => $item->getValue()];
            })->toArray();
    }
    
    /**
     * Clear the settings cache.
     *
     * @param string|null $key
     * @return void
     */
    public static function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget("setting.{$key}");
        } else {
            $keys = static::pluck('key');
            foreach ($keys as $settingKey) {
                Cache::forget("setting.{$settingKey}");
            }
        }
    }
    
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(function ($setting) {
            $setting->clearCache($setting->key);
        });
        
        static::deleted(function ($setting) {
            $setting->clearCache($setting->key);
        });
    }
}
