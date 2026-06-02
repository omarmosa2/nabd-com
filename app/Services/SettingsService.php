<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class SettingsService
{
    protected string $settingsPath;

    public function __construct()
    {
        $this->settingsPath = storage_path('app/settings.json');
    }

    public function get(): array
    {
        if (!File::exists($this->settingsPath)) {
            return $this->defaults();
        }

        return json_decode(File::get($this->settingsPath), true) ?? $this->defaults();
    }

    public function update(array $data): array
    {
        $current = $this->get();
        $merged = array_merge($current, $data);

        File::put($this->settingsPath, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $merged;
    }

    protected function defaults(): array
    {
        return [
            'complex_name' => 'مجمع نبض الطبي',
            'default_examination_fee' => 150,
            'currency' => 'SAR',
            'currency_symbol' => 'ر.س',
            'invoice_header' => 'مجمع نبض الطبي',
            'invoice_footer' => 'شكراً لزيارتكم',
        ];
    }
}
