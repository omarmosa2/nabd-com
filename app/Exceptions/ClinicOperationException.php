<?php

namespace App\Exceptions;

use Exception;

class ClinicOperationException extends Exception
{
    public function __construct(string $message, public array $context = [])
    {
        parent::__construct($message, 422);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'context' => $this->context,
        ], 422);
    }
}
