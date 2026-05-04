<?php

namespace App\Filters;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ErrorLogFilter
{
    public function apply(Request $filters, $size, $data)
    {
        if ($filters->has('uuid') && !empty($filters->uuid))
        {
            $data->where('uuid', $filters->uuid);
        }

        if ($filters->has('level') && !empty($filters->level))
        {
            $data->where('level', 'like', "%$filters->level%");
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
                    $query->where('level', 'like', "%$word%")
                        ->orWhere('uuid', $word);
                }
            });
        }

        return $data->orderBy($filters->sortBy ?? 'created_at', $filters->orderBy ?? 'asc')->paginate($size);
    }
}