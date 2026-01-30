<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
    /**
     * Get all store settings
     */
    public function getStoreSettings()
    {
        return Setting::getStoreSettings();
    }

    /**
     * Update store settings
     */
    public function updateStoreSettings(array $settings)
    {
        foreach ($settings as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value, 'string', 'store');
            }
        }
    }

    /**
     * Handle file upload for logo or banner
     */
    public function uploadImage(UploadedFile $file, $type = 'logo')
    {
        // Create directory if it doesn't exist
        if (!Storage::disk('public')->exists('store')) {
            Storage::disk('public')->makeDirectory('store');
        }

        // Delete old file if exists
        $oldFile = Setting::get("store_{$type}");
        if ($oldFile && Storage::disk('public')->exists($oldFile)) {
            Storage::disk('public')->delete($oldFile);
        }

        // Store new file
        $filename = $type . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('store', $filename, 'public');

        // Update setting
        Setting::set("store_{$type}", $path, 'string', 'store');

        return $path;
    }

    /**
     * Delete uploaded image
     */
    public function deleteImage($type)
    {
        $file = Setting::get("store_{$type}");
        if ($file && Storage::disk('public')->exists($file)) {
            Storage::disk('public')->delete($file);
            Setting::set("store_{$type}", null, 'string', 'store');
            return true;
        }
        return false;
    }

    /**
     * Get store info for display
     */
    public function getStoreInfo()
    {
        return [
            'name' => Setting::get('store_name', 'Mini Mart POS'),
            'tagline' => Setting::get('store_tagline', 'Your Friendly Neighborhood Store'),
            'logo' => Setting::get('store_logo'),
            'banner' => Setting::get('store_banner'),
            'address' => Setting::get('store_address', ''),
            'phone' => Setting::get('store_phone', ''),
            'email' => Setting::get('store_email', ''),
            'tax_rate' => Setting::get('tax_rate', '0.00'),
            'currency_symbol' => Setting::get('currency_symbol', '$'),
            'receipt_footer' => Setting::get('receipt_footer', 'Thank you for your business!'),
        ];
    }
}