<?php

namespace App\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClaimHeaderFilter
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

        if ('user_department_uuid' && !empty($filters->user_department_uuid))
        {
            $data->whereHas('user.employment.department', function($query) use ($filters) {
                $query->where('uuid', $filters->user_department_uuid);
            });
        }

        if ($filters->has('is_director') && $filters->is_director >= 0)
        {
            $data->where('manager_reviewed_at', '!=', null);
        }

        if ($filters->has('user_office_uuid') && !empty($filters->user_office_uuid))
        {
            $data->whereHas('user.employment.office', function($query) use ($filters) {
                $query->where('uuid', $filters->user_office_uuid);
            });
        }

        if ($filters->has('user_position_uuid') && !empty($filters->user_position_uuid))
        {
            $data->whereHas('user.employment.position', function($query) use ($filters) {
                $query->where('uuid', $filters->user_position_uuid);
            });
        }

        if ($filters->has('manager_approver_uuid') && !empty($filters->manager_approver_uuid))
        {
            $data->whereHas('managerApprover', function($query) use ($filters) {
                $query->where('uuid', $filters->manager_approver_uuid);
            });
        }

        if ($filters->has('relevant_to_me') && $filters->relevant_to_me)
        {
            $data->where('user_id', Auth::user()->id);
        }
        
        if ($filters->has('name') && !empty($filters->name))
        {
            $data->where('name', 'like', "%$filters->name%");
        }

        if ($filters->has('created_at_from') && $filters->has('created_at_to'))
        {
            $data->where(function ($q) use ($filters) {
                if ($filters->created_at_from && $filters->created_at_to)
                {
                    $q->whereDate('created_at', '<=', $filters->created_at_to)
                        ->whereDate('created_at', '>=', $filters->created_at_from);
                }
            });
        }

        if ($filters->has('created_from') && !empty($filters->created_from))
        {
            $data->whereDate('created_at', '>=', $filters->created_from);
        }

        if ($filters->has('created_to') && !empty($filters->created_to))
        {
            $data->whereDate('created_at', '<=', $filters->created_to);
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
                        ->orWhereHas('user.personal', function($query) use ($word) {
                            $query->where('full_name', 'like', "%$word%")
                                ->orWhere('first_name', 'like', "%$word%")
                                ->orWhere('last_name', 'like', "%$word%");
                        })
                        ->orWhereHas('user', function($query) use ($word) {
                            $query->where('email', 'like', "%$word%");
                        })
                        ->orWhereHas('claimItems', function($query) use ($word) {
                            $query->where('name', 'like', "%$word%");
                        });
                }
            });
        }

        return $data->orderBy($filters->sortBy ?? 'created_at', $filters->orderBy ?? 'asc')->paginate($size);
    }
}
