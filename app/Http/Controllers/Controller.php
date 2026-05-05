<?php

namespace App\Http\Controllers;

use App\Constants\HttpStatusCodeConstants;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    public function response($data = null, $message = 'success', int $code = HttpStatusCodeConstants::OK)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function responsePaginated($data, $items)
    {
        return response()->json([
            'success'       => true,
            'data'          => $data,
            'pagination'    => [
                'current_page'  => $items->currentPage(),
                'last_page'     => $items->lastPage(),
                'prev_page_url' => $items->previousPageUrl(),
                'next_page_url' => $items->nextPageUrl(),
                'per_page'      => $items->perPage(),
                'total'         => $items->total(),
                'count'         => $items->count(),
            ],
        ], HttpStatusCodeConstants::OK);
    }

    public function uuid()
    {
        return (string) Str::uuid();
    }

    public function currentDateTime()
    {
        return Carbon::now();
    }

    public function auth()
    {
        return Auth::user();
    }

    // public function removeSpace($string) {
    //     if (!is_string($string))
    //     {
    //         return $string;
    //     }
    //     else
    //     {
    //         $string = trim(mb_strtolower($string));
    //         return preg_replace('/\s+/', ' ', $string);
    //     }
    // }
}
