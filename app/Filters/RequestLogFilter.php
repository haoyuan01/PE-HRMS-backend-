<?php

namespace App\Filters;

use Illuminate\Http\Request;
use Carbon\Carbon;

class RequestLogFilter
{
    public function apply(Request $filters, $size, $data)
    {
        if ($filters->has('uuid') && !empty($filters->uuid))
        {
            $data->where('uuid', $filters->uuid);
        }

        if ($filters->has('method') && !empty($filters->method))
        {
            $data->where('method', 'like', "%$filters->method%");
        }

        if ($filters->has('path') && !empty($filters->path))
        {
            $data->where('path', 'like', "%$filters->path%");
        }

        if ($filters->has('ip') && !empty($filters->ip))
        {
            $data->where('ip', 'like', "%$filters->ip%");
        }

        if ($filters->has('user_agent') && !empty($filters->user_agent))
        {
            $data->where('user_agent', 'like', "%$filters->user_agent%");
        }

        if ($filters->has('status_code') && !empty($filters->status_code))
        {
            $data->where('status_code', 'like', "%$filters->status_code%");
        }

        if ($filters->has('success') && !empty($filters->success))
        {
            $data->where('success', $filters->success);
        }

        if (
            $filters->has('created_at_start') && !empty($filters->created_at_start) &&
            $filters->has('created_at_end') && !empty($filters->created_at_end)
        )
        {
            $data->whereBetween('created_at', [
                Carbon::parse($filters->created_at_start)->startOfDay(),
                Carbon::parse($filters->created_at_end)->endOfDay(),
            ]);
        }

        if ($filters->has('search_words') && !empty($filters->search_words))
        {
            $data->where(function($query) use ($filters) {
                foreach ($filters->search_words as $word) {
                    $query->where('method', 'like', "%$word%")
                        ->orWhere('path', 'like', "%$word%")
                        ->orWhere('ip', 'like', "%$word%")
                        ->orWhere('user_agent', 'like', "%$word%")
                        ->orWhere('status_code', 'like', "%$word%")
                        ->orWhere('uuid', $word);
                }
            });
        }

        return $data->orderBy($filters->sortBy ?? 'created_at', $filters->orderBy ?? 'asc')->paginate($size);
    }
}
