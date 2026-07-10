<?php

namespace App\Filters;

use Illuminate\Http\Request;

class LeaveRequestFilter
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

        if ($filters->has('manager_approver_uuid') && !empty($filters->manager_approver_uuid))
        {
            $data->whereHas('managerApprover', function($query) use ($filters) {
                $query->where('uuid', $filters->manager_approver_uuid);
            });
        }

        if ($filters->has('leave_entitlement_policy') && !empty($filters->leave_entitlement_policy))
        {
            $data->whereHas('leaveEntitlement.leavePolicy', function($query) use ($filters) {
                $query->where('uuid', $filters->leave_entitlement_policy)
                    ->orWhere('name', 'like', "%$filters->leave_entitlement_policy%")
                    ->orWhere('code', 'like', "%$filters->leave_entitlement_policy%");
            });
        }

        if ($filters->has('is_director') && $filters->is_director >= 0)
        {
            $data->whereHas('user.employment', function($query) use ($filters) {
                $query->where('is_director', "$filters->is_director");
            });
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
                    $query->whereHas('user.personal', function($query) use ($word) {
                            $query->where('full_name', 'like', "%$word%")
                                ->orWhere('first_name', 'like', "%$word%")
                                ->orWhere('last_name', 'like', "%$word%");
                        })
                        ->orWhereHas('user', function($query) use ($word) {
                            $query->where('email', 'like', "%$word%");
                        })
                        ->orWhereHas('leaveEntitlement.leavePolicy', function($query) use ($word) {
                            $query->where('name', 'like', "%$word%")
                                ->orWhere('code', 'like', "%$word%");
                        })
                        ->orWhere('uuid', $word);
                }
            });
        }

        return $data->orderBy($filters->sortBy ?? 'created_at', $filters->orderBy ?? 'asc')->paginate($size);
    }
}
