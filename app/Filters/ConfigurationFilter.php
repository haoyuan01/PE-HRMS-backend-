<?php

namespace App\Filters;

use Illuminate\Http\Request;

class ConfigurationFilter
{
    public function apply(Request $filters, $size, $data)
    {
        if ($filters->has('uuid') && !empty($filters->uuid))
        {
            $data->where('uuid', $filters->uuid);
        }
        
        if ($filters->has('key') && !empty($filters->key))
        {
            $data->where('key', 'like', "%$filters->key%");
        }

        if ($filters->has('value_type') && !empty($filters->value_type))
        {
            $data->where('value_type', 'like', "%$filters->value_type%");
        }

        if ($filters->has('search_words') && !empty($filters->search_words))
        {
            $data->where(function($query) use ($filters) {
                foreach ($filters->search_words as $word) {
                    $query->where('key', 'like', "%$word%")
                        ->orWhere('value_type', 'like', "%$word%")
                        ->orWhere('uuid', $word);
                }
            });
        }

        return $data->orderBy($filters->sortBy ?? 'created_at', $filters->orderBy ?? 'asc')->paginate($size);
    }
}