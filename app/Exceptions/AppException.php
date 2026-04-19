<?php

namespace App\Exceptions;

use App\Constants\HttpStatusCodeConstants;
use Illuminate\Http\Request;

class AppException extends \Exception
{
    protected $code;

    public function __construct(string $message = 'An error occurred.', int $code = HttpStatusCodeConstants::BAD_REQUEST)
    {
        parent::__construct($message);
        $this->code = $code;
    }

    public function render(Request $request)
    {
        return response()->json([
            'success'  => false,
            'message' => $this->getMessage(),
            'data' => null
        ], $this->code);
    }
}