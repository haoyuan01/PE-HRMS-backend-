<?php

namespace App\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserFilter
{
    public function apply(Request $filters, $size, $data)
    {
        if ($filters->has('user_uuid') && !empty($filters->user_uuid))
        {
            $data->where('uuid', $filters->user_uuid);
        }

        if ($filters->has('name') && !empty($filters->name))
        {
            $data->whereHas('personal', function($query) use ($filters) {
                $query->where('full_name', 'like', "%$filters->name%")
                    ->orWhere('first_name', 'like', "%$filters->name%")
                    ->orWhere('last_name', 'like', "%$filters->name%");
            });
        }

        if ($filters->has('email') && !empty($filters->email))
        {
            $data->where('email', $filters->email);
        }

        if ($filters->has('is_active') && $filters->is_active >= 0)
        {
            $data->where('is_active', "$filters->is_active");
        }

        if ($filters->has('is_married') && $filters->is_married >= 0)
        {
            $data->whereHas('personal', function($query) use ($filters) {
                $query->where('is_married', "$filters->is_married");
            });
        }

        if ($filters->has('gender') && $filters->gender >= 0)
        {
            $data->whereHas('personal', function($query) use ($filters) {
                $query->where('gender', "$filters->gender");
            });
        }

        if ($filters->has('blood_type') && !empty($filters->blood_type))
        {
            $data->whereHas('personal', function($query) use ($filters) {
                $query->where('blood_type', 'like', "%$filters->blood_type%");
            });
        }

        if ($filters->has('identity_number') && !empty($filters->identity_number))
        {
            $data->whereHas('personal', function($query) use ($filters) {
                $query->where('identity_number', 'like', "%$filters->identity_number%");
            });
        }

        if ($filters->has('passport_number') && !empty($filters->passport_number))
        {
            $data->whereHas('personal', function($query) use ($filters) {
                $query->where('passport_number', 'like', "%$filters->passport_number%");
            });
        }

        if ($filters->has('company_email') && !empty($filters->company_email))
        {
            $data->whereHas('contact', function($query) use ($filters) {
                $query->where('company_email', 'like', "%$filters->company_email%");
            });
        }

        if ($filters->has('phone_number') && !empty($filters->phone_number))
        {
            $data->whereHas('contact', function($query) use ($filters) {
                $query->where('phone_number', 'like', "%$filters->phone_number%");
            });
        }

        if ($filters->has('address') && !empty($filters->address))
        {
            $data->whereHas('contact', function($query) use ($filters) {
                $query->where(DB::raw("CONCAT_WS(address_1,'',address_2,'',address_3)"), 'like', "%$filters->address%");
            });
        }

        if ($filters->has('city') && !empty($filters->city))
        {
            $data->whereHas('contact', function($query) use ($filters) {
                $query->where('city', 'like', "%$filters->city%");
            });
        }

        if ($filters->has('state') && !empty($filters->state))
        {
            $data->whereHas('contact', function($query) use ($filters) {
                $query->where('state', 'like', "%$filters->state%");
            });
        }

        if ($filters->has('country') && !empty($filters->country))
        {
            $data->whereHas('contact', function($query) use ($filters) {
                $query->where('country', 'like', "%$filters->country%");
            });
        }

        if ($filters->has('postcode') && !empty($filters->postcode))
        {
            $data->whereHas('contact', function($query) use ($filters) {
                $query->where('postcode', 'like', "%$filters->postcode%");
            });
        }

        // if ($filters->has('personal') && !empty($filters->personal))
        // {
        //     $data->whereHas('personal', function($query) use ($filters) {
        //         $query->where('first_name', 'like', "%$filters->personal%")
        //             ->orWhere('last_name', 'like', "%$filters->personal%")
        //             ->orWhere('full_name', 'like', "%$filters->personal%")
        //             ->orWhere('identity_number', 'like', "%$filters->personal%")
        //             ->orWhere('passport_number', 'like', "%$filters->personal%")
        //             ->orWhere('blood_type', 'like', "%$filters->personal%");
        //     });
        // }

        // if ($filters->has('emergency') && !empty($filters->emergency))
        // {
        //     $data->whereHas('emergency', function($query) use ($filters) {
        //         $query->where('name', 'like', "%$filters->emergency%")
        //             ->orWhere('phone_number', 'like', "%$filters->emergency%")
        //             ->orWhere('relationship', 'like', "%$filters->emergency%");
        //     });
        // }

        // if ($filters->has('contact') && !empty($filters->contact))
        // {
        //     $data->whereHas('contact', function($query) use ($filters) {
        //         $query->where('phone_number', 'like', "%$filters->contact%")
        //             ->orWhere('company_email', 'like', "%$filters->contact%")
        //             ->orWhere(DB::raw("CONCAT_WS(address_1,'',address_2,'',address_3)"), 'like', "%$filters->contact%")
        //             ->orWhere('city', 'like', "%$filters->contact%")
        //             ->orWhere('state', 'like', "%$filters->contact%")
        //             ->orWhere('postcode', 'like', "%$filters->contact%")
        //             ->orWhere('country', 'like', "%$filters->contact%");
        //     });
        // }

        // if ($filters->has('employment') && !empty($filters->employment))
        // {
        //     $data->whereHas('employment', function($query) use ($filters) {
        //         $query->whereHas('position', function($query) use ($filters) {
        //             $query->where('name', 'like', "%$filters->employment%")
        //                 ->orWhere('uuid', $filters->employment);
        //         })
        //         ->orWhereHas('department', function($query) use ($filters) {
        //             $query->where('name', 'like', "%$filters->employment%")
        //                 ->orWhere('uuid', $filters->employment);
        //         })
        //         ->orWhereHas('office', function($query) use ($filters) {
        //             $query->where('name', 'like', "%$filters->employment%")
        //                 ->orWhere('uuid', $filters->employment);
        //         });
        //     });
        // }

        if ($filters->has('department') && !empty($filters->department))
        {
            $data->whereHas('employment', function($query) use ($filters) {
                $query->whereHas('department', function($query) use ($filters) {
                    $query->where('name', 'like', "%$filters->department%")
                        ->orWhere('uuid', $filters->department);
                });
            });
        }

        if ($filters->has('position') && !empty($filters->position))
        {
            $data->whereHas('employment', function($query) use ($filters) {
                $query->whereHas('position', function($query) use ($filters) {
                    $query->where('name', 'like', "%$filters->position%")
                        ->orWhere('uuid', $filters->position);
                });
            });
        }

        if ($filters->has('office') && !empty($filters->office))
        {
            $data->whereHas('employment', function($query) use ($filters) {
                $query->whereHas('office', function($query) use ($filters) {
                    $query->where('name', 'like', "%$filters->office%")
                        ->orWhere('uuid', $filters->office);
                });
            });
        }

        if ($filters->has('joined_date') && !empty($filters->joined_date))
        {
            $data->whereHas('employment', function($query) use ($filters) {
                $query->where('joined_date', 'like', "%$filters->joined_date%");
            });
        }

        if (
            $filters->has('joined_date_from') && !empty($filters->joined_date_from) &&
            $filters->has('joined_date_to') && !empty($filters->joined_date_to)
        )
        {
            $data->whereHas('employment', function($query) use ($filters) {
                $query->whereBetween('joined_date', [$filters->joined_date_from, $filters->joined_date_to]);
            });
        }

        if ($filters->has('has_role') && !empty($filters->has_role))
        {
            $data->whereHas('roles', function($query) use ($filters) {
                $query->where('name', $filters->has_role);
            });
        }

        if ($filters->has('search_words') && !empty($filters->search_words))
        {
            $data->where(function($query) use ($filters) {
                foreach ($filters->search_words as $word) {
                    $query->where('email', 'like', "%$word%")
                        ->orWhereHas('personal', function($query) use ($word) {
                            $query->where('full_name', 'like', "%$word%")
                                ->orWhere('first_name', 'like', "%$word%")
                                ->orWhere('last_name', 'like', "%$word%")
                                ->orWhere('identity_number', 'like', "%$word%")
                                ->orWhere('passport_number', 'like', "%$word%");
                        })
                        ->orWhere('uuid', $word);
                }
            });
        }

        return $data->orderBy($filters->sortBy ?? 'created_at', $filters->orderBy ?? 'asc')->paginate($size);
    }
}