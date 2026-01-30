<?php

namespace App\View\Composers;

use App\Services\SettingsService;
use Illuminate\View\View;

class StoreSettingsComposer
{
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function compose(View $view)
    {
        $storeInfo = $this->settingsService->getStoreInfo();
        $view->with('storeInfo', $storeInfo);
    }
}