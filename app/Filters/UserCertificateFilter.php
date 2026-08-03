<?php

namespace App\Filters;

use Illuminate\Http\Request;

class UserCertificateFilter
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

        if ($filters->has('name') && !empty($filters->name))
        {
            $data->where('name', 'like', "%$filters->name%");
        }

        if ($filters->has('organization') && !empty($filters->organization))
        {
            $data->where('organization', 'like', "%$filters->organization%");
        }

        if ($filters->has('date_applied') && !empty($filters->date_applied))
        {
            $data->where('date_applied', 'like', "%$filters->date_applied%");
        }

        if (
            $filters->has('date_applied_from') && !empty($filters->date_applied_from) &&
            $filters->has('date_applied_to') && !empty($filters->date_applied_to)
        )
        {
            $data->whereBetween('date_applied', [$filters->date_applied_from, $filters->date_applied_to]);
        }

        if ($filters->has('valid_until') && !empty($filters->valid_until))
        {
            $data->where('valid_until', 'like', "%$filters->valid_until%");
        }

        if (
            $filters->has('valid_until_from') && !empty($filters->valid_until_from) &&
            $filters->has('valid_until_to') && !empty($filters->valid_until_to)
        )
        {
            $data->whereBetween('valid_until', [$filters->valid_until_from, $filters->valid_until_to]);
        }

        if ($filters->has('is_active') && $filters->is_active >= 0)
        {
            $data->where('is_active', "$filters->is_active");
        }

        if ($filters->has('user_name') && !empty($filters->user_name))
        {
            $data->whereHas('user.personal', function($query) use ($filters) {
                $query->where('full_name', 'like', "%$filters->user_name%")
                    ->orWhere('first_name', 'like', "%$filters->user_name%")
                    ->orWhere('last_name', 'like', "%$filters->user_name%");
            });
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

        if ($filters->has('phone_number') && !empty($filters->phone_number))
        {
            $data->whereHas('user.contact', function($query) use ($filters) {
                $query->where('phone_number', 'like', "%$filters->phone_number%");
            });
        }

        if ($filters->has('department') && !empty($filters->department))
        {
            $data->whereHas('user.employment.department', function($query) use ($filters) {
                $query->where('name', 'like', "%$filters->department%")
                    ->orWhere('uuid', $filters->department);
            });
        }

        if ($filters->has('position') && !empty($filters->position))
        {
            $data->whereHas('user.employment.position', function($query) use ($filters) {
                $query->where('name', 'like', "%$filters->position%")
                    ->orWhere('uuid', $filters->position);
            });
        }

        if ($filters->has('office') && !empty($filters->office))
        {
            $data->whereHas('user.employment.office', function($query) use ($filters) {
                $query->where('name', 'like', "%$filters->office%")
                    ->orWhere('uuid', $filters->office);
            });
        }

        if ($filters->has('search_words') && !empty($filters->search_words))
        {
            $data->where(function($query) use ($filters) {
                foreach ($filters->search_words as $word) {
                    $query->where('name', 'like', "%$word%")
                        ->orWhere('organization', 'like', "%$word%")
                        ->orWhere('description', 'like', "%$word%")
                        ->orWhere('uuid', $word)
                        ->orWhereHas('user', function($query) use ($word) {
                            $query->where('email', 'like', "%$word%")
                                ->orWhere('uuid', $word)
                                ->orWhereHas('personal', function($query) use ($word) {
                                    $query->where('full_name', 'like', "%$word%")
                                        ->orWhere('first_name', 'like', "%$word%")
                                        ->orWhere('last_name', 'like', "%$word%")
                                        ->orWhere('identity_number', 'like', "%$word%")
                                        ->orWhere('passport_number', 'like', "%$word%");
                                });
                        });
                }
            });
        }

        return $data->orderBy($filters->sortBy ?? 'created_at', $filters->orderBy ?? 'asc')->paginate($size);
    }
}
