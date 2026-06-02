<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingsService $settingsService,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->settingsService->get());
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'complex_name' => 'sometimes|string|max:255',
            'default_examination_fee' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|max:10',
            'currency_symbol' => 'sometimes|string|max:10',
            'invoice_header' => 'sometimes|string|max:255',
            'invoice_footer' => 'sometimes|string|max:255',
        ]);

        $settings = $this->settingsService->update($data);

        return response()->json($settings);
    }
}
