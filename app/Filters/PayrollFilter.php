<?php

namespace App\Filters;

use Illuminate\Http\Request;

class PayrollFilter
{
    public function apply(Request $filters, $size, $data)
    {
        if ($filters->has('uuid') && !empty($filters->uuid))
        {
            $data->where('uuid', $filters->uuid);
        }

        if ($filters->has('user_uuid') && !empty($filters->user_uuid))
        {
            $data->whereHas('user', function($query) use ($filters) {
                $query->where('uuid', $filters->user_uuid);
            });
        }

        if ($filters->has('is_active') && $filters->is_active >= 0)
        {
            $data->where('is_active', "$filters->is_active");
        }

        if ($filters->has('is_published') && $filters->is_published >= 0)
        {
            $data->where('is_published', "$filters->is_published");
        }

        if ($filters->has('year') && !empty($filters->year))
        {
            $data->where('year', $filters->year);
        }

        if ($filters->has('month') && !empty($filters->month))
        {
            $data->where('month', $filters->month);
        }

        if ($filters->has('email') && !empty($filters->email))
        {
            $data->whereHas('user', function($query) use ($filters) {
                $query->where('email', $filters->email);
            });
        }

        if ($filters->has('company_email') && !empty($filters->company_email))
        {
            $data->whereHas('user.contact', function($query) use ($filters) {
                $query->where('company_email', 'like', "%$filters->company_email%");
            });
        }

        if ($filters->has('department') && !empty($filters->department))
        {
            $data->whereHas('user.employment', function($query) use ($filters) {
                $query->whereHas('department', function($query) use ($filters) {
                    $query->where('name', 'like', "%$filters->department%")
                        ->orWhere('uuid', $filters->department);
                });
            });
        }

        if ($filters->has('position') && !empty($filters->position))
        {
            $data->whereHas('user.employment', function($query) use ($filters) {
                $query->whereHas('position', function($query) use ($filters) {
                    $query->where('name', 'like', "%$filters->position%")
                        ->orWhere('uuid', $filters->position);
                });
            });
        }

        if ($filters->has('office') && !empty($filters->office))
        {
            $data->whereHas('user.employment', function($query) use ($filters) {
                $query->whereHas('office', function($query) use ($filters) {
                    $query->where('name', 'like', "%$filters->office%")
                        ->orWhere('uuid', $filters->office);
                });
            });
        }

        if ($filters->has('joined_date') && !empty($filters->joined_date))
        {
            $data->whereHas('user.employment', function($query) use ($filters) {
                $query->where('joined_date', 'like', "%$filters->joined_date%");
            });
        }

        if (
            $filters->has('joined_date_from') && !empty($filters->joined_date_from) &&
            $filters->has('joined_date_to') && !empty($filters->joined_date_to)
        )
        {
            $data->whereHas('user.employment', function($query) use ($filters) {
                $query->whereBetween('joined_date', [$filters->joined_date_from, $filters->joined_date_to]);
            });
        }

        if ($filters->has('search_words') && !empty($filters->search_words))
        {
            $data->where(function($query) use ($filters) {
                foreach ($filters->search_words as $word) {
                    $query->whereHas('user', function($query) use ($word) {
                            $query->where('email', 'like', "%$word%")
                                ->orWhere('uuid', $word)
                                ->orWhereHas('personal', function($query) use ($word) {
                                    $query->where('full_name', 'like', "%$word%")
                                        ->orWhere('first_name', 'like', "%$word%")
                                        ->orWhere('last_name', 'like', "%$word%");
                                });
                        });
                }
            });
        }

        return $data->orderBy($filters->sortBy ?? 'created_at', $filters->orderBy ?? 'asc')->paginate($size);
    }
}
