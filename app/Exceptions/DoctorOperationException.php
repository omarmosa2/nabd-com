<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorOperationException extends Exception
{
    public function __construct(
        string $message,
        protected array $errorFields = [],
        protected array $context = [],
        int $statusCode = 422
    ) {
        parent::__construct($message);
        $this->code = $statusCode;
    }

    public function render(Request $request): JsonResponse
    {
        $body = [
            'message' => $this->getMessage(),
            'errors' => array_fill_keys($this->errorFields, [$this->getMessage()]),
        ];
        if (!empty($this->context)) {
            $body['context'] = $this->context;
        }
        return response()->json($body, $this->code);
    }
}
