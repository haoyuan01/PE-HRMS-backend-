<?php

namespace App\Filters;

use App\Constants\StatusCodeConstants;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpcomingEventFilter
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

        if ($filters->has('location') && !empty($filters->location))
        {
            $data->where('location', 'like', "%$filters->location%");
        }

        if ($filters->has('department_uuid') && !empty($filters->department_uuid))
        {
            $data->whereHas('upcomingEventDepartments.department', function($query) use ($filters) {
                $query->where('uuid', $filters->department_uuid);
            });
        }

        if ($filters->has('office_uuid') && !empty($filters->office_uuid))
        {
            $data->whereHas('upcomingEventOffices.office', function($query) use ($filters) {
                $query->where('uuid', $filters->office_uuid);
            });
        }

        if ($filters->has('relevant_to_me') && $filters->relevant_to_me)
        {
            $user = User::findByUuid(Auth::user()->uuid);

            $data->where(function($query) use ($user) {
                $query->whereHas('upcomingEventDepartments', function($query) use ($user) {
                    $query->where('department_id', $user->employment?->department_id)
                        ->where('is_active', StatusCodeConstants::ACTIVE);
                })
                ->orWhereHas('upcomingEventOffices', function($query) use ($user) {
                    $query->where('office_id', $user->employment?->office_id)
                        ->where('is_active', StatusCodeConstants::ACTIVE);
                });
            });
        }

        if ($filters->has('is_published') && $filters->is_published >= 0)
        {
            $data->where('is_published', "$filters->is_published");
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
