<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Get all settings grouped by their group
     *
     * @param bool $publicOnly
     * @return array
     */
    public function getAllSettings(bool $publicOnly = false): array
    {
        return Setting::allSettings($publicOnly);
    }

    /**
     * Get settings by group
     *
     * @param string $group
     * @param bool $publicOnly
     * @return array
     */
    public function getSettingsByGroup(string $group, bool $publicOnly = false): array
    {
        return Setting::getByGroup($group, $publicOnly);
    }

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param array $options
     * @return void
     */
    public function setSetting(
        string $key, 
        $value, 
        string $type = 'string', 
        array $options = []
    ): void {
        $setting = Setting::firstOrNew(['key' => $key]);
        
        // If this is a new setting, set default values
        if (!$setting->exists) {
            $setting->group = $options['group'] ?? 'general';
            $setting->display_name = $options['display_name'] ?? ucwords(str_replace('_', ' ', $key));
            $setting->description = $options['description'] ?? '';
            $setting->is_public = $options['is_public'] ?? false;
            $setting->options = $options['options'] ?? null;
            $setting->sort_order = $options['sort_order'] ?? 0;
        }
        
        // Update the value and type
        $setting->value = $value;
        $setting->type = $type;
        
        // Save the setting
        $setting->save();
    }

    /**
     * Set multiple settings at once
     *
     * @param array $settings
     * @return void
     */
    public function setMultipleSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->setSetting($key, $value);
        }
    }

    /**
     * Get all public settings for the API
     *
     * @return array
     */
    public function getPublicSettings(): array
    {
        return $this->getAllSettings(true);
    }

    /**
     * Get app settings for the API
     *
     * @return array
     */
    public function getAppSettings(): array
    {
        return $this->getSettingsByGroup('app', true);
    }

    /**
     * Get user settings for the API
     *
     * @return array
     */
    public function getUserSettings(): array
    {
        return $this->getSettingsByGroup('user', true);
    }

    /**
     * Get swipe settings for the API
     *
     * @return array
     */
    public function getSwipeSettings(): array
    {
        return $this->getSettingsByGroup('swipe', true);
    }

    /**
     * Get chat settings for the API
     *
     * @return array
     */
    public function getChatSettings(): array
    {
        return $this->getSettingsByGroup('chat', true);
    }

    /**
     * Clear the settings cache
     *
     * @param string|null $key
     * @return void
     */
    public function clearCache(?string $key = null): void
    {
        Setting::clearCache($key);
    }
}
