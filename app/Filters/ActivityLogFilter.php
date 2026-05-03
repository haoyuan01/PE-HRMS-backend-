<?php

namespace App\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityLogFilter
{
    public function apply(Request $filters, $size, $data)
    {
        if ($filters->has('uuid') && !empty($filters->uuid))
        {
            $data->where('uuid', $filters->uuid);
        }

        if ($filters->has('log_name') && !empty($filters->log_name))
        {
            $data->where('log_name', 'like', "%$filters->log_name%");
        }

        if ($filters->has('event') && !empty($filters->event))
        {
            $data->where('event', 'like', "$filters->event");
        }

        if ($filters->has('description') && !empty($filters->description))
        {
            $data->where('description', 'like', "$filters->description");
        }

        if ($filters->has('user') && !empty($filters->user))
        {
            $data->whereHas('user', function ($query) use ($filters) {
                $query->where('email', 'like', "%$filters->user%");
            });
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
                    $query->where('log_name', 'like', "%$word%")
                          ->orWhere('event', 'like', "%$word%")
                          ->orWhere('description', 'like', "%$word%")
                          ->orWhereHas('user', function ($query) use ($word) {
                              $query->where('email', 'like', "%$word%");
                          });
                }
            });
        }

        return $data->orderBy($filters->sortBy ?? 'created_at', $filters->orderBy ?? 'asc')->paginate($size);
    }
}