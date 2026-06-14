<?php

namespace App\Filters;

use Illuminate\Http\Request;

class ClaimHeaderFilter
{
    public function apply(Request $filters, $size, $data)
    {
        if ($filters->has('uuid') && !empty($filters->uuid))
        {
            $data->where('uuid', $filters->uuid);
        }
        
        if ($filters->has('name') && !empty($filters->name))
        {
            $data->where('name', 'like', "%$filters->name%");
        }

        if ($filters->filled('start_date') && $filters->filled('end_date'))
        {
            $data->where(function ($q) use ($filters) {
                if ($filters->start_date && $filters->end_date)
                {
                    $q->whereDate('start_date', '<=', $filters->end_date)
                        ->whereDate('end_date', '>=', $filters->start_date);
                }
            });
        }

        if ($filters->has('is_active') && $filters->is_active >= 0)
        {
            $data->where('is_active', "$filters->is_active");
        }

        if ($filters->has('search_words') && !empty($filters->search_words))
        {
            $data->where(function($query) use ($filters) {
                foreach ($filters->search_words as $word) {
                    $query->where('name', 'like', "%$word%")
                        ->orWhere('uuid', $word);
                }
            });
        }

        return $data->orderBy($filters->sortBy ?? 'created_at', $filters->orderBy ?? 'asc')->paginate($size);
    }
}
