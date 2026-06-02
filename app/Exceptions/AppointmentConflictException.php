<?php

namespace App\Exceptions;

use Exception;

class AppointmentConflictException extends Exception
{
    /**
     * @param  array<int, array<string, mixed>>  $conflicts
     */
    public function __construct(string $message, public array $conflicts = [])
    {
        parent::__construct($message, 422);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'appointment_date' => [$this->getMessage()],
            ],
            'conflicts' => $this->conflicts,
        ], 422);
    }
}
